<?php
if (!defined("API")) {
    exit("Main include fail");
}
include_once __DIR__ . '/FormRequest.php';
include_once __DIR__ . '/Validation.php';
include_once __DIR__ . '/CheckForm.php';
include_once __DIR__ . '/Registration.php';
include_once __DIR__ . '/Login.php';
include_once __DIR__ . '/RecoveryPassword.php';
include_once __DIR__ . '/FormAccount.php';

$validation = new Validation();
$checkForm = new CheckForm($validation);
$request = new FormRequest($checkForm, $lng);
//$user = new UsersSite();
//$registration = new Registration($checkForm, $lng, $user);
//$login = new Login($checkForm, $lng, $user);
//$recoveryPassword = new RecoveryPassword($checkForm, $lng, $user);
//$formAccount = new FormAccount($checkForm, $lng, $user);

switch ($rFile) {

    case "topSend.php":
        $request->request('topSend');
        @$API['content'] = $request->data['content'];
        break;

    case "downSend.php":
        $request->request('downSend');
        @$API['content'] = $request->data['content'];
        break;

    case "business.php":
        $request->request('business');
        @$API['content'] = $request->data['content'];
        break;

    default:
        page404();
        break;
}

@$API['template'] = 'ru/blank.html';

// Setting up out data (All modelu support)
//$md = api::getConfig("modules", "Request", "md");
//$mk = api::getConfig("modules", "Request", "mk");
//$navigationMainTitle = $lng['navigationMainTitle'];
//@$navigationTitle = $lng['navigationTitle'];
//
//$nClass = new navigation();
//$nClass->setMainPage((!empty($navigationMainTitle) ? $navigationMainTitle : api::getConfig("main", "api", "mainPageInNavigation")), $base . "/index.php");
//if (!@empty($lng['navigationTitle'])) $nClass->add($lng['navigationTitle'], $base . "/Request/index.php");
//
//@$API['navigation'] = $nClass->get();
//@$API['pageTitle'] = $lng['pageTitle'];
//
//
//@$API['md'] = (!empty($mk) ? $mk : $API['md']);
//@$API['mk'] = (!empty($mk) ? $mk : $API['mk']);





