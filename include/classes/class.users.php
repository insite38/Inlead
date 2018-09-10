<?php

class users
{
    protected $loginInfo = array();

    function __construct()
    {
        if (!isset($_SESSION['auth'])) {
            $_SESSION['auth'] = array();
        }

        $this->loginInfo = &$_SESSION['auth'];
    }

    public function showLoginForm()
    {
        $template = new template(api::setTemplate("api/auth.html"));
        $template->stop(1);

    }

    public function checkLogin()
    {
        global $_POST, $API, $sql;
        if (isset($_POST['authLogin']) && isset($_POST['authPassword'])) {
            // create template variables;
            $login = $API['config']['admin']['login'];
            $password = $API['config']['admin']['password'];

            // check for suid
            if ($login === trim($_POST['authLogin']) && $password === $_POST['authPassword']) {
                // is suid
                $this->saveAuthDataToSession(
                    0,
                    $login,
                    "Super user access",
                    "root@localhost",
                    1,
                    array(),
                    time(),
                    "0000-00-00 00:00:00",
                    "Super user account",
                    0,
                    true);
                return true;
            }
            return false;
        }

        if ($this->getSinginUserInfo() === true) {
            //echo "Auth is true";
            return true;
        }
        return true;
    }

    function saveAuthDataToSession(
        $userId,
        $userLogin,
        $userFio,
        $userEmail,
        $accessRights,
        $accessModules,
        $lastLogin,
        $regTime,
        $addedBy,
        $isSu = false
    )
    {


        $this->loginInfo = array(
            "auth" => true,
            "userId" => $userId,
            "userLogin" => $userLogin,
            "userFio" => $userFio,
            "userEmail" => $userEmail,
            "accessRight" => $accessRights,
            "accessModules" => $accessModules,
            "lastLogin" => $lastLogin,
            "regTime" => $regTime,
            "addedBy" => $addedBy,
            "isSu" => $isSu
        );

        return true;

    }

    public function getSinginUserInfo($key = "auth")
    {
        return isset($this->loginInfo[$key]) ? $this->loginInfo[$key] : false;
    }

    public function checkAccessToModule($moduleDir, $lang = 'ru')
    {
        if (@$this->loginInfo['isSu'] === true) return true;
        if ((int)@$this->loginInfo['accessRight'] === 1) return true;
        if (isset($this->loginInfo['accessModules']) && is_array($this->loginInfo['accessModules']) && array_search($moduleDir . ":" . $lang, $this->loginInfo['accessModules'], true) !== false) {
            return true;
        }
        return false;
    }

    public function clearAuth()
    {
        $this->loginInfo = array();
    }
}

?>