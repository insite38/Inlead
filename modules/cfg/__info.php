<?php
if (!defined("API")) {
    exit("Main include fail");
}

$key = "cfg";
$version = "alpha";
$buildSerial = "00-000-001";
$groupModule = "admin";

$mname['ru'] = "Конфиг";
$adminMenu['ru'] = array(
    array($groupModule . "/" . $key . "/index.php", "Просмотр городов"),
);