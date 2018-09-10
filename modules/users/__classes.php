<?php

/**
 * Модуль управления пользователями системы управления
 *
 * Основной класс системы управления
 *
 * @package Plaza Content Managment System
 * @subpackage User manager module
 * @author Sergey A Oskorbin
 * @link http://in-site.ru
 * @copyright 2006-2007 Insite Ltd.
 */
if (!defined("API")) {
    exit("Main include fail");
}

class usersModule {

    public $page_title='';
    public $mDir = false;
    public $lang = array();
    public $exel = false;
    public $return = array();
    public $smarty = false;
    public $curLang = "ru";
    public $returnPath = "/admin/users/index.php";
    public $templatePath = "modules/users/";
    private $sql = false;
    private $api = array();
    private $data = array();
    private $uriArray = array();
    private $getArray = array();
    private $postArray = array();
    private $treeArray = array();
    private $filesArray = array();
    private $serverArray = array();
    private $sessionArray = array();

    /**
     * Фукция геренации HTML кода вывода формы добавления/редактирования пользователя
     *
     * @param string $actionType Тип операции (add - добавление, edit - удаление);
     * @param string $error Ошибка, возникшая при добавлении/редактирования пользователя
     * @param array $assignArray Массив для передачи в шаблонизатор
     *
     * @return string HTML код;
     */
    public function getAddOrEditUserForm($actionType = "add", $error = "", $assignArray = array()) {
        $smarty = clone $this->smarty;
        $smarty->assign("actionType", $actionType);
        $smarty->assign("loadedModules", api::fetchAllModulesAndLangs($this->curLang));

        $smarty->assign("error", (!empty($error) ? '<h4 class="alert_error">' . $error . '</h4>' : ''));
        $smarty->assign($assignArray);
        
        $this->page_title = $this->lang['users_edit'];

        return $smarty->fetch(api::setTemplate($this->templatePath . "admin/add.or.edit.user.tpl"));
    }

    /**
     * Функция выполняет проверку вводимых данных пользователем при добавлении/редактировании пользователя
     *
     * @param string $actionType add|edit тип проверки (add - добавление, !add - редактирвание);
     * @param array $postArray массив $_POST с убранными косыми слешами;
     * @return mixed
     */
    public function getPostDataError($actionType = "add", $postArray = array()) {
        if (empty($postArray['login']))
            return $this->lang['NO_LOGIN_ERR'];
        if (empty($postArray['fio']))
            return $this->lang['NO_FIO_ERR'];
        if ((empty($postArray['password_1']) || empty($postArray['password_2'])) && $actionType == "add")
            return $this->lang['NO_PASSWORD_ERR'];
        if (empty($postArray['email']))
            return $this->lang['NO_EMAIL_ERR'];
        if ($postArray['password_1'] !== $postArray['password_2'])
            return $this->lang['PASSWORD_DISMATCH_ERR'];
        if ($actionType == "add") {
            $this->sql->query("
								SELECT
										COUNT(*) as `count`
								FROM
										`#__#users_v2`
								WHERE
										`login` = '" . $this->sql->escape($postArray['login']) . "'
										", true);
            if ($this->sql->result['count'] != 0)
                return $this->lang['DUBLICATE_LOGIN_ERR'];
        }

        return false;
    }

    /**
     * Функция отображения формы управления пользователями
     *
     * @return string HTML код
     *
     */
    public function getManagerUsersForm() {
        // SQL
        $this->sql->query("
							SELECT
									*
							FROM
									`#__#users_v2`
							");

        $smarty = clone $this->smarty;
        $smarty->assign("users", $t = $this->sql->getList());
        return $smarty->fetch(api::setTemplate($this->templatePath . "admin/manager.users.tpl"));
    }

    function __construct() {
        global $_GET, $_POST, $_FILES, $sql, $API, $_SESSION, $uri, $module, $_SERVER;
        $this->getArray = &$_GET;
        $this->postArray = &$_POST;
        $this->filesArray = &$_FILES;
        $this->serverArray = &$_SERVER;

        $this->uriArray = &$uri;
        $this->module = &$module;

        $this->returnPath = $this->mDir == $this->module ? "/" . $this->mDir . "/index.html" : "/" . $this->module . "/" . $this->mDir . "/";


        if (isset($_SESSION[(string) ($this->mDir) . "_sess"]['returnPath'])) {
            $this->returnPath = $_SESSION[(string) ($this->mDir) . "_sess"]['returnPath'];
        }


        $this->sql = &$sql;
        $this->api = &$API;
    }

}

?>
