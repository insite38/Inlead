<?php

if (!defined("API")) {
    exit("Main include fail");
}

class page
{
    public $lang = array();
    public $templateToSetView = "";
    public $page_title = '';
    public $city = '';
    private $main = '';
    public $cityAdmin = '';
    private $allArray = array();
    private $getArray = array();
    private $uploadGroupImage = "upload/small/";
    private $uploadPhotosSmallDir = "upload/small/";
    private $uploadPhotosNotSoBigDir = "upload/notSoBig/";
    private $uploadPhotosBigDir = "upload/big/";
    private $groupImageLimits = array(150, 150);
    private $photoImageSmallLimits = array(100, 100);
    private $photoAddedImageSmallLimits = array(98, 82);
    private $photoImageNotSoBigLimits = array(640, 480);
    private $photoImageBigLimits = array(1024, 768);


    function __construct()
    {
        global $_GET, $sql, $admLng, $main;
        $this->getArray = api::slashData($_GET);
        $this->sql = &$sql;
        $this->lang = &$admLng;
        $this->main = $main;
        
        // $this->city = $this->getCityIdByName($_SESSION['cityname']);
        $this->city = $_SESSION['cityname'];
        // $this->cityAdmin = $this->getCityIdByName($_SESSION['citynameadmin']);
        $this->cityAdmin = $_SESSION['citynameadmin'];
        
        $this->getNavigationData();
    }

/* ----------------------  Работа над прайслистами   ------------------------- */
    public function prices()
    {
        global $API;

        $Template = new template();
        $Template->file($this->main->setTemplate("modules/page/admin.tabel.list.html"));

        $this->sql->query('SELECT * FROM `priceTables` ORDER BY `name`');
        $tables = $this->sql->getList();
        $list = '';
        foreach ($tables as $value) {
            $list .= '<li><a href="priceedit.php?uri='.$value['ownerUri'].'">'.$value['name'].'</a></li>';
        }

        $Template->assign('tables', $list);
        $API['content'] = $Template->get();
        $this->page_title = 'Таблицы (прайсы)';
        return true;
    }

    public function priceedit($act = 'add')
    {
        global $API;

        $Template = new template();
        $Template->file($this->main->setTemplate("modules/page/admin.tabel.edit.html"));

        if($act == 'add'){
            $defautSubPage = 0;
        }else{
            // узнаем id проайса
            $this->sql->query('SELECT `id` FROM `priceTables` WHERE `ownerUri` = "'.$this->getArray['uri'].'" LIMIT 1', true);
            $tblId = $this->sql->result['id'];

            // узнаем к какой странице принадлежит данный прайс
            $this->sql->query('SELECT `id` FROM `pages` WHERE `uri` = "'.$this->getArray['uri'].'" LIMIT 1', true);
            $defautSubPage = $this->sql->result['id'];

            // прочитаем все значения прайса по строкам
            $this->sql->query('SELECT * FROM `priceTablesValues` WHERE `tabelId` = '.$tblId.' ORDER BY `id` ASC');
            $values = $this->sql->getList();

            $valuesTemplate = new template();
            $valuesTemplate->file($this->main->setTemplate("modules/page/admin.tabel.edit.html"));
            foreach ($values as $value) {
                $valuesTemplate->assign('param', $list);
                $valuesTemplate->assign('col1', $list);
                $valuesTemplate->assign('col2', $list);
                $valuesTemplate->assign('col3', $list);
            }
        }

        $Template->assign("selectOwnerPage", $this->genSelectOwnerPage($defautSubPage, -1));
        // $Template->assign('tables', $list);
        $API['content'] = $Template->get();
        $this->page_title = 'Таблицы (прайсы)';
        return true;
    }


/* ----------------------  Работа над страницами   ------------------------- */

