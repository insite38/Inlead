<?
/* REDIRECTION
 * 0 - Оставляем логику как есть
 * 1 - Перенаправляем 301 редиректом с страниц вида page.html?lang=ru на page.html
 * 2 - Перенаправляем 301 редиректом с страниц вида page.html на page.html?lang=ru
 */
$redirect = 1;

error_reporting(E_ALL);
ini_set("display_errors", "on");

unset($content, $api, $class, $templateMain, $login, $password, $_SESSION, $subTemplateBody, $subTemplateItem, $admin, $module, $session);
session_start();

// Include main config file and main functions
include("include/__config.php");
include("include/__functions.php");


// Init fckeditor system (mpuck)
include("include/__wysiwyg.php");

// setingup language;
$lang = (!empty($_GET['lang']) ? sl($_GET['lang']) : $API['config']['defaultlang']);

// Including classes from directory "include/classes/"
if (!file_exists("include/classes/") || !is_readable("include/classes/")) {
    exit("Main API: can't access to include/classes/ directory");
}

$dirID = opendir("include/classes/");
while ($fileName = readdir($dirID)) {
    if (!preg_match("/^[a-z\.]+\.php$/i", $fileName)) {
        continue;
    }
    require("include/classes/" . $fileName);
}

// Creating Classes
$mainTemplate = new template();
$sql = new MySQL();
$project = new projectClass();
$socket = new Socket();
$parcer = new Parcer();
$main = new api();
$install = new install();
$navigation = new navigation();
$users = new users();
$mail = new mail();

// checkInstall MainAPI
$defaultLang = $API['config']['defaultlang'];

// check present lang;
$main->checkLang($lang);

// MySQL
$sql->server = $API['config']['mysql']['server'];
$sql->username = $API['config']['mysql']['username'];
$sql->password = $API['config']['mysql']['password'];
$sql->db = $API['config']['mysql']['db'];
$sql->prefix = $API['config']['mysql']['prefix'];

/* определяем город для фронтэнда по субдомену */
$serverName = $_SERVER['SERVER_NAME'];
$subDomian = explode('.', $serverName);
$countSubD = count($subDomian);



/*
при разработке на субдомене, обязательно сменить lego на нужный субдомен
*/
if($subDomian[0] != 'www'){
    if($countSubD>2){
        // $cityDomian = $subDomian[0];
        if($subDomian[0] == 'lego'){
            $cityDomian = 'irkutsk';
        }else{
            $cityDomian = $subDomian[0];
        }
    }else{
        $cityDomian = 'irkutsk';
    }
}else{
    if($countSubD>3){
        if($subDomian[1] == 'lego'){
            $cityDomian = 'irkutsk';
        }else{
            $cityDomian = $subDomian[1];
        }
    }else{
        $cityDomian = 'irkutsk';
    }
}

$API['city'] = $_SESSION['cityname'] = $cityDomian;
//print_r($cityDomian);


/* определяем город */
$dbcity = clone $sql;
$dbcity->query("SELECT * FROM `city`");
$city = $dbcity->getList();
$arr_city = array();
foreach ($city as $item){
    $arr_city[$item['id']]=array(
        'city' => $item['city'],
        'name' => $item['name']
    );
}
if(empty($_SESSION['cityname'])){
    $_SESSION['cityname'] = 'irkutsk';
}
$_SESSION['arr_city'] = $arr_city;
if(empty($_SESSION['citynameadmin'])){
    $_SESSION['citynameadmin'] = 'irkutsk';
}
if(!empty($_POST['submitCityAdminSelect'])){
    $_SESSION['citynameadmin'] = $_POST['city'];
}

// getAllLang
$dirId = opendir("templates");
$allLang = array();
while ($fName = readdir($dirId)) {
    if (preg_match("/^[a-z]+$/i", $fName) && is_dir("templates/" . $fName)) {
        $allLang[] = $fName;
    }
}

// Main API initaliszation
$sql->query("SELECT `category`, `type`, `name`, `value`, `description`, `lang` FROM #__#config");
while ($sql->next_row()) {
    $category = $sql->result[0];
    $type = $sql->result[1];
    $name = $sql->result[2];
    $value = $sql->result[3];
    $description = $sql->result[4];
    $sLang = $sql->result[5];

    $API[$category][$type][$name][$sLang]['value'] = $value;
    $API[$category][$type][$name][$sLang]['descr'] = $description;
}

$API['template'] = $main->setTemplate("index.html");
$API['title'] = $main->getConfig("main", "api", "projectTitle");
$API['md'] = $main->getConfig("main", "api", "md");
$API['mk'] = $main->getConfig("main", "api", "mk");
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = $defaultLang;
if (!empty($_GET['lang'])) $_SESSION['lang'] = $_GET['lang'];

if ($_SESSION['lang'] == "en") {
    $API['lang'] = '
	<a href="/index.html?lang=ru">Russian</a><br>
	<a class="icon-secondary"><strong>English</strong></a><br>
	<a href="/index.html?lang=zh">中文</a>';
}

if ($_SESSION['lang'] == "zh") {
    $API['lang'] = '
	<a href="/index.html?lang=ru">Russian</a><br> 
	<a href="/index.html?lang=en">English</a><br>
	<a class="icon-secondary"><strong>中文</strong></a>';
} elseif ($_SESSION['lang'] == "ru") {
    $API['lang'] = '
	<a class="icon-secondary"><strong>Russian</strong></a><br> 
	<a href="/index.html?lang=en">English</a><br>
	<a href="/index.html?lang=zh">中文</a>
	';
}

// Parcing uri
$uriToParce = getenv('REQUEST_URI');

