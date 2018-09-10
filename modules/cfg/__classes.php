<?php

if (!defined("API")) {
    exit("Main include fail");
}

class cfg {

    public $return = array();
    public $lang = array();
    public $curLang = "ru";
    public $uri = array();
    public $sql = false;
    public $postArray = array();
    public $errorParam = false;
    public $api = array();

    public function show() {        
        $this->return['content'] = '';
        
        if (!empty($this->errorParam)) {
            $this->return['content'] .= '<h4 class="alert_error">' . $this->errorParam . '</h4>';
        }
        
        $this->return['content'] .= '<form class="general_form form-horizontal" action="save.php" method="post" onsubmit="disableSubmit(&quot;submit&quot;);">';
        
        $mCfg = new mCfg();

        foreach ($mCfg->cfg as $key => $empty) {
            if (isset($this->postArray['cfgParams'][$paramName = $mCfg->cfg[$key][0]])) {
                $defaultValue = htmlspecialchars(stripslashes($this->postArray['cfgParams'][$paramName]));
            } else {
                $defaultValue = api::getConfig("modules", $this->uri[3], $mCfg->cfg[$key][0]);
            }

            switch ($mCfg->cfg[$key][3]) {
                case "text":
                    $formHtmlCode = '
                        <div class="inputs col-md-9">
                            <div class=" ">
                                <input class="text form-control" type="text" name="cfgParams[' . $mCfg->cfg[$key][0] . ']" value="' . $defaultValue . '">
                            </div>';
                    /*'<input type="text" name="cfgParams[' . $mCfg->cfg[$key][0] . '] value="' . $defaultValue . '">';*/
                    break;

                case "textarea":
                    $formHtmlCode = '<textarea name="cfgParams[' . $mCfg->cfg[$key][0] . ']">' . $defaultValue . '</textarea>';
                    break;

                default:
                    continue;
                    break;
            }

            $this->return['content'] .= '
                    <div class=" form-group">
                        <label class="control-label col-md-3">' . $mCfg->cfg[$key][1] . ':</label>' . $formHtmlCode . ' 
                    </div>
                </div>';
            /* '<label>' . $mCfg->cfg[$key][1] . '</label>' . $formHtmlCode; */
            /*                    "<tr><td width=\"40%\" valign=\"top\"" . ($mCfg->cfg[$key][0] === $this->errorParam ? " style=\"background: #FFF2F2;\"" : "") . "><strong>" . $mCfg->cfg[$key][1] . ":</strong><br><small>" . $mCfg->cfg[$key][2] . "</small>" . ($mCfg->cfg[$key][0] === $this->errorParam ? "<br><br><strong><font color=\"red\">ОШИБКА! Поле не заполнено или заполнено неверно!</font>" : "") . "</td><td width=\"60%\" nowrap=\"nowrap\">" . $formHtmlCode . (strtolower($mCfg->cfg[$key][5]) === "yes" ? "<font color=\"red\">*</font>" : "") . "</td></tr>"; */
        }

        $this->return['content'] .= '<p align="center"><input class="submit" type="submit" value="Сохранить" id="submit"></p></form>';
    }

    public function save() {
        global $uri;
        
        $mCfg = new mCfg();

        foreach ($cfgArray = &$mCfg->cfg as $key => $empty) {
            if (strtolower($cfgArray[$key][5]) === "yes" && (!isset($this->postArray['cfgParams'][$paramName = $cfgArray[$key][0]]) || empty($this->postArray['cfgParams'][$paramName]))) {
                $this->errorParam = 'Ошибка в поле "' . $cfgArray[$key][1] . '"';
                return $this->show();
            }
        }

        mCfg::check();

        if ($this->errorParam !== false) {
            return false;
        }

        $queries = "<strong>Были выполнены следующие запросы</strong><br><br>";

        foreach ($cfgArray as $key => $empty) {
            $paramName = $cfgArray[$key][0];
            $paramValue = $this->postArray['cfgParams'][$paramName];

            $this->sql->query("
                SELECT COUNT(*) FROM `#__#config` 
                WHERE `category` = 'modules' 
                AND `type` = '" . $this->uri[3] . "' 
                AND `name` = '" . $paramName . "' 
                AND `lang` = '" . $this->curLang . "'
                AND `city_name` = '".$_SESSION['citynameadmin']."'", true);

            if ((int) $this->sql->result[0] === 0) {
                $this->sql->query($query = "INSERT INTO `#__#config`(`category`,
																	`type`,
																	`name`,
																	`value`,
																	`lang`,
                                                                    `city_name`)
															VALUES(
																	'modules',
																	'" . $this->uri[3] . "',
																	'" . $paramName . "',
																	'" . $paramValue . "',
																	'" . $this->curLang . "',
                                                                    '".$_SESSION['citynameadmin']."')
                                                            ");

                $queries .= "SQL\t" . htmlspecialchars($query) . "<br>";
            } else {
                $this->sql->query($query = "
                    UPDATE `#__#config` 
                    SET  `value` = '" . $paramValue . "' 
                    WHERE `category` = 'modules' 
                    AND `type` = '" . $this->uri[3] . "' 
                    AND `name` = '" . $paramName . "' 
                    AND `lang` = '" . $this->curLang . "'
                    AND `city_name` = '".$_SESSION['citynameadmin']."'");
            }
        }
        message("OK", (@$this->postArray['showSql'] == 1 ? $queries : ""), (isset($uri[3]) && $uri[3] != 'fb'  ? 'admin/' . $uri[3] . '/' : 'admin/'));
    }

    function __construct() {
        global $_POST;
        $this->postArray = api::slashData($_POST);
    }

}

?>
