<?
class api {
	private static $modulesInfo;

	public function checkLang($lang) {
		if (!file_exists("templates/".$lang)) {
			page404();
		}
	}

	public static function getConfig($category, $type, $name) {
		global $API, $lang, $defaultLang;
		if (isset($API[$category][$type][$name][$lang]['value'])) {
			return $API[$category][$type][$name][$lang]['value'];
		} elseif (isset($API[$category][$type][$name][$defaultLang]['value'])) {
			return $API[$category][$type][$name][$defaultLang]['value'];
		} else {
			return "";
		}
	}

	public static function setTemplate($templateName) {
		global $lang, $defaultLang;

		if (file_exists("templates/".$lang."/".$templateName)) {
			return $lang."/".$templateName;
		} elseif (file_exists("templates/".$defaultLang."/".$templateName)) {
			return $defaultLang."/".$templateName;
		} else {
			exit("Can't find template <strong>templates/".$lang."/".$templateName."</strong> or <strong>templates/".$defaultLang."/".$templateName."</strong>");
		}
	}

	public function loadModulesInfo($lang, $defaultLang) {
		global $key, $version, $buildSerial, $name, $adminMenu, $mname;
		$return = array();
		$dirId  = opendir("modules/");

		$moduleName=array(
		"page",
		"formRequest",
		);
		
		$i=0;
		
		// while (false !== ($dirName = readdir($dirId))) 
		while ( $i<count($moduleName)) {
				
			$dirName=$moduleName[$i];
	
				// if (is_dir("modules/".$dirName) && $dirName != "." && $dirName != "..") {
				// 	// check file exists
				if (!file_exists("modules/".$dirName."/__info.php")) {
					continue;
				}

				// loading info
				// unset($key, $version, $buildSerial, $name, $adminMenu, $mname);
				
				include("modules/".$dirName."/__info.php");

				if (isset($mname[$lang])) {
					$return[$key]['name'] 		= $mname[$lang];
				} else {
					$return[$key]['name'] 		= $mname[$defaultLang];
				}
				
				if (isset($adminMenu[$lang])) {
					$return[$key]['adminMenu']	= $adminMenu[$lang];
				} else {
					$return[$key]['adminMenu'] = $adminMenu[$defaultLang];
				}

				$return[$key]['version']		= $version;
				$return[$key]['buildSerial']	= $buildSerial;

				// }
			$i++;

		}

		return $return;

	}

	public static function getAdminNavigation() {
		global $lang, $defaultLang, $main, $users;
		if (!isset(api::$modulesInfo) || !is_array(api::$modulesInfo)) {
			api::$modulesInfo=$main->loadModulesInfo($lang, $defaultLang);

		}



		$subTemplateBody = new template();
		$subTemplateItem = new template();

		$subTemplateBody->file(api::setTemplate("modules/admin/admin.menu.left.body.html"));
		$subTemplateItem->file(api::setTemplate("modules/admin/admin.menu.left.item.html"));

		$return = "";

		foreach (api::$modulesInfo as $key => $empty) {
			if ($users->checkAccessToModule($key, $lang) !== true) {
				continue;
			}
			$body = "";

			foreach (api::$modulesInfo[$key]['adminMenu'] as $adminMenyKey => $empty) {
				if (api::$modulesInfo[$key]['adminMenu'][$adminMenyKey][1] === "br") {
					continue;
				}

				$subTemplateItem->assign("uri", api::$modulesInfo[$key]['adminMenu'][$adminMenyKey][0]);
				$subTemplateItem->assign("title", api::$modulesInfo[$key]['adminMenu'][$adminMenyKey][1]);

				if (@api::$modulesInfo[$key]['adminMenu'][$adminMenyKey+1][1] == "br") {
					$subTemplateItem->assign("br", " class=\"tdAdmMenuBreakLine\"");
				} else {
					$subTemplateItem->assign("br", " class=\"left_menu\"");
				}
				$body.= $subTemplateItem->get();
			}

		
			$subTemplateBody->assign("name", api::$modulesInfo[$key]['name']);
			$subTemplateBody->assign("body", $body);

			$return .= $subTemplateBody->get();
		}

		return $return;

	}

