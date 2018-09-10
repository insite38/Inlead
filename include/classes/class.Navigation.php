<?php
class navigation
{
    // Attributes
    var $navigationMenuArray = array();

    // Class operations
    function setMainPage($string, $ref = "")
    {
        if (empty($ref)) {
            $ref = "/";
        }
        $this->navigationMenuArray[0]['title'] = $string;
        $this->navigationMenuArray[0]['ref'] = $ref;
    }

    function add($title, $ref)
    {
        global $API;
        if (!isset($this->navigationMenuArray) || !is_array($this->navigationMenuArray)) {
            $this->setMainPage($API['main']['config']['defaultMainPageNavigationTitle']['value']);
        }
        $nextArrayOffset = count($this->navigationMenuArray);
        $this->navigationMenuArray[$nextArrayOffset]['title'] = $title;
        $this->navigationMenuArray[$nextArrayOffset]['ref'] = $ref;

        return true;
    }

    function get()
    {
        global $lang;
        $return = "";
        if (!isset($this->navigationMenuArray) || !is_array($this->navigationMenuArray)) {
            $this->setMainPage(api::getConfig("main", "api", "mainPageInNavigation"));
        }

        $nextOffset = 0;
        $count = count($this->navigationMenuArray);
		$return .= '<a class="navigation" href="/">' ."Р“Р»Р°РІРЅР°СЏ" . '</a>';
	
        foreach ($this->navigationMenuArray as $key => $value) {
	
            if (isset($this->navigationMenuArray[$nextOffset])) {
				if ($key>0) {
					if ($nextOffset < $count-1) {
						$return .= '<a class="navigation" href="' . $this->navigationMenuArray[$key]['ref'] . '">' .$this->navigationMenuArray[$key]['title'] . '</a>';
					} 
					else {
						$return .= $this->navigationMenuArray[$key]['title'];
					}
				}	
				$nextOffset = $key + 1;
			}
        }

        return $return;
    }

 function get2()
    {
        global $lang;
        $return = "";
        if (!isset($this->navigationMenuArray) || !is_array($this->navigationMenuArray)) {
            $this->setMainPage(api::getConfig("main", "api", "mainPageInNavigation"));
        }

        $nextOffset = 0;
        $count = count($this->navigationMenuArray);
		$return .= '';
	
        foreach ($this->navigationMenuArray as $key => $value) {
	
            if (isset($this->navigationMenuArray[$nextOffset])) {
				if ($key>0) {
					if ($nextOffset < $count-1) {
						$return .= $this->navigationMenuArray[$key]['ref'];
					} 
					else {
						$return .= '';
					}
				}	
				$nextOffset = $key + 1;
			}
        }

        return $return;
    }
	

    // Core function to set class attribute (PHP4/5 support only)
    function set_attribute($value)
    {
        if (!isset($navigationMenuArray) || !is_array($navigationMenuArray)) {
            $this->navigationMenuArray = array();
        }

        return $value;
    }

}


?>