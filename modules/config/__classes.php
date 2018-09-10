<?php

if (!defined("API")) {
    exit("Main include fail");
}

class config {

    public $lang = array();
    public $curLang = "ru";
    public $data = array();
    public $page_title = '';

    public function show() {
        $this->page_title = $this->lang['show'];
        $this->showMainConfig();

        return true;
    }

    public function robots(){
        global $sql;

        if($_POST){

            $sql->query('
                UPDATE `robots` 
                SET `robotext` = "'.$_POST['robodata'].'" 
                WHERE `city_name` = "'.$_SESSION['citynameadmin'].'"');

            header('Location: http://'.$_SERVER['SERVER_NAME'].'/admin/config/robots.php?status=ok');

        }else{

            if(isset($_GET['status']))
                $alert = '<script>alert("Файл robots.txt успешно обновлен")</script>';
            else
                $alert = '';

            $sql->query('SELECT `robotext` FROM `robots` WHERE `city_name` = "'.$_SESSION['citynameadmin'].'"', true);
            $content = $sql->result['robotext'];

            $template = new template(api::setTemplate("modules/config/admin.show.config.robots.html"));
            $template->assign("body", $content);
            $template->assign("alert", $alert);
            $this->data['content'] = $template->get();

        }
        return true;
    }


    private function showMainConfig() {
        global $sql;

        $sql->query("
            SELECT `name`, `value`, `description` 
            FROM `config` 
            WHERE `category` = 'main' 
            AND `lang` = '" . $this->curLang . "'
            AND `city_name` = '".$_SESSION['citynameadmin']."'");

        $template = new template(api::setTemplate("modules/config/admin.show.config.item.html"));

        $body = "";

        while ($sql->next_row()) {
            $template->assign("category", "main");
            $template->assign("type", "api");
            $template->assign("name", $sql->result[0]);
            $template->assign("value", $sql->result[1]);
            $template->assign("description", $sql->result[2]);

            $body .= $template->get();
        }

        $template = new template(api::setTemplate("modules/config/admin.show.config.body.html"));

        $template->assign("blockTitle", $this->lang['mainConfigBlockTitle']);
        $template->assign("category", "main");
        $template->assign("type", "api");

        $template->assign("body", $body);

        $this->data['content'] = $template->get();

        return true;
    }

    public function edit() {
        global $_GET, $sql;
        $getArray = slashArray($_GET);

        $category = $getArray['category'];
        $type = $getArray['type'];
        $name = $getArray['name'];

        $sql->query("
            SELECT `value`, `description` 
            FROM config 
            WHERE `category` = '" . $category . "' 
            AND `type` = '" . $type . "' 
            AND `name` = '" . $name . "' 
            AND `lang` = '" . $this->curLang . "'
            AND `city_name` = '" . $_SESSION['citynameadmin'] . "'", 
            true);

        if ((int) $sql->num_rows() !== 1) {
            page500();
        }

        $template = new template(api::setTemplate("modules/config/admin.edit.value.form.html"));
        $template->assign("category", $category);
        $template->assign("type", $type);
        $template->assign("name", $name);
        $template->assign("value", $sql->result[0]);
        $template->assign("description", $sql->result[1]);

        $this->data['content'] = $template->get();
        $this->page_title = $this->lang['edit'];
        
        return TRUE;
    }

    public function editGo() {
        global $_POST, $sql;
        $postArray = slashArray($_POST);

        @$category = $postArray['category'];
        @$type = $postArray['type'];
        @$name = $postArray['name'];
        @$value = $postArray['value'];
        @$description = $postArray['description'];

        if (empty($category) || empty($type) || empty($name)) {
            message($this->lang['error'], $this->lang['empty'], "admin/config/index.php");
        }

        $sql->query("
            UPDATE config 
            SET 
                `value` = '" . $value . "', 
                `description` = '" . $description . "' 
            WHERE `category` = '" . $category . "' 
            && `type` = '" . $type . "' 
            && name = '" . $name . "' 
            && `lang` = '" . $this->curLang . "'
            AND `city_name` = '" . $_SESSION['citynameadmin'] . "'");

        message($this->lang['editOk'], "", "admin/config/index.php");
    }

    public function add() {
        global $_GET, $_POST, $sql;
        $getArray = slashArray($_GET);
        $postArray = slashArray($_POST);

        $template = new template(api::setTemplate("modules/config/admin.add.value.html"));
        $template->assign("category", $getArray['category']);
        $template->assign("type", $getArray['type']);
        
        $this->data['content'] = $template->get();
        $this->page_title = $this->lang['add'];
        
        return TRUE;
    }

    public function addGo() {
        global $_GET, $_POST, $sql;
        $getArray = slashArray($_GET);
        $postArray = slashArray($_POST);

        $category = $getArray['category'];
        $type = $getArray['type'];
        $name = $postArray['name'];
        $value = $postArray['value'];
        $description = $postArray['description'];

        $sql->query("
            SELECT `value`, `description` 
            FROM config 
            WHERE `category` = '" . $category . "' 
            && `type` = '" . $type . "' 
            && name = '" . $name . "' 
            && `lang` = '" . $this->curLang . "'
            AND `city_name` = '" . $_SESSION['citynameadmin'] . "'", true);

        if ((int) $sql->num_rows() !== 0) {
            page500();
        }

        if (empty($category) || empty($type) || empty($name)) {
            message($this->lang['error'], $this->lang['empty'], "admin/config/index.php");
        }

        $sqlQuery = "
            INSERT INTO config 
                (`category`, `type`, `name`, `value`, `description`, `lang`, `city_name`) 
            VALUES
                (
                    '" . $category . "', 
                    '" . $type . "', 
                    '" . $name . "', 
                    '" . $value . "', 
                    '" . $description . "', 
                    '" . $this->curLang . "',
                    '" . $_SESSION['citynameadmin'] . "'
                )";
        
        $sql->query($sqlQuery);
        message($this->lang['addOk'], '', "admin/config/index.php");
    }

    public function delete() {
        global $_GET, $sql;
        $getArray = slashArray($_GET);

        $category = $getArray['category'];
        $type = $getArray['type'];
        $name = $getArray['name'];

        $sql->query("
            SELECT `value`, `description` 
            FROM config 
            WHERE `category` = '" . $category . "' 
            && `type` = '" . $type . "' 
            && name = '" . $name . "' 
            && `lang` = '" . $this->curLang . "'
            AND `city_name` = '" . $_SESSION['citynameadmin'] . "'", true);

        if ((int) $sql->num_rows() !== 1) {
            page500();
        }

        $sql->query("
            DELETE FROM config 
            WHERE `category` = '" . $category . "' 
            && `type` = '" . $type . "' 
            && name = '" . $name . "' 
            && `lang` = '" . $this->curLang . "'
            AND `city_name` = '" . $_SESSION['citynameadmin'] . "'");

        message($this->lang['deleteOk'], "", "admin/config/index.php");
    }

}

?>