	public function showLangLinks() {
		global $users, $lang;
		$dirId = opendir("templates/");
		$langs = "";
		
		// get assessibility languagers
		$allModules = $users->getSinginUserInfo("accessModules");
		$allAccessLangs = array();

		foreach ($allModules as $key=>$value) {
			$langsTOSplit = split(":", $value);
			if (isset($langsTOSplit[1])) {
				if (array_search($langsTOSplit[1], $allAccessLangs) === false) $allAccessLangs[] = $langsTOSplit[1];
			}
		}
		
		//var_dump($allAccessLangs);
		$langs = "<form action=\"\" method=\"get\"><select name=\"lang\">";
		while ($fileName = readdir($dirId)) {
			if (array_search($fileName, $allAccessLangs) === false && $users->getSinginUserInfo("accessRight") != 1 && !$users->getSinginUserInfo("isSu")) continue;
			
			if (preg_match("/^[a-z0-9]+$/i", $fileName) && is_dir("templates/".$fileName)) {
				//$langs .= "<a href=\"?lang=".sl($fileName)."\">".su($fileName)."</a> &nbsp;";
				if (file_exists("templates/".$fileName."/__menu.txt")) {
					$infoArray = parse_ini_file("templates/".$fileName."/__menu.txt");
					if (isset($infoArray['title'])) {
						$titleToSelectBlock=$infoArray['title']; 
					} else {
						$titleToSelectBlock=su($fileName);
					}
				} else {
					$titleToSelectBlock=su($fileName);
				}
				$langs .= "<option value=\"".$fileName."\"".($lang == $fileName ? " selected" : "").">".$titleToSelectBlock."</option>";
			}
		}
	$langs .= "</select><input type=\"submit\" value=\"OK\"></form>";
	return $langs;
	}

	public static function setPluginParams($params) {
		$array = array();
		preg_replace("/([a-z0-9]+)\:\s*([^;]+);/ie", "\$array['\\1'] = '\\2'", $params);
		return $array;
	}

	public function genFck($name, $defaultValue = "", $height = "450", $width = "100%") {
		$fckForm = new fckEditor($name);
		$fckForm->Value  = $defaultValue;
		$fckForm->Height = $height;
		$fckForm->Width  = $width;
		return $fckForm->createHtml();
	}

	public static function slashData($data, $urldecode = false) {
		if (get_magic_quotes_gpc() == false) {
			if (is_array($data)) return api::slashArray($data, $urldecode); else return addslashes(($urldecode ? urldecode($data) : $data));
		} else {
			return api::urldecodeData($data);
		}
	}

	public static function slashArray($array, $urldecode) {
		$returnArray = array();
		foreach ($array as $key=>$value) {
			if (is_array($array[$key]))  {
				$returnArray[$key] = api::slashArray($array[$key], $urldecode);
			} else {
				$returnArray[$key] = addslashes(($urldecode ? urldecode($value) : $value));
				}
		}
		return $returnArray;
	}

	public function urldecodeData($data) {
			if (is_array($data)) return api::urldecodeArray($data); else return urldecode($data);
	}

	public function urldecodeArray($array) {
		$returnArray = array();
		foreach ($array as $key=>$value) {
			if (is_array($array[$key]))  {
				$returnArray[$key] = api::urldecodeArray($array[$key]);
			} else {
				$returnArray[$key] = urldecode($value);
				}
		}
		return $returnArray;
	}




	public function checkTemplate($string) {
		return (bool)preg_match("/^[a-z0-9\.\_\/]*$/", $string);
	}