    function showAddPageForm($assignArray = array(), $defautSubPage = 0, $text = "", $halt = -1)
    {
        global $API, $main, $admLng, $_GET, $_SESSION;
        //$title   = getTitle();

        if (isset($_GET['setOwnerId'])) {
            $defautSubPage = (int)@$_GET['setOwnerId'];
        }

        $subTemplate = new template();
        $subTemplate->file($main->setTemplate("modules/page/admin.add.page.form.html"));

        if (isset($assignArray['id']) && !empty($assignArray['id'])) {
            $subTemplate->assign('action', $admLng['edit']);
        } else {
            $subTemplate->assign('action', $admLng['add']);
        }

        if (!isset($assignArray['error']) || empty($assignArray['error'])) {
            $assignArray['error'] = "";
        }

        foreach ($assignArray as $key => $value) {
            $subTemplate->assign($key, $value);
        }

        $editorForm = new FCKeditor('text');
        $editorForm->Value = $text;
        $editorForm->Height = 450;
        $textForm = $editorForm->CreateHtml();

        $template = new template();

        $subTemplate->assign("selectOwnerPage", $this->genSelectOwnerPage($defautSubPage, -1));
        $subTemplate->assign("fckFormText", $textForm);
        $subTemplate->assign('selectTemplate', genTemplateList(isset($assignArray['valueTemplate']) ? $assignArray['valueTemplate'] : ''));
        $API['content'] = $subTemplate->get();
        $this->page_title = $this->lang['page_edit'];

        return true;
    }

    function addPageDataToDatabase()
    {
        global $sql, $_POST, $admLng, $lang, $_GET;
        $error = $this->checkPagePostData();
        if ($error !== false) {
            return $this->showAddPageForm($this->assignAddPagePost($error), @intval($_GET['id']), strip($_POST['text']));
        }

        $postArray = slashArray($_POST);

        if ($_FILES['photo']['name'] != "") {

            $file = $_FILES['photo']['tmp_name'];
            $image = new image();
            $newPhotoFileName = $image->resize($file, $this->uploadPhotosSmallDir, $this->photoAddedImageSmallLimits[1], $this->photoAddedImageSmallLimits[0]);
            if ($newPhotoFileName === false) {
                message($image->error);
            }

            $image->dest = $this->uploadPhotosNotSoBigDir . basename($newPhotoFileName);
            $photoResult = $image->resize($file, $this->uploadPhotosNotSoBigDir, $this->photoImageNotSoBigLimits[1], $this->photoImageNotSoBigLimits[0]);
            if ($photoResult === false) {
                message($image->error);
            }

            $image->dest = $this->uploadPhotosBigDir . basename($newPhotoFileName);
            $photoResult = $image->resize($file, $this->uploadPhotosBigDir, $this->photoImageBigLimits[1], $this->photoImageBigLimits[0]);
            if ($photoResult === false) {
                message($image->error);
            }

            $piture = explode("/", $photoResult);
            $image = $piture[2];
        } else
            $image = "";
        // addign

        $sql->query("INSERT INTO #__#pages (`ownerId`,
											`title`,
											`uri`,
											`template`,
											`navigationShow`,
											
											`redirect`,
											`text`,
											`md`,
											
											`lang`,
											`pageTitle`,
											`photo`,
											`city_name`,
                                            `textAdd1`,
                                            `textAdd2`,
                                            `pageH1`
											) values (
														'" . $postArray['ownerId'] . "',
														'" . $postArray['title'] . "',
														'" . $postArray['uri'] . "',
														'" . $postArray['template'] . "',
														'" . $postArray['navigationShow'] . "',
														
														'" . $postArray['redirect'] . "',
														'" . $postArray['text'] . "',
														'" . $postArray['md'] . "',
														
														'" . $postArray['lang'] . "',
														'" . $postArray['pageTitle'] . "',
														'" . $image . "',
														'" . $this->cityAdmin . "',
                                                        '" . $postArray['textAdd1'] . "',
                                                        '" . $postArray['textAdd2'] . "',
                                                        '" . $postArray['pageH1'] . "'
														)");
        
        $this->sql->query("UPDATE `#__#pages` SET `position` = `id` WHERE `position` = '0'");
        $addNewPageLink = '<a href="admin/page/add.php?lang=' . $lang . '">Добавить новую страницу</a>';
        $addSamePageLink = '<a href="admin/page/add.php?lang=' . $lang . (!empty($postArray['ownerId']) ? '&setOwnerId=' . $postArray['ownerId'] : '') . '">Добавить еще страницу в раздел</a>';

        message($admLng['addOk'], $addSamePageLink . '<br /><br />' . $addNewPageLink, "admin/page/index.php");
    }

