<?php

class FormAccount extends Sender
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

    public function account($dataRules)
    {
        //  получаем массив с правилами
        $this->rulesArray = $this->lang[$dataRules];

        if (isset($this->rulesArray['old_password'])){

            $user = $this->user->getUserDB($_SESSION['auth']['userId']);
            $correctPassword = $this->user->checkPassword($_POST['old_password'], $user['password']);

            if (!$correctPassword) {

                $this->responseEncodeData(array(
                    'errors' => 'Старый пароль введен не верно',
                    'error' => true));
                return true;
            }
        }

        //  проверяем данные с формы
        $validationResponse = $this->checkForm->check($this->rulesArray);

        //  если нет ошибки то пишем данные в базу
        if (!$validationResponse['error']){

            //  обновляем данные юзера
            $this->user->updateUserDB($_POST);
            $validationResponse['ok'] = 'данные обновлены';
        }

        //  иначе возвращаем ошибку
        $this->responseEncodeData($validationResponse);
        return true;
    }
}