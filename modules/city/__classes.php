<?php
if (!defined("API")) {
    exit("Main include fail");
}

class city
{
    private $mDir = "city";
    public $data = array();
    public $page_title = "";

    function __construct()
    {

    }

    public function adminShowIndex()
    {
        global $sql;

        $db = clone $sql;
        $db->query("SELECT `id`, `name`, `city` FROM `city`");
        $cityList = $db->getList();
        $city = '';
        $template = new template(api::setTemplate("modules/" . $this->mDir . "/admin.show.item.html"));
        foreach ($cityList as &$item) {
            $template->assign('id', $item['id']);
            $template->assign('name', $item['name']);
            $template->assign('city', $item['city']);
            $city .= $template->get();
        }
        $template = new template(api::setTemplate("modules/" . $this->mDir . "/admin.show.body.html"));
        $template->assign('body', $city);


        $this->data['content'] = $template->get();
    }

    public function edit($name)
    {
        global $sql;

        $db = clone $sql;
        if (empty($_POST['city'])) {
            $template = new template(api::setTemplate("modules/" . $this->mDir . "/admin.add.value.html"));

            if (!empty($_GET['name']) && $_GET['name'] != 'add') {
                $db->query("SELECT `city`, `name`, `robots` FROM `city` WHERE `name`='" . $name . "'");
                $result = $db->getList();
                foreach ($result as $elem) {
                    $template->assign('city', $elem['city']);
                    $template->assign('name', $elem['name']);
                    $template->assign('robots', $elem['robots']);
                }
            } else {
                $template->assign('name', 'add');
                $template->assign('city', '');
                $template->assign('name', '');
                $template->assign('robots', '');
            }

            $this->data['content'] = $template->get();
        } else {
            if ($_GET['name'] == 'add' || $_GET['name'] == '') {
                $db->query("
                    INSERT INTO `city`(`city`, `name`, `robots`) 
                    VALUES ('" . $_POST['city'] . "','" . $_POST['name'] . "','" . $_POST['robots'] . "')"
                );

                /* Копируем все страницы с главного города */
                // получаем id  города который создали
                // $cityId = $db->lastId();
                // Копируем все страницы Иркутска в новый город
                $this->addPageToСity($_POST['name']);

                // создадим дефолтную запись роботсов
                $this->addRobotToCity($_POST['name']);
                
//                $this->addSlideToСity($cityId);
                message('Успешно!', 'Город ' . $_POST['city'] . ' успешно создан.', '/admin/city/index.php');
            } else {
                $db->query("UPDATE `city` SET `city`='" . $_POST['city'] . "', `name`='" . $_POST['name'] . "',`robots`='" . $_POST['robots'] . "' WHERE `id`='" . $id . "'");
                message('Успешно!', 'Город ' . $_POST['city'] . ' успешно отредактирован.', '/admin/city/index.php');
            }
        }
    }

    private function addRobotToCity($cityName)
    {
        global $sql;

        $default = 'User-Agent: *
Disallow: /';
        
        $sql->query('INSERT INTO `robots` (`city_name`, `robotext`) VALUES ("'.$cityName.'", "'.$default.'")');

        return true;
    }

    private function addPageToСity($cityName)
    {
        global $sql;
        $db = clone $sql;
        $db->query("SELECT * FROM `pages` WHERE `city_name`='irkutsk'");
        $pages = $db->getList();
        foreach ($pages as $page) {
            $db->query("INSERT INTO `pages` (`ownerId`, `title`, `uri`, `image`, `template`, `thisTemplate`, `navigationShow`, `navigationTitle`, `footerShow`, `redirect`, `text`, `md`, `mk`, `lang`, `pageTitle`, `position`, `photo`, `shopitems`, `region_id`, `text2`, `city_name`) 
                                VALUES ('" . $page['ownerId'] . "', '" . $page['title'] . "', '" . $page['uri'] . "', '" . $page['image'] . "', '" . $page['template'] . "', '" . $page['thisTemplate'] . "', '" . $page['navigationShow'] . "', '" . $page['navigationTitle'] . "', '" . $page['footerShow'] . "', '" . $page['redirect'] . "', '" . $page['text'] . "', '" . $page['md'] . "', '" . $page['mk'] . "', '" . $page['lang'] . "', '" . $page['pageTitle'] . "', '" . $page['position'] . "', '" . $page['photo'] . "', '" . $page['shopitems'] . "', '" . $page['region_id'] . "', '" . $page['text2'] . "', '" . $cityName . "')");
        }
        $db->query("SELECT `id`, `uri` FROM `pages` WHERE `ownerId`='0' AND `city_name`='irkutsk'");
        $parentPage = $db->getList();
        foreach ($parentPage as $old){
            $db->query("SELECT `id` FROM `pages` WHERE `uri`='" . $old['uri'] . "' AND `city_name`='" . $cityName . "' LIMIT 1");
            $child = $db->getList();
            $db->query("UPDATE `pages` SET `ownerId` = '" . $child[0]['id'] . "' WHERE ownerId='" . $old['id'] . "' AND `city_name`='".$cityName."'");
        }
    }

    public function deleteCity($name)
    {
        global $sql;
        if ($name != 'irkutsk') {
            $db = clone $sql;
            $db->query("SELECT COUNT(*) as `count` FROM `city` WHERE `name`='" . $name . "' LIMIT 1");
            $res = $db->getList();
            if ($res[0]['count'] > 0) {
                $db->query("DELETE FROM `city` WHERE `name`='" . $name . "'");
                $db->query("DELETE FROM `pages` WHERE `city_name`='" . $name . "'");
                $db->query("DELETE FROM `slider` WHERE `city_name`='" . $name . "'");
                message('Успешно!', 'Город с name = ' . $name . ' успешно удалён.', '/admin/city/index.php');
            } else {
                message('Ошибка!', 'Город c name = ' . $name . ' отсутствует в базе.', '/admin/city/index.php');
            }
        } else {
            message('ошибка!', 'Нельзя удалить ведущий город!', '/admin/city/index.php');
        }

    }
}