    function checkPagePostData()
    {
        global $_POST, $_GET, $admLng, $lang;

        $postArray = slashArray($_POST);

        $id = @intval($_GET['id']);
        $ownerId = $postArray['ownerId'];
        $title = $postArray['title'];
        $uri = $postArray['uri'];
        $template = $postArray['template'];
        $navigationShow = $postArray['navigationShow'];
//        $navigationMainTitle = $postArray['navigationMainTitle'];
        $redirect = $postArray['redirect'];
        $text = $postArray['text'];
        $md = $postArray['md'];
//        $mk = $postArray['mk'];
        $pageTitle = $postArray['pageTitle'];
        $lang = $postArray['lang'];

        if (empty($title))
            return $admLng['noTitle'];
        if (empty($uri))
            return $admLng['noUri'];

        if (!preg_match("/^[a-z0-9_]+$/i", $uri))
            return $admLng['invalidUri'];
        if (!preg_match("/^[a-z0-9_\.]*$/i", $template))
            return $admLng['invalidTemplate'];

        if ($navigationShow != "y" && $navigationShow != "n")
            $admLng['invalidNav'];

        if (!file_exists("templates/" . $lang))
            return $admLng['invalidLng'];

        return false;
    }

    function assignAddPagePost($error = "")
    {
        global $_POST, $_GET, $admLng;

        $postArray = stripArray($_POST);

        return array(
            "id" => @intval($_GET['id']),
            "error" => (!empty($error) ? '<h4 class="alert_error">' . $error . '</h4>' : ''),
            "valueTitle" => $postArray['title'],
            "valueUri" => $postArray['uri'],
            "valueTemplate" => $postArray['template'],
            "valueNavigationShow" => $postArray['navigationShow'],
            "valueNavigationMainTitle" => $postArray['navigationMainTitle'],
            "valueRedirect" => $postArray['redirect'],
            "valueMd" => $postArray['md'],
            "valueMk" => $postArray['mk'],
            "valueLang" => $postArray['lang'],
            "valuePageTitle" => $postArray['pageTitle'],
            "textAdd1" => $postArray['textAdd1'],
            "textAdd2" => $postArray['textAdd2']
        );
    }

    private function genSelectOwnerPage($defaultValue = 0, $halt = -1)
    {
        global $admLng, $lang;
        $lang = $_SESSION['post'];
        $id = @intval($_GET['id']);
        $return = "<select name=\"ownerId\">";
        if ($defaultValue == 0)
            $return .= "<option value=\"0\" selected=\"selected\">--- " . su($admLng['no']) . " ---</option>"; else
            $return .= "<option value=\"0\">--- НЕТ ---</option>";

        $treeArray = template::genTree("pages", "id", "ownerId", "title", 0, $halt);
        foreach ($treeArray as $key => $value) {
            if ($key != $id) {
                $return .= "<option value = \"" . $key . "\"" . ($defaultValue == $key ? " selected=\"selected\"" : "") . ">" . str_repeat("- ", ($treeArray[$key]['level'] * 2)) . $treeArray[$key]['value'] . "</option>";
            }
        }
        $return .= "</select>";

        return $return;
    }

