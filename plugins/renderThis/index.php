<?php

class plugin_renderThis
{

    protected $pluginParams = array();
    protected $result = '';

    function __construct($params)
    {
        global $API;

        $this->pluginParams = api::arrayKeysSL(api::setPluginParams($params));

        $p = array();

        if (isset($this->pluginParams['name'])) {
            $template = new template(api::setTemplate('plugins/renderThis/' . $this->pluginParams['name'] . '.html'));

            if (isset($this->pluginParams['params']))
            {
                $p = explode(',', $this->pluginParams['params']);

                foreach ($p as $item)
                {
                    $template->assign($item , api::getConfig('main', 'api', $item));
                }

            }
		
            $template->assign('title' , $API['title']);
            $template->assign('md' , $API['md']);
            $template->assign('mk' , $API['mk']);
			// $template->assign('getDayMonth' , $API['DayMonth']);
			$template->assign('city', $API['city']);
			// $template->assign('dateC', $API['dateC']);
			// $template->assign('globalFilter', $API['globalFilter']);

            $this->result = $template->get();
        }

        return true;
    }

    public function start()
    {
        return $this->result;
    }

}