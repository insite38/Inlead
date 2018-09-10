<?php

if (!defined("API")) {
    exit("Main include fail");
}

$page = new page();
$page->lang = $admLng;

switch ($rFile) {
    case "index.php":
        $API['content'] = $page->listPages();
        break;

    case "add.php":
        if (!isset($_POST['go']) || $_POST['go'] !== "go"){
            $page->showAddPageForm();
        } else{
            $page->addPageDataToDatabase();
        }
        break;

    case "edit.php":
        if (!isset($_POST['go']) || $_POST['go'] !== "go")
            $page->showEditPageForm(@intval($_GET['id'])); else
            $page->editPageDataToDatabase();
        break;

    case "delete.php":
        $id = @intval($_GET['id']);
        $sql->query("SELECT COUNT(*) FROM `pages` WHERE `id` = '" . $id . "'");
        $sql->next_row();

        if ((int) $sql->field(0) !== 1) {
            page404();
        }

        $sql->query("DELETE FROM `pages` WHERE `id`='" . $id . "'");
//        message($admLng['delOk'], "", "/admin/page/index.php");
        header( "Location: http://" . $_SERVER['HTTP_HOST'] . "/admin/page/index.php");
        break;

    case "move.php":
        $page->move();
        break;

    case "backupIt.php":
        $page->movePageToArh();
        break;

    case "listBackups.php":
        $page->listBackups();
        break;

    case "deleteArchPage.php":
        $page->deleteArchPage();
        break;

    case "deletePhoto.php":
        $page->deleteOnlyPhoto();
        break;


    case "prices.php":
        $page->prices();
        break;
    case "priceedit.php":
        $page->priceedit('edit');
        break;
    case "addPrice.php":
        $page->priceedit('add');
        break;

    default:
        page404();
        break;
}
$API['pageTitle'] = $page->lang['pages'] . ' / ' . $page->page_title;