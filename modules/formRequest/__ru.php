<?php
if (!defined("API")) {
    exit("Main include fail");
}

$l = array(
    'navigationMainTitle' => "",
    'navigationTitle' => "Написать нам",
    'pageTitle' => "Написать нам",
    'enterCode' => 'Введите код с картинки',
    'send' => 'Отправить',
    "errorPost" => "Неверно заполнено поле ",
//    "errorLenght" => "Количество символов не должно превышать ",
    "wrongCapcha" => "Неверный код ",
    "empty" => "Не заполнено поле ",
    "subject" => "Сообщение с сайта",

    /*  массивы валидации формы
     *
     *  captcha обязательное поле true|false проверять|не проверять
     *  name(на input) поля - ключ массива с правилами
     *      в каждом массиве список правил
     *      name обязательное поле (возвращается на форму в ответе с ошибкой)
     *      required обязательное поле true|false
     *      type проверка запускается по этому полю в классе CheckForm
     */
    "topSend" => array(

        'captcha' => false,

        'name' => array(
            'name' => 'имя',
            'type' => 'text',
            'max' => 150,
            'min' => 3,
            'required' => true),

        'phoneEmail' => array(
            'name' => 'телефон или mail',
            'max' => 35,
            'min' => 5,
            'required' => true),
    ),

    "business" => array(

        'captcha' => false,

        'business' => array(
            'name' => 'сфера бизнеса',
            'type' => 'text',
            'max' => 150,
            'min' => 3,
            'required' => true),

        'good' => array(
            'name' => 'что продать',
            'type' => 'text',
            'max' => 150,
            'min' => 3,
            'required' => true),

        'region' => array(
            'name' => 'ваш регион',
            'type' => 'text',
            'max' => 150,
            'min' => 3,
            'required' => true),

        'phone' => array(
            'name' => 'телефон',
            'type' => 'phone',
            'max' => 18,
            'min' => 5,
            'required' => true),
    ),

    "downSend" => array(

        'captcha' => false,

        'name' => array(
            'name' => 'имя',
            'type' => 'text',
            'max' => 150,
            'min' => 3,
            'required' => true),

        'email' => array(
            'name' => 'E-mail',
            'type' => 'email',
            'max' => 35,
            'min' => 5,
            'required' => true),

        'phone' => array(
            'name' => 'телефон',
            'type' => 'phone',
            'max' => 18,
            'min' => 5,
            'required' => true),
    ),

    "ok" => "Сообщение успешно отправлено",
    "okDesc" => "Спасибо за регистрацию! Мы обязательно свяжемся с вами",
);

?>

