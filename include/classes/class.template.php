<?
class template {
        public    $debug;
        protected $fileName;
        protected $variables=array();
        protected $content;

		public function file($fToParce) {
                if (!file_exists("templates/".$fToParce) || !is_readable("templates/".$fToParce)) {
                        exit("Class template, function file(): can't find file: templates/".$fToParce);
                }
                $this->fileName="templates/".$fToParce;
                return true;
        }

        public function assign($valueName, $valueValue) {
                $this->variables[$valueName]=$valueValue;
                return true;
        }

        protected function readFile() {
                $fID=fopen($this->fileName, "r");
                @flock($fID, LOCK_SH);
                $this->content=fread($fID, filesize($this->fileName));
                @flock($fID, LOCK_UN);
                fclose($fID);
                return true;
        }

		public function parcePlugins($plugName, $plugParams) {
			if (file_exists("plugins/".$plugName."/index.php")) {
				require_once("plugins/".$plugName."/index.php");

				if (!class_exists("plugin_".$plugName)) {
					return ("Plugin $plugName error: can't fined class <strong>plugin_".$plugName."</strong>");
				}

				if (!method_exists("plugin_".$plugName, "start")) {
					return ("Plugin start fail: can't find method <strong>start</strong> in <strong>plugin_$plugName</strong> class");
				}
				eval("\$$plugName = new plugin_".$plugName."(\$plugParams);");
				return $$plugName->start($plugParams);
			} else {
				return "Can't fined plugins/".$plugName."/index.php";
			}

		}

        protected function parce($inText) {
                global $module;
                $inText=preg_replace("/\{%%[\s]*([a-z0-9]+)\s*\(([^\)]*)\)[\s*]%%\}/ise", "\$this->parcePlugins('\\1', '\\2')", $inText);
                $inText=@preg_replace("/\{([a-z0-9]+)\}/ise", "\$this->variables['\\1']", $inText);

                $inText=preg_replace("/\[HOST\]/ise", "getenv(\"HTTP_HOST\")", $inText);
                $inText=preg_replace("/\[SELF\]/ise", "\"/\".getenv(\"REDIRECT_QUERY_STRING\")", $inText);
                $inText=preg_replace("/\[MODULE\]/ise", "\$module", $inText);
                $inText=preg_replace("/tip\=\"([^\"]+)\"\s*tipsize=\"([^\"]+)\"/i", "onMouseOver=\"toolTip('\\1', \\2)\" onMouseOut=\"toolTip()\"", $inText);
                $inText=preg_replace("/\[CONFIG:\s([a-z0-9]+),([a-z0-9]+),([a-z0-9]+)\]/ise", "api::getConfig('\\1', '\\2', '\\3')", $inText);
                $inText=preg_replace("/\[selectNavigationShow=\"([A-Z]*)\"\]/ie", "\$this->genSelectNavigationShow('\\1')", $inText);
				$inText=preg_replace("/\[cityNavigationShow=\"([A-Z]*)\"\]/ie", "\$this->genCityNavigationShow('\\1')", $inText);
                $inText=preg_replace("/\[selectLang=\"([A-Z]*)\"\]/ie", "\$this->genSelectLang('\\1')", $inText);

                $inText=preg_replace("/\[adminMenu]/ie", "api::getAdminNavigation()", $inText);
                $inText=preg_replace("/\[langLinks]/ie", "api::showLangLinks()", $inText);

                return $inText;
        }


        public function genSelectNavigationShow($defaultValue) {

			if (empty($defaultValue)) {
				$defaultValue = "y";
			}
        	$entries = array("y", "n");

        	$return  = "<select name=\"navigationShow\" size=\"1\">";
        	foreach ($entries as $value) {
        		$return.= "<option value=\"".$value."\"".($defaultValue == $value ? " selected=\"selected\"" : "").">".strtoupper($value)."</option>";
        	}
        	$return.= "</select>";

        	return $return;
        }
		
		public function genCityNavigationShow($defaultValue) {

			if (empty($defaultValue)) {
				$defaultValue = "irk"; 
			}
        	$entries = array("irk"=>"Социальная поддержка", "ang"=>"Спорт", "uso"=>"Творчество", "bla"=>"Благоустройство");

        	$return  = "<select name=\"city\" size=\"1\">";
        	foreach ($entries as $key => $value) {
        		$return.= "<option value=\"".$key."\"".(sl($defaultValue) == sl($key) ? " selected=\"selected\"" : "").">".strtoupper($value)."</option>";
        	}
        	$return.= "</select>";

        	return $return;
        }

        public function genSelectLang($defaultValue) {
        	global $lang, $allLang;
        	if (empty($defaultValue)) {
        		$defaultValue = $lang;
        	}

        	$return = "<select name=\"lang\" size=\"1\">";
        	foreach ($allLang as $value) {
        		$return.= "<option value=\"".$value."\"".(sl($defaultValue) == sl($value) ? " selected=\"selected\"" : "").">".strtoupper($value)."</option>";
        	}
        	$return.= "</select>";

        	return $return;
        }

