<?php

####################################################
## VLAD
####################################################
# Плагин отображает параметр конфигурации
# на входе подается название переменной
####################################################

class plugin_confShow {
	protected $pluginParams = array();
	protected $session		= array();
	protected $smarty		= array();
	protected $sql;

	function start() {
		$this->sql->query("SELECT `value` FROM `#__#config` WHERE `type`='api' AND `name`='".$this->pluginParams['name']."'", true);
		return @$this->sql->result['value'];
	}

	function __construct($params) {
		global $smarty, $_SESSION, $sql;
		$this->smarty = &$smarty;
		$this->session = &$_SESSION;
		$this->sql = &$sql;
     	$this->pluginParams = api::setPluginParams($params);
	}

}
?>