	public function genUniversalForm($data, $values, $error = "&nbsp;") {
		$body = "";
		$assignArray = array();

		foreach ($data as $key=>$empty) {
			$halt = false;
			switch($data[$key][2]) {
				case "text":
				case "email":
					$template = new template(api::setTemplate("api/universalForm.item.html"));
					if (isset($data[$key][7])) $body.="<input type=\"hidden\" name=\"".$data[$key][0]."\"".(!empty($data[$key][3]) ? " value=\"".$data[$key][3]."\"" : " value=\"".@$values[$key]."\"").">";
					$inputForm = "<input style=\"width: 90%\" type=\"text\" name=\"".$data[$key][0]."\"".(!empty($data[$key][3]) ? " value=\"".$data[$key][3]."\"" : " value=\"".@$values[$key]."\"")."".(isset($data[$key][7]) ? " disabled" : "").">";
				break;

				case "textarea":
					$template = new template(api::setTemplate("api/universalForm.item.html"));
					$inputForm = "<textarea style=\"width: 90%; height: 150px;\" name=\"".$data[$key][0]."\">".(!empty($data[$key][3]) ? $data[$key][3] : @$values[$key])."</textarea>";
				break;

				case "submit":
					$template = new template(api::setTemplate("api/universalForm.item.submit.html"));
				break;

				case "hidden":
					$body.=	"<input type=\"hidden\" name=\"".$data[$key][0]."\"".(!empty($data[$key][3]) ? " value=\"".$data[$key][3]."\"" : " value=\"".@$values[$key]."\"").">";
					$halt = true;
				break;

				case "html":
					$body.=$data[$key][1];
					$halt = true;
				break;

				case "assign":
					$keySub 			  = $data[$key][0];
					$value 				  = $data[$key][1];
					$assignArray[$keySub] = $value;
					$halt = true;
				break;

				default:
					$template = new template(api::setTemplate("api/universalForm.item.html"));
					$halt = true;
				break;
			}

			if ($halt) continue;

			$template->assign("text", 	   (@$data[$key][6] ? "<strong>".$data[$key][1]."</strong>" : $data[$key][1]));
			$template->assign("inputForm", $inputForm);
			$body .= $template->get();
		}

		$template = new template(api::setTemplate("api/universalForm.body.html"));

		foreach ($assignArray as $key=>$value) {
			$template->assign($key, $value);
		}

		$template->assign("title", $this->formTitle);
		$template->assign("tableWidth",  $this->tableWidth);
		$template->assign("tableClass",  $this->tableClass);
		$template->assign("error", $error);
		$template->assign("body",  $body);



		return $template->get();
	}

	function checkUniversalFormData($data) {
		global $_POST;

		$postArray = api::slashData($_POST);
		$values    = array();

		// assign post
		foreach ($data as $key=>$value) {
   			@$paramName = $data[$key][0];
   			$this->returnForm[$paramName] = $values[$key] = trim(@$postArray[$paramName]);

		}

		$halt = false;

		// check data
		foreach ($data as $key=>$value) {
			if ($halt === false && @$data[$key][6] === true && empty($values[$key])) {
				$halt = $this->lang['emptyEntry']." &laquo;".$data[$key][1]."&raquo;";
				}

			if ($halt === false && $data[$key][2] == "email" && !preg_match("/^[0-9a-z\.\-\_]{1,66}@[a-z0-9\.\-\.]{2,66}\.[a-z]{2,10}$/i", $values[$key])) {
				$halt = $this->lang['incorrectEntry']." &laquo;".$data[$key][1]."&raquo;";;
			}
		}

		if ($halt !== false) {
			return api::genUniversalForm($data, $values, $halt);
		}
		return false;
	}


	function universalSetValues($data, $valuesToSet) {
		$values    = array();

		foreach ($data as $key=>$value) {
   			$paramName = $data[$key][0];
   			$values[$key] = trim(@$valuesToSet[$paramName]);
		}

		return $values;
	}

	public function quoteReplace($array, $keysArray) {
		foreach ($keysArray as $key) {
			if (isset($array[$key]) && !is_array($array[$key]) && !is_object($array[$key])) {
				$array[$key] = str_replace("\"", "&quot;", $array[$key]);
				}
			}
		return $array;
		}

	public function getStat($tableName) {
		global $sql;
		$sql->query("SELECT COUNT(*) FROM `#__#".$tableName."`", true);
		return $sql->result[0];
	}

