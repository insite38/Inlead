<?php

class FormRequest extends Sender
{
    protected $checkForm;
    private $rulesArray = array();

    public function __construct($checkForm, $lang)
    {
        parent::__construct();
        $this->checkForm = $checkForm;
        $this->lang = $lang;
    }

    /** ответ
     *
     * @param $dataRules
     * @return bool
     */
    public function request($dataRules)
    {
        //  получаем правила валидации с __ru
        $this->rulesArray = $this->lang[$dataRules];
        //  проверяем форму
        $validationResponse = $this->checkForm->check($this->rulesArray);

        //  если нет ошибки то отправляем письмо
        if (!$validationResponse['error']){

            $this->sendMail($this->getMessageBody());
            $this->responseEncodeData($validationResponse);
            return true;
        }

        //  иначе возвращаем ошибку
        $this->responseEncodeData($validationResponse);
        return true;
    }

    /** формируем тело письма
     *
     * @return string
     */
    private function getMessageBody()
    {
        $messageBody = '';

        foreach ($this->rulesArray as $nameRules => $rules){

            if ($nameRules == 'captcha') { continue; }

            $_POST[$nameRules] = (!empty($_POST[$nameRules])) ? $_POST[$nameRules] : ' поле не заполнили';
            $messageBody .= $rules['name'] . ' : ' . $_POST[$nameRules] . '<br>';
        }

        return $messageBody;
    }

}