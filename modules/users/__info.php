<?php
if (!defined("API")) {
	exit("Main include fail");
}

$key         	= "users";
$version 		= "3 Beta";
$buildSerial 	= "08-018-001";

$mname['ru']    	 = "Пользователи";
$adminMenu['ru'] = array(
						array("admin/".$key."/managerUsers.php", "Управление"),
						);
?>
