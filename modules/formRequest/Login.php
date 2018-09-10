<?php

class Login extends Sender
{
    private $checkForm;
    private $user;
    private $rulesArray = array();

    public function __construct($checkForm, $lang, $user)
    {
        parent::__construct();
        $this->checkForm = $checkForm;
        $this->lang = $lang;
        $this->user = $user;
    }

    public function login($dataRules)
    {
        //  получаем массив с правилами
        $this->rulesArray = $this->lang[$dataRules];

        //  проверяем данные с формы
        $validationResponse = $this->checkForm->check($this->rulesArray);

        //  если нет ошибки то вход
        if (!$validationResponse['error']){

            //  проверяем есть ли такой user  базе
            $haveUser = $this->user->getUserByEmail($_POST['email']);

            if ($haveUser) {

                //  проверяем пароль
                $passwordOk = $this->user->checkPassword($_POST['password'], $haveUser['password']);

                if ($passwordOk) {

                    //  создаем сессию
                    $this->user->authSession($haveUser['id']);
                    //  переходим в кабинет в fb.js
                    $this->responseEncodeData(array('redirect' => true));
                    return true;
                }

                $validationResponse['error'] = true;
                $validationResponse['errors'] = 'Пароль неправильный';

            } else {

                $validationResponse['error'] = true;
                $validationResponse['errors'] = 'Такой email не зарегестрирован';
            }
        }

        //  иначе возвращаем ошибку
        $this->responseEncodeData($validationResponse);
        return true;
    }
}