	public function move($getArray, $sqlLink, $table, $ownerOrGroupColumn = "") {
		$sql = $sqlLink;

		$position  	= (int)@$getArray['currentPosition'];
		$moveType 	= @$getArray['moveType'];
		$id 	= @$getArray['id'];

		$ref = getenv("HTTP_REFERER");

		if (empty($ref) || $position < 1) {
			exit($ref);
			page500();
		}

		switch ($moveType) {
			case "start":

				$sql->query("(SELECT `id` FROM `#__#".$table."` WHERE `position` = '".$position."') UNION (SELECT MIN(`position`) FROM `#__#".$table."` GROUP BY `position` LIMIT 0,1)", true);
				$currentId = (int)$sql->result[0];
				if ((int)($sql->num_rows()-1) != 1) {
					page500();
					}

				$sql->next_row();

				$minPosition = $sql->result[0];

				$sql->query("UPDATE `#__#".$table."` SET `position` = `position` + 1 WHERE `position` < '".$position."'");
				$sql->query("UPDATE `#__#".$table."` SET `position` = '".$minPosition."' WHERE `id` = '".$currentId."'");
				
			break;

			case "up":
				$sql->query("SELECT `id`".(!empty($ownerOrGroupColumn) ? ", `$ownerOrGroupColumn`" : "")." FROM `#__#".$table."` WHERE `position` = '".$position."'", true);
				$currentId = (int)$sql->result[0];
				$ownerId = @$sql->result[1];
				if ((int)$sql->num_rows() != 1) {
					page500();
				}

				$sql->query("SELECT `id`, `position` FROM `#__#".$table."` WHERE `position` < '".$position."'".(!empty($ownerOrGroupColumn) ? " && `$ownerOrGroupColumn` = '".$ownerId."'" : "")." ORDER BY `position` DESC LIMIT 0,1", true);
				$replaceId = (int)$sql->result[0];
				$newPosition = $sql->result[1];
				if ((int)($sql->num_rows()) != 1) {
					break;
				}

                $sql->query("UPDATE `#__#".$table."` SET `position` = '".$newPosition."' WHERE `id` = '".$currentId."'");
				$sql->query("UPDATE `#__#".$table."` SET `position` = '".$position."' WHERE `id` = '".$replaceId."'");
			break;


			case "down":
				$sql->query("SELECT `id`".(!empty($ownerOrGroupColumn) ? ", `$ownerOrGroupColumn`" : "")." FROM `#__#".$table."` WHERE `position` = '".$position."'", true);
				$currentId = (int)$sql->result[0];
				$ownerId = @$sql->result[1];
				if ((int)$sql->num_rows() != 1) {
					page500();
				}

				$sql->query("SELECT `id`, `position` FROM `#__#".$table."` WHERE `position` > '".$position."'".(!empty($ownerOrGroupColumn) ? " && `$ownerOrGroupColumn` = '".$ownerId."'" : "")." ORDER BY `position` ASC LIMIT 0,1", true);
				$replaceId = (int)$sql->result[0];
				$newPosition = $sql->result[1];
				if ((int)($sql->num_rows()) != 1) {
					break;
				}

                $sql->query("UPDATE `#__#".$table."` SET `position` = '".$newPosition."' WHERE `id` = '".$currentId."'");
				$sql->query("UPDATE `#__#".$table."` SET `position` = '".$position."' WHERE `id` = '".$replaceId."'");

			break;

			case "end":
				$sql->query("(SELECT `id` FROM `#__#".$table."` WHERE `position` = '".$position."') UNION (SELECT `position` FROM `#__#".$table."` ORDER BY `position`  DESC LIMIT 0,1)", true);
				$currentId = (int)$sql->result[0];

				if ((int)($sql->num_rows()-1) != 1) {
					page500();
					}

				$sql->next_row();

				$maxPosition = $sql->result[0];

				$sql->query("UPDATE `#__#".$table."` SET `position` = `position` - 1 WHERE `position` > '".$position."'");
				$sql->query("UPDATE `#__#".$table."` SET `position` = '".$maxPosition."' WHERE `id` = '".$currentId."'");
			break;

		}
	go($ref);
	exit();
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
		return api::genPageListEval($array, ($start / $perPage) + 1, $class);
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

	public function getEmailAltBody($altText) {
		return strip_tags(
									str_replace(
												array("<p>", "<br>", "<br/>", "<br\>", "<br />", "<br \>"),
												"\n",
												str_replace(
															array("\n", "\r"),
															"",
															$altText
															)
												)
									);
	}

	public function sendNotification($template, $assignArray = array(), $to = null) {
		$mail = clone $this->mail;
		$sm = clone $this->smarty;

		$mail->ClearAllRecipients();
		$mail->ClearAttachments();
		$mail->ClearCustomHeaders();
		$mail->ClearReplyTos();

		$mail->isHTML(false);
		if (sizeof($assignArray) > 0) {
			$sm->assign($assignArray);
		}

		$mail->Body = $sm->fetch(api::setTemplate("modules/".$this->mDir."/".$template));

		$mail->Subject = "Notification from ".getenv("HTTP_HOST"); // тема
		$mail->AddReplyTo("No reply", "no.reply@".getenv("HTTP_HOST")); // ответить кому
		$mail->From = "no.reply@".getenv("HTTP_HOST"); // от кого
		$mail->FromName = "Notification system"; // от кого - текст 
		if (isset($to)) {
			$mail->AddAddress($to, ""); // Отправить кому
		} else {
			$mail->AddAddress($this->api['config']['mail']['supportEmail'], "Site manager"); // Отправить кому
		}
		return $mail->Send();
	}

	public function addGetsArrayToLink($link = "", $additionalArray = array(), $skipArray = array()) {
		global $_GET;
		$return = $link."?";
		$fArray = $_GET+$additionalArray;
		
		foreach ($fArray as $key=>$value) {
			if (array_search($key, $skipArray) !== false || is_array($value) || empty($value)) {
				continue;
			}
		
			$return .= "&".$key."=".urlencode($value);
		}
		return $return."&";
	}
	
	public static function arrayKeysSL($array) {
		if (!is_array($array)) {
			return $array;
		}

		$newArray = array();
		
		foreach ($array as $key=>$value) {
			if (is_array($array[$key])) {
				$array[$key] = api::arrayKeysSL($array[$key]);
			} else {
				$slKey = strtolower($key);
				$newArray[$slKey] = $value;
			}
		}
		return $newArray;
	}

	/**
	 * Функция возвращает 3-x мерный массив всех языков и модулей, доступных в системе
	 * 			[lang_1]
	 * 				[module_1]
	 * 					[title]
	 * 					[dir]
	 * 				[module_2]
	 * 					[title]
	 * 					[dir]
	 * 				[module_n]
	 * 					[title]
	 * 					[dir]
	 *
	 *			[lang_n]
	 * 				[module_1]
	 * 					...
	 * 				[module_2]
	 * 					...
	 * 				[module_n]
	 * 					...
	 *
	 * @param string $curLang Текуций язык;
	 * @param string $defaultLang Язык по умолчанию
	 * @return array
	 */
	
	function fetchAllModulesAndLangs($curLang, $defaultLang = "ru") {
		
		// Инициализируем переменные
		$langArray		= array();
		$modulesArray	= array();
		$returnArray	= array();
		
		// Получаем список всех языков в системе
		$dirHandle = opendir("templates/");
		while (($fName = readdir($dirHandle)) !== false) {
			if (preg_match("/^[0-9a-z\_]+$/i", $fName, $match)) {
				$langArray[] = $match[0];
			}
		}
		
		// Получаем список модулей системы
		// Получаем список всех языков в системе
		$dirHandle = opendir("modules/");
		while (($fName = readdir($dirHandle)) !== false) {
			if (preg_match("/^[0-9a-z\_]+$/i", $fName, $match)) {
				if (file_exists("modules/".$match[0]."/__info.php") && is_readable("modules/".$match[0]."/__info.php")) {
					include("modules/".$match[0]."/__info.php");
					$modulesArray[] = array	(
										"title" => (isset($mname[$curLang]) ? $mname[$curLang] : $mname[$defaultLang]),
										"dir" => $match[0],
										);
				}
			}
		}
		
		// Выполняем объединение двух массивов
		foreach ($langArray as $key => $value) {
			$returnArray[$value] = $modulesArray;
		}
		
		// Возвращяем результат работы функции
		return $returnArray;
	}

	
	public function setReturnPath() {
		GLOBAL $_SERVER, $_SESSION;
		$requestUri = @$_SERVER['REQUEST_URI'];
		$this->returnPath = $requestUri;
		$_SESSION['returnPath'] = $requestUri;
		$_SESSION[(string)$this->mDir]['returnPath'] = (string)$requestUri;
		return true;

	}

}
?>