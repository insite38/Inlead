<?php
/*
Модуль администрирования
Системы управления содержимым сайта
*/
global $API;

if (!defined("API")) {
    exit("Main include fail");
}
//print_r($_SESSION);
if(isset($_SESSION['auth']) && isset($_SESSION['auth']['auth']) && $_SESSION['auth']['auth']=='1'){
    if($_SESSION['auth']['userId']!='0'){
        go('/');
    }
}

if (@$uri[2] == "logout") {
    $users->clearAuth();
    go("/index.php");
}

if (empty($uri[2])) { //Проверка что нет продолжения после "/admin/"
    go($base . "/admin/page/index.php");
}

if (!isset($uri[2]) || !preg_match("/^[a-z0-9_]+$/i", $uri[2], $match) || !file_exists("modules/" . $uri[2]) || !is_dir("modules/" . $uri[2]) || !file_exists("modules/" . $uri[2] . "/admin.php") || $uri[2] == "admin") { // Если есть продолжение проверяем есть ли такой модуль, нет? тогда 404
    page404();
}

$trace = $users->checkAccessToModule($uri[2], $lang);

if ($users->getSinginUserInfo("accessRight") == 2 && $users->checkAccessToModule($uri[2], $lang) !== true) {
    // if moderation go to the thist accessibility module
    $modulesAccess = $users->getSinginUserInfo("accessModules");
    $toLocate = split(":", $modulesAccess[0]);
    //echo "Try to redirect to "."/admin/".$toLocate[0]."/index.php?lang=".$toLocate[1];
    go("/admin/" . $toLocate[0] . "/index.php?lang=" . $toLocate[1]);
    exit(0);
}


if ($users->checkLogin() !== true || $users->checkAccessToModule($uri[2], $lang) !== true)
{
    $users->showLoginForm();
    exit();
}

// including admin sub module in module;
$install->checkInstall($uri[2]);
if (file_exists("modules/" . $uri[2] . "/__classes.php")) {
    include("modules/" . $uri[2] . "/__classes.php");
}


// Load lang
if (file_exists("modules/" . $uri[2] . "/__" . $lang . ".php")) {
    require("modules/" . $uri[2] . "/__" . $lang . ".php");
    @$admLng = $l;
} else {
    if (file_exists("modules/" . $uri[2] . "/__" . $defaultLang . ".php")) {
        require("modules/" . $uri[2] . "/__" . $defaultLang . ".php");
        @$admLng = $l;
    }
}
/* Работа с городами в админке */
$cityAdminSelect = $_SESSION['arr_city'];
$cityAdmin = '';
foreach ($cityAdminSelect as $item){
    if($_SESSION['citynameadmin'] == $item['name']){
        $activeCity = 'selected';
    }else{
        $activeCity = '';
    }
    $cityAdmin .= '<option value="'.$item['name'].'" '.$activeCity.'>'.$item['city'].'</option>';
}

/* конец работы с городами в админке */
$API['template'] = api::setTemplate("admin.html");
$mainTemplate->assign("cityAdminSelect", $cityAdmin);
$mainTemplate->assign("selectAdminLang", adminSelectLang($defaultLang));

require("modules/" . $uri[2] . "/admin.php");