if (!empty($API['config']['base'])) {
    $uriToParce = substr($uriToParce, strlen($API['config']['base']));
}

$uri = array_filter(explode("/", preg_replace('#/+#', '/', '/' . $uriToParce)));


// Seting up Uri params
$uriParams = array();
foreach ($uri as $key => $value) {
    if (preg_match("/^([a-z0-9]{1,10})\-([a-z0-9\%\+]{1,50})$/i", $uri[$key], $match)) {
        $uriParams[$match[1]] = slash(urldecode($match[2]));
        unset($uri[$key]);
    }
}

if (!preg_match("/^[a-z0-9_]+$/i", @$uri[1])) {
    $module = "none";
    $rFile = "index.php";
} else {
    $module = $uri[1];
}


// Gets request filename
if (count($uri) > 1 && preg_match("/([a-z0-9_]+\.(?:[a-z0-9]{2,4}))[$\/]*/i", getenv('REQUEST_URI'), $match)) {
    $allCount = count($uri);
    unset($uri[$allCount]);
    $rFile = $match[1];
} else {
    if (count($uri) > 1 && !preg_match("/\/\?/", getenv('REQUEST_URI')) && substr(getenv('REQUEST_URI'), -1, 1) != "/") {
        if ($module != "none") go(getenv('REQUEST_URI') . "/");
    }
    $rFile = "index.php";
}

$curYear = intval(date("Y"));
$rusMonth = explode(" ", " января февраля марта апреля май июня июля августа сентября октября ноября декабря");
$uric = count($uri);
$base = $API['config']['base'];


// Запускаем шаболонизатор Smarty
define('SMARTY_DIR', 'include/libs/');
require(SMARTY_DIR . 'Smarty.class.php');

$smarty = new Smarty();
$smarty->assign("curLang", $lang);
$smarty->assign("projectTitle", $main->getConfig('main', 'api', 'projectTitle'));
$smarty->assign("rFile", $rFile);

// Запускаем Ajax

// Запускаем класс работы с почтой PHP Mailer
require("include/phpmailer/class.phpmailer.php");
$mail = new PHPMailer();
$mail->SetLanguage("ru", "include/phpmailer/language/");
$mail->PluginDir = "include/phpmailer/language/";
$mail->CharSet = "utf-8";
$mail->IsMail();

// Если идет запрос с параметром lang=ru то перенаправляем на страницу без параметра
//$e = explode("?", getenv('REQUEST_URI'));
if ($redirect == 1 && !empty($e[1]) and stristr(getenv('REQUEST_URI'), "admin") == false) {
    $rr = parse_url(getenv('REQUEST_URI'));
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $rr['path']);
    exit();
}

// Если идет запрос БЕЗ параметра то перенаправляем на страницу c параметром lang=ru
if ($redirect == 2 && empty($_GET['lang'])) {
    $rr = parse_url(getenv('REQUEST_URI'));
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $rr['path'] . "/?lang=ru");
    exit();
}

if (isset($uri[1]) && $uri[1] == 'robots.txt') {
    $sql->query('SELECT `robotext` FROM `robots` WHERE `city_name` = "'.$_SESSION['cityname'].'"', true);
    header('Content-type: text/plain');
    echo $sql->result['robotext'];
    exit();
}

// Seting up navigation
$navigation->setMainPage($main->getConfig("main", "api", "mainPageInNavigation"));

if (count($uri) < 1 || $uri[1] === "index.php" || $uri[1] === "index.html" || $uri[1] === "index.php?lang=$lang" || $uri[1] === "index.html?lang=$lang" || (count($uri) === 1 && preg_match("/\/\?/", getenv('REQUEST_URI')))) {
    $module = $uri[1] = "page";
    $uric = count($uri);
}

if ($module == "none") {
    // redirect to page module
    //go($base."/page/index.php?lang=".$lang);
} else {
    define("API", 1);
    $API['template'] = trim(($templateToSet = api::getConfig("modules", $module, "defaultTemplate")) != "" ? api::settemplate($templateToSet) : $API['template']);
    $API['md'] = (trim($main->getConfig("modules", $module, "md")) != "" ? $main->getConfig("modules", $module, "md") : $API['md']);
    $API['mk'] = (trim($main->getConfig("modules", $module, "mk")) != "" ? $main->getConfig("modules", $module, "mk") : $API['mk']);

    if (file_exists("modules/" . $uri[1] . "/__" . $lang . ".php")) {
        require("modules/" . $uri[1] . "/__" . $lang . ".php");
        @$lng = $l;
    } else {
        if (file_exists("modules/" . $uri[1] . "/__" . $defaultLang . ".php")) {
            require("modules/" . $uri[1] . "/__" . $defaultLang . ".php");
            @$lng = $l;
        }
    }

    if (file_exists("modules/" . $module . "/__classes.php")) {
        require("modules/" . $module . "/__classes.php");
    }

    if (file_exists("modules/" . $module . "/index.php")) {
        require "modules/" . $module . "/index.php";
    } else {
        page404();
    }
}


// Show main content
$mainTemplate->file($API['template']);
@$mainTemplate->assign("title", (!@empty($API['siteTitle']) ? $API['siteTitle'] : $API['title']));
@$mainTemplate->assign("pageTitle", $API['pageTitle']);
@$mainTemplate->assign("content", $API['content']);
@$mainTemplate->assign("navigation", $API['navigation']);
@$mainTemplate->assign("md", $API['md']);
@$mainTemplate->assign("mk", $API['mk']);

@$mainTemplate->assign("mainPageInNavigation", $main->getConfig("main", "api", "mainPageInNavigation"));

$mainTemplate->out();
