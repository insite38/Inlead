<?php

class Registration extends Sender
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

    /**
     * @param $dataRules
     * @return bool
     */
    public function registration($dataRules)
    {
        //  получаем правила валидации из файла __ru
        $this->rulesArray = $this->lang[$dataRules];

        //  проверяем данные с формы
        $validationResponse = $this->checkForm->check($this->rulesArray);

        //  если нет ошибки то регистрация
        if (!$validationResponse['error']){

            //  проверяем есть ли такой email  базе
            $haveUser = $this->user->getUserByEmail($_POST['email']);

            if (!$haveUser) {

                //  добавляем юзера
                $idUser = $this->user->putUserDB($_POST);
                //  создаем сессию
                $this->user->authSession($idUser);
                //  переходим в кабинет в fb.js
                $this->responseEncodeData(array('redirect' => true));
                return true;
            }

            $validationResponse['error'] = true;
            $validationResponse['errors'] = 'Такой email уже зарегестрирован';
        }

        //  возвращаем ошибку заполнения формы
        $this->responseEncodeData($validationResponse);
        return true;
    }
}