    public function showEditPageForm($id)
    {
        global $sql;
        $sql->query("SELECT * FROM #__#pages WHERE `id` = '" . $id . "'");
        if ($sql->num_Rows() == 0) {
            page404();
        }
        $sql->next_row();

        if ($sql->result['photo'] != "" && $sql->result['photo'] != NULL)
            $photopage = "<a href=\"deletePhoto.php?id=" . $id . "&lang=ru\" onclick='return confirm(\"Вы уверены, что хотите удалить фото?\");'><img border=\"0\" src=\"/upload/small/" . $sql->result['photo'] . "\" /></a><br /><br />Нажмите на фото для его удаления";
        else
            $photopage = "Фотография отсутствует";

        $assignArray = stripArray(array(
            "id" => $id,
            "valueTitle" => $sql->result['title'],
            "valueUri" => $sql->result['uri'],
            "valueTemplate" => $sql->result['template'],
            "valueNavigationShow" => $sql->result['navigationShow'],
            "valueNavigationMainTitle" => $sql->result['navigationTitle'],
            "valueRedirect" => $sql->result['redirect'],
            "valueMd" => $sql->result['md'],
            "valueMk" => $sql->result['mk'],
            "valueLang" => $sql->result['lang'],
            "valuePageTitle" => $sql->result['pageTitle'],
            "valuepageH1" => $sql->result['pageH1'],
            "textAdd1" => $sql->result['textAdd1'],
            "textAdd2" => $sql->result['textAdd2'],
            "photopage" => $photopage
        ));

        $this->showAddPageForm($assignArray, $sql->result['ownerId'], $sql->result['text'], $id);

        return true;
    }

