<?php

if (!defined("API")) {
    exit("Main include fail");
}
$page = new page();

if (!preg_match("/^([a-z0-9_]{1,255})\.(?:html|php|htm|shtml)/i", $rFile, $requestUri)) {
    page404();
}

// getting owner id
if ($uric > 1) {
    $ownerUri = $uri[$uric];
    $sql->query("SELECT `id` FROM #__#pages WHERE `uri` = '" . $ownerUri . "' && lang='" . $lang . "'");
    $sql->next_row();

    if ((int)$sql->num_rows() !== 1) {
        page404();
    }
    $ownerId = $sql->result[0];
} else {
    $ownerId = 0;
}

$lang = $_SESSION['lang'];

$sql->query("SELECT `title`, `uri`, `template`, `navigationShow`, `navigationTitle`, `redirect`, `text`, `md`, `mk`, `id`,
`pageTitle`, `pageH1` FROM `#__#pages` WHERE `uri` = '" . $requestUri[1] . "' AND `lang` = '" . $lang . "' AND `city_name`='".$page->city."'");

//print_r($page->city);
//exit();

if ((int)$sql->num_rows() !== 1) {
    page404();
} else {
    $sql->next_row();
}

$id = $sql->result['id'];
// If need redirect;
if (!empty($sql->result[5])) {
    go($sql->result[5]);
}

// set up template
if (!empty($sql->result[2])) {
    $pageTemplate = $sql->result[2];
}



// Setting up all API values;
@$API['title'] = "" . (empty($sql->result[10]) ? $sql->result[0] : $sql->result[10]);
@$API['title'] = "" . (empty($sql->result[11]) ? $API['title'] : $sql->result[11]);
@$API['pageTitle'] = (empty($sql->result[10]) ? $sql->result[0] : $sql->result[10]);

@$API['content'] = $sql->result[6];

@$API['md'] = (!empty($sql->result[7]) ? $sql->result[7] : $sql->result[7]);
@$API['mk'] = (!empty($sql->result[8]) ? $sql->result[8] : $sql->result[8]);
@$API['mk'] = (!empty($sql->result[8]) ? $sql->result[8] : $sql->result[8]);
@$API['navigation'] = $page->getNavigation($sql->result[9], $sql->result[4]);

if (!empty($page->templateToSetView)) {
    $API['template'] = api::setTemplate($page->templateToSetView);
}

if (!empty($pageTemplate)) {
    $API['template'] = api::setTemplate($pageTemplate);
}

if (isset($_GET['arch'])) {
    $sql->query("SELECT `text`,  DATE_FORMAT(`date`, '%d/%m/%Y') as `date` FROM `#__#pagesArch` WHERE `id` = '" . @(int)$_GET['arch'] . "'", true);
    if ($sql->num_rows() !== 1) page404();
    $API['content'] = $sql->result['text'];
    @$API['pageTitle'] = $API['pageTitle'] . " " . $lng['from'] . " " . $sql->result['date'];
}

$sql->query("
	SELECT `id`,DATE_FORMAT(`date`, '%d/%m/%Y') as `date`
	FROM `#__#pagesArch`
	WHERE `ownerId` = '" . $id . "'");

if ($sql->num_rows() > 0) {
    $toAdd = "<div align=\"left\"><form action=\"\" method=\"GET\">" . $lng['archText'] . "<select name=\"arch\">";
    while ($sql->next_row()) {
        $toAdd .= "<option value=\"" . $sql->result['id'] . "\">" . $sql->result['date'] . "</option>";
    }
    $toAdd .= "</select><input type=\"submit\" value=\"OK\"></form></div>";
    $API['content'] = $API['content'] . $toAdd;
}


//print_r($_SERVER['HTTP_ACCEPT_LANGUAGE'][3] );
