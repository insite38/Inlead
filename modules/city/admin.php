<?php

if (!defined("API")) {
	exit("Main include fail");
}

$account = new city();
$account->lang = $admLng;

switch ($rFile) {

    case "index.php":
        $account->adminShowIndex();
    break;
    
    case "del.php":
        if(!empty($_GET['name'])){
            $account->deleteCity($_GET['name']);
        }else{
            page404();
        }
    break;
    
    case "edit.php":
        if(isset($_GET['name'])){
            $account->edit($_GET['name']);
        }else{
            page404();
        }
        
    break;
    
    default:
        page404();
   
}

$API['content'] = $account->data['content'];
$API['pageTitle'] = $account->lang['pageTitle'] . ' / ' . $account->page_title;