<?php
if (!defined("API")) {
    exit("Main include fail");
}

$key = "city";
$version = "alpha";
$buildSerial = "00-000-001";
$groupModule = "admin";

$mname['ru'] = "Города";
$adminMenu['ru'] = array(
    array($groupModule . "/" . $key . "/index.php", "Просмотр городов"),
);