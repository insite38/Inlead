<?php
if (!defined("API")) {
	exit("Main include fail");
}

$cfg = new cfg();
$cfg->uri 		= $uri;
$cfg->sql 		= &$sql;
$cfg->api		= &$API;

if (!isset($uri[3]) || empty($uri[3]) || !preg_match("/^[0-9a-z]+$/i", $uri[3]) || !file_exists("modules/".$uri[3]."/__cfg.php") || !is_readable("modules/".$uri[3]."/__cfg.php")) {
	page404();
}

require("modules/".$uri[3]."/__cfg.php");

switch ($rFile) {
	case "index.php":
		$cfg->show();

	break;

	case "save.php":
		$cfg->save();
	break;

	default:
		page404();
	break;
}


$API['content']  = $cfg->return['content'];
@$API['pageTitle'] = 'Настройки модуля &laquo;' . $cfg->uri[3] . '&raquo;';

?>
