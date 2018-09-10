<?php

class RecoveryPassword extends Sender
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
    public function recovery($dataRules)
    {
        //  получаем правила валидации из файла __ru
        $this->rulesArray = $this->lang[$dataRules];

        //  проверяем данные с формы
        $validationResponse = $this->checkForm->check($this->rulesArray);

        //  если нет ошибки то регистрация
        if (!$validationResponse['error']){

            //  проверяем есть ли такой email  базе
            $haveUser = $this->user->getUserByEmail($_POST['email']);

            if ($haveUser) {

                //  меняем пароль
                $newPassword = $this->user->changePassword($haveUser['id']);
                //  отправляем пароль на почту пользователя
                $this->to = $haveUser['email'];
                $this->sendMail($this->getMessageBody($newPassword));
                //  возврат
                $this->responseEncodeData(array('ok' => 'Новый пароль вымлан вам на email'));
                return true;
            }

            $validationResponse['error'] = true;
            $validationResponse['errors'] = 'Такой email не зарегестрирован';
        }

        //  возвращаем ошибку заполнения формы
        $this->responseEncodeData($validationResponse);
        return true;
    }

    /** формируем тело письма
     *
     * @param $newPassword
     * @return string
     */
    private function getMessageBody($newPassword)
    {
        return '<p>Ваш новый пароль для входа : </p>' . $newPassword;
    }
}