<?php
if (!defined("API")) {
    exit("Main include fail");
}


class mCfg extends cfg
{
    public $cfg = array(
        array(
            "defaultTemplate",
            "Основной шаблон для модуля",
            "Оставьте на заполненым, если необходимо использовать основной шаблон системы",
            "text",
            "",
            "no"),
    );


    public static function check()
    {
        return true;
    }
}