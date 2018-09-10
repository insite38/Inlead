<?php
if (!defined("API")) {
	exit("Main include fail");
}

$config = new config();
$config->lang		= $admLng;
$config->curLang	= $lang;

switch ($rFile) {
	case "index.php":
		$config->show();
	break;

	case "edit.php":
		$config->edit();
	break;

	case "editGo.php":
		$config->editGo();
	break;

	case "add.php":
		$config->add();
	break;


	case "addGo.php":
		$config->addGo();
	break;

	case "delete.php":
		$config->delete();
	break;
	
	case "robots.php":
		$config->robots();
	break;
}

$API['content'] = $config->data['content'];
$API['pageTitle'] = $config->lang['pageTitle'] . ' / ' . $config->page_title;

?>