        public function out() {
                $this->readFile();
                echo($this->parce($this->content));
        		return true;
        }

        public function get() {
                $this->readFile();
                return($this->parce($this->content));
        }

        public function stop($value = "") {
                $this->assign("CONTENT", $value);
                $this->readFile();
                exit($this->parce($this->content));
        }
        protected function genPageListEval($array, $currentPage = 1, $class = "") {
                $return = "";
                foreach ($array as $key=>$value) {
                        if ($key != $currentPage) {
                                $return.="<a class=\"".$class."\" href=\"".$value."\">[".$key."]</a> ";
                                } else {
                                        $return.="<font class=\"".$class."\" href=\"".$value."\"><strong>[".$key."]</strong></font> ";
                                        }
                        }

                return $return;
                }

        public function genPageList($allData, $perPage, $start, $link, $class = "") {
              $count = 1;
              $array=array();

              $allPage = intval($allData / $perPage);

              // float increment
              if ($allPage * $perPage < $allData) {
                      $allPage ++;
                      }

              // current page
              $curPage = intval($start / $perPage);

              // generate array
              while ($count <= $allPage) {
                      $array[$count] = str_replace("[PAGE]", ($count-1) * $perPage, $link);
                      $count++;
                      }

              // generate html code
              return $this->genPageListEval($array, ($start / $perPage) + 1, $class);
               }
        public function assignOrders($typesArray, $currentOrderBy, $currentOrderType, $templateValueName) {
        	eval("global \$".$templateValueName.", \$base;");

        	if (array_search($currentOrderBy, $typesArray) === false) {
        		$currentOrderBy=$typesArray[0];
        	}

			$imageName = ($currentOrderType == "desc" ? "<img border=\\\"0\\\" src=\\\"".$base."/templates/ru/images/api/down.gif\\\">" : "<img border=\\\"0\\\" src=\\\"".$base."/templates/ru/images/api/up.gif\\\">");

        	foreach ($typesArray as $key=>$value) {
        		$linkID = $key+1;

        		if ($currentOrderBy === $value) {
        			$orderType = ($currentOrderType == "desc" ? "asc" : "desc");
        			eval ("\$".$templateValueName."->assign(\"imgOrder".$linkID."\", \"".$imageName."\");");
        		} else {
        			$orderType = "desc";
        		}
        		eval ("\$".$templateValueName."->assign(\"orderLink".$linkID."\", \"sortBy=".$value."&sortType=".$orderType."\");");
        	}

        return true;
        }

		static function genTree($tableName, $idCol = "id", $ownCol = "ownerId", $titCol = "title", $start = 0, $halt = -1, $orderBy = "") {
			global $API, $return, $lang;
			$return = array();
			$sqlId = mysql_query("SELECT `".$idCol."`, `".$titCol."` FROM ".$API['config']['mysql']['prefix'].$tableName." WHERE ".$ownCol." = '".$start."' && `lang` = '".$lang."'".(!empty($orderBy) ? " ORDER BY `".$orderBy."`" : ""));

			while ($result = mysql_fetch_array($sqlId)) {
				$id    = $result[0];
				$title = $result[1];

				if ($id == $halt) {
					continue;
				}

				$return[$id]['value'] = $title;
				$return[$id]['level'] = 0;

				template::genTreeEval($tableName, $idCol, $ownCol, $titCol, $id, $halt, 1, $orderBy);
			}
		return $return;
		}

		static function genTreeEval($tableName, $idCol = "id", $ownCol, $titCol, $start, $halt, $level, $orderBy) {
			global $API, $return, $lang;
			$sqlId = mysql_query("SELECT `".$idCol."`, `".$titCol."` FROM ".$API['config']['mysql']['prefix'].$tableName." WHERE ".$ownCol." = '".$start."' && `lang` = '".$lang."'".(!empty($orderBy) ? " ORDER BY `".$orderBy."`" : ""));
			while ($result = mysql_fetch_array($sqlId)) {
				$id    = $result[0];
				$title = $result[1];

				if ($id == $halt) {
					continue;
				}


				$return[$id]['value'] = $title;
				$return[$id]['level'] = $level;

				template::genTreeEval($tableName, $idCol, $ownCol, $titCol, $id, $halt, $level+1, $orderBy);

			}

		}


        function __construct($file = "") {
        	global $API, $lang;
        	$this->assign("base",			$API['config']['base']);
        	$this->assign("supportEmail", 	$API['config']['mail']['supportEmail']);
        	$this->assign("curLang", 		@$lang);
        	$this->assign("curYear",		date("Y"));


        	if (!empty($file)) {
        		$this->file($file);
        	}
        }
}

?>