    public function editPageDataToDatabase()
    {
        global $sql, $_POST, $_FILES, $admLng, $lang;
        $error = $this->checkPagePostData();
        if ($error !== false) {
            return $this->showAddPageForm($this->assignAddPagePost($error), @intval($_POST['ownerId']), strip($_POST['text']));
        }

        $postArray = slashArray($_POST);
        $id = (int)@$this->getArray['id'];
        $this->sql->query("	SELECT `photo`
									FROM `pages`
									WHERE `id` = '" . $id . "'", true
        );
        if ($_FILES['photo']['name'] == "")
            $image = $this->sql->result['photo'];
        else {
            if (!empty($this->sql->result['photo'])) {
                unlink(@$this->uploadGroupImage . basename($this->sql->result['photo']));
                unlink(@$this->uploadPhotosNotSoBigDir . basename($this->sql->result['photo']));
                unlink(@$this->uploadPhotosBigDir . basename($this->sql->result['photo']));
            }

            $file = $_FILES['photo']['tmp_name'];
            $image = new image();
            $newPhotoFileName = $image->resize($file, $this->uploadPhotosSmallDir, $this->photoAddedImageSmallLimits[1], $this->photoAddedImageSmallLimits[0]);
            if ($newPhotoFileName === false) {
                message($image->error);
            }
            
            $image->dest = $this->uploadPhotosNotSoBigDir . basename($newPhotoFileName);
            $photoResult = $image->resize($file, $this->uploadPhotosNotSoBigDir, $this->photoImageNotSoBigLimits[1], $this->photoImageNotSoBigLimits[0]);
            if ($photoResult === false) {
                message($image->error);
            }

            $image->dest = $this->uploadPhotosBigDir . basename($newPhotoFileName);
            $photoResult = $image->resize($file, $this->uploadPhotosBigDir, $this->photoImageBigLimits[1], $this->photoImageBigLimits[0]);
            if ($photoResult === false) {
                message($image->error);
            }

            $piture = explode("/", $photoResult);
            $image = $piture[2];
        }

        $sql->query("UPDATE #__#pages SET
										`ownerId` = '" . $postArray['ownerId'] . "',
										`title` = '" . $postArray['title'] . "',
										`uri` = '" . $postArray['uri'] . "',
										`template` = '" . $postArray['template'] . "',
										`navigationShow`  = '" . $postArray['navigationShow'] . "',
										
										`redirect` = '" . $postArray['redirect'] . "',
										`text` = '" . $postArray['text'] . "',
										`md` = '" . $postArray['md'] . "',
										
										`lang` = '" . $postArray['lang'] . "',
										`pageTitle` = '" . $postArray['pageTitle'] . "',
										`photo` = '" . $image . "',
                                        `textAdd1` = '" . $postArray['textAdd1'] . "',
                                        `textAdd2` = '" . $postArray['textAdd2'] . "',
                                        `pageH1` = '" . $postArray['pageH1'] . "'
                                        
										WHERE
										`id` = '" . @intval($_GET['id']) . "'
										");

        message($admLng['page'] . " &laquo;" . $postArray['title'] . "&raquo; " . $admLng['editOk'], "", "admin/page/index.php");
    }

    public function listPages()
    {
        $mysql = clone $this->sql;
//        print_r($this->city);
        $query = "SELECT `id`, `title`, `ownerId` FROM `pages` WHERE `city_name`='".$this->cityAdmin."'";

        unset($mysql);
        /*
        $bodyTemplate = new template();
        $itemTemplate = new template();

        $bodyTemplate->file(api::setTemplate("modules/page/admin.list.page.body.html"));
        $itemTemplate->file(api::setTemplate("modules/page/admin.list.page.item.html"));

        $body = "";
        $positionsArray = array();

        $treeArray = template::genTree("pages", "id", "ownerId", "title", 0, -1, "position");
        $this->sql->query("	SELECT
  									`pages`.`id`,
  									`pages`.`position`,
  									COUNT(`pagesArch`.`id`) AS `countArch`,
  									`pages`.`ownerId`

  							FROM
  									`pages`

  							LEFT JOIN
  									`pagesArch` ON `pagesArch`.`ownerId` = `pages`.`id`

  							GROUP BY `pages`.`id`
  							");
         //echo print_r($treeArray, true);
        while ($this->sql->next_row()) {
            $positionsArray[$empty = $this->sql->result['id']] = $this->sql->result['position'];
            $archArray[$empty = $this->sql->result['id']] = $this->sql->result['countArch'];
        }
        $level = 0;
        $close = false;
        foreach ($treeArray as $key => $value) {
            if ($level != $treeArray[$key]['level']) {
                $level = $treeArray[$key]['level'];
                $body .= '<ul>';
                $close = true;
            }
            $itemTemplate->assign("id", $key);
            $itemTemplate->assign("title", strip_tags($treeArray[$key]['value']));
            $itemTemplate->assign("level", $treeArray[$key]['level']);
            $itemTemplate->assign("owner", $treeArray[$key]['owner']);
            $itemTemplate->assign("padding", $treeArray[$key]['level'] * 50 + 5) .
                    $itemTemplate->assign("pageUrl", $this->getPageUrl($key));
            $itemTemplate->assign("position", $positionsArray[$key]);
            $itemTemplate->assign("archCount", ($archArray[$key] > 0 ? $archArray[$key] : $this->lang['no']));
            $itemTemplate->assign("archLink", ($archArray[$key] > 0 ? "<a href=\"javascript:void();\" onclick='showArchMenu(" . $key . "); return false;'>" . $this->lang['archShow'] . "</a>" : ""));


            $body.=$itemTemplate->get();
            if ($close) {
                $body .= '</ul>';
                $close = false;
            }
        }

        if (empty($body)) {
            $template = new template(api::setTemplate("modules/page/admin.list.empty.pages.html"));
            $body = $template->get();
        }

        $bodyTemplate->assign("body", $body);

        $this->page_title = $this->lang['pages_list'];

        return $bodyTemplate->get();    */
        $result = $this->listPagesTree(0);
        $result = $this->listPagesTemplating($result);

        $template = new template(api::setTemplate('modules/page/admin.list.page.body.html'));
        $template->assign('body', $result);
        $result = $template->get();
        unset($template);

        return $result;
    }

    protected function listPagesTree($owner)
    {
        global $API;
        if (!empty($_POST['lang'])) {
            if ($_POST['lang'] == "ru" || $_POST['lang'] == "en" || $_POST['lang'] == "zh") {
                $_SESSION['post'] = $_POST['lang'];
            } else
                $_SESSION['post'] = $API['config']['defaultlang'];
        }
        $query = "SELECT `id`, `title`, `position` FROM `pages` WHERE `ownerId` =" . (int)$owner . " && `lang` = '" . $_SESSION['post'] . "' && `city_name` = '".$this->cityAdmin."' ORDER BY `position`";

        $mysql = clone $this->sql;
        $mysql->query($query);
        $result = array();
        if ($mysql->num_rows() > 0) {
            $rows = $mysql->getList();
            foreach ($rows as $row) {
                $query = "SELECT `id`, `title` FROM `pages` WHERE `ownerId` = " . (int)$row['id'] . " && `lang` = '" . $_SESSION['post'] . "' && `city_name` = '".$this->cityAdmin."' ORDER BY `position`";
                $mysql->query($query);

                if ($mysql->num_rows() > 0) {
                    $result[$row['id']] = array(
                        'title' => $row['title'],
                        'url' => $this->getPageUrl($row['id']),
                        'position' => $row['position'],
                        'has_children' => TRUE,
                        'children' => $this->listPagesTree($row['id']),
                    );
                } else {
                    $result[$row['id']] = array(
                        'title' => $row['title'],
                        'url' => $this->getPageUrl($row['id']),
                        'position' => $row['position'],
                        'has_children' => FALSE,
                        'children' => array(),
                    );
                }
            }

        }

        unset($mysql);

        return $result;
    }

    protected function listPagesTemplating($data = array())
    {
        $result = '<ul>';
        $template = new template(api::setTemplate('modules/page/admin.list.page.item.html'));

        foreach ($data as $key => $item) {
            //$result .= '<li><a href="' . $item['url'] . '">' . @$item['title'] . '</a>';
            $template->assign('id', $key);
            $template->assign('title', strip_tags($item['title']));
            $template->assign('url', $item['url']);
            $template->assign('position', $item['position']);
            $template->assign('class', $item['has_children'] ? ' class="toggle-in"' : '');

            $result .= $template->get();
            if (isset($item['has_children']) && $item['has_children']) {
                $result .= $this->listPagesTemplating($item['children']);
            }

            $result .= '</li>';
        }
        unset($template);
        $result .= '</ul>';

        return $result;
    }

    public function getNavigation($id, $mainPageTitle = "")
    {
        global $sql, $base;

        $halt = false;
        $return = array();

        while (!$halt) {
            $sql->query("SELECT `ownerId`, `title`, `uri`, `navigationShow`, `template` FROM #__#pages WHERE `id` = '" . $id . "'");
            if ($sql->num_Rows() == 0) {
                $halt = true;
            }

            if ($id = 0) {
                $halt = true;
            }

            $sql->next_row();
            $id = $sql->result[0];
            array_unshift($return, array($sql->result[1], $sql->result[2], $sql->result[3], $this->sql->result['template']));
        }

        $navReturn = new navigation();
        $navReturn->setMainPage(api::getConfig("main", "api", "mainPageInNavigation"), $base);
        if (!empty($mainPageTitle)) {
            $navReturn->setMainPage($mainPageTitle, $base);
        }
        $sUri = "/";

        foreach ($return as $key => $empty) {
            if ($return[$key][2] == 'y')
                $navReturn->add($return[$key][0], $base . "/page" . $sUri . $return[$key][1] . ".html");
            if (!empty($return[$key][1]))
                $sUri .= $return[$key][1] . "/";

            if (!empty($return[$key][3])) {
                $this->templateToSetView = $return[$key][3];
            }
        }

        return $navReturn->get();
    }

    public function getNavigationData()
    {
        global $sql;
        $sql->query("SELECT `id`, `ownerId`, `title`, `uri` FROM #__#pages");
        while ($sql->next_row()) {
            $id = $sql->result[0];
            $this->allArray[$id] = array("ownerId" => $sql->result[1], "title" => $sql->result[2], "uri" => $sql->result[3]);
        }
    }

    public function getPageUrl($id)
    {
        $halt = false;
        $uriArray = array();

        while (!$halt) {
            if (!isset($this->allArray[$id])) {
                $halt = true;
                continue;
            }
            $uri = $this->allArray[$id]['uri'];
            $id = $ownerId = $this->allArray[$id]['ownerId'];

            array_unshift($uriArray, $uri);

            if ($ownerId == 0) {
                $halt = true;
            }
        }

        $return = "/page/";
        $count = 1;

        foreach ($uriArray as $key => $value) {
            if ($count == count($uriArray)) {
                $return .= $value . ".html";
            } else {
                $return .= $value . "/";
            }
            $count++;
        }

        return $return;
    }

    public function move()
    {
        api::move($this->getArray, $this->sql, "pages", "ownerId");
    }

    public function movePageToArh()
    {
        $ownerId = (int)@$this->getArray['id'];

        $this->sql->query("	SELECT
									`text`
							FROM
									`#__#pages`
							WHERE
									`id` = '" . $ownerId . "'
									", true);

        if (!$this->sql->result['text']) {
            page404();
        }

        $origionalText = $this->sql->result['text'];

        $this->sql->query("	INSERT INTO `pagesArch` (`ownerId`, `text`, `date`) VALUES ('" . $ownerId . "','" . mysql_escape_string($origionalText) . "',NOW())");

        message($this->lang['pageAddedToArchSuccessfully'], "", "/admin/page/");
    }

    public function getAjaxReturnArchPagesHtml($ownerId)
    {
        $this->sql->query("	SELECT `id`, DATE_FORMAT(`date`, '%d/%m/%Y') as `date` FROM `#__#pagesArch` WHERE `ownerId` = '" . (int)$ownerId . "'");

        $body = "";
        $template = new template(api::setTemplate("modules/page/admin.list.arch.page.item.html"));

        while ($this->sql->next_row()) {
            $template->assign("id", $this->sql->result['id']);
            $template->assign("date", $this->sql->result['date']);
            $body .= $template->get();
        }

        return $body;
    }

    public function deleteArchPage()
    {
        $this->sql->query("
							DELETE FROM `#__#pagesArch` WHERE `id` = '" . @(int)$this->getArray['id'] . "'");

        message($this->lang['archDeletedSuccessfully'], "", "/admin/page/index.php");
    }

    function deleteOnlyPhoto()
    {
        $id = (int)@$this->getArray['id'];
        /* $fName = basename($this->getArray['locate']); */
        
        if (empty($id)) {
            page500();
        }

        $this->sql->query("	SELECT `photo`
									FROM `pages`
									WHERE `id` = '" . $id . "'", true
        );

        if ((int)$this->sql->num_rows() !== 1) {
            page500();
        }

        /* 	if (basename($this->sql->result['photo']) !== $fName) {
          page500();
          } */

        if (!empty($this->sql->result['photo'])) {
            unlink($this->uploadGroupImage . basename($this->sql->result['photo']));
            unlink($this->uploadPhotosNotSoBigDir . basename($this->sql->result['photo']));
            unlink($this->uploadPhotosBigDir . basename($this->sql->result['photo']));
        }

        $this->sql->query("	UPDATE `pages`
									SET `photo` = ''
									WHERE `id` = '" . $id . "'"
        );

        message($this->lang['addedPhotoDeleteOk'], "", "admin/page/index.php");
    }


}