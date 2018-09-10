<?php
if (!defined("API")) {
    exit("Main include fail");
}

class mCfg extends cfg
{
    public $cfg = array(

        array(
            "sendEmailTo",
            "Основной адрес электронной почты",
            "",
            "text",
            "",
            "yes"
        ),

    );

    public function check()
    {
        if (!preg_match("/^[0-9a-z_\.\-]+@[0-9a-z\.\-]+\.[a-z]{2,6}$/i", $this->postArray['cfgParams']['sendEmailTo'])) {

            $this->errorParam = "sendEmailTo";
            return $this->show();
        }

        return true;
    }
}

?>
