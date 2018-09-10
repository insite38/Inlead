<?php

class CheckForm
{
    private $validation;
    private $message = array();

    public function __construct($validation)
    {
        $this->validation = $validation;
    }

    /** проверка формы
     *
     * @param $rulesArray
     * @return array
     */
    public function check($rulesArray)
    {
        $this->message['error'] = false;
        $this->message['redirect'] = false;
        $this->message['ok'] = 'Сообщение успешно отправлено.';

        /*
         * проверка на заполнение не видимого инпута
         * если у нго есть value то проверять дальше нет смысла
         * <input type="hidden" name="pirate">
         */
        if  (!empty($_POST['pirate'])){

            $this->message['error'] = true;
            $this->message['errors'] = 'Вы пират! йо-хо-хо!';
            return $this->message;
        }

        /*
         * запускаем цикл по правилам
         *
         * $nameRules -- name поля
         * $rules  -- его правила
         *
         * break стоит потому как возвращаю одну ошибку, но в массиве
         * для того чтоб вывести все ошибки убираем break каждой ошибке даем
         * свое имя и в файле fb.js раскидываем массив по полям
         */
        foreach ($rulesArray as $nameRules => $rules){

            //  проверяем поле на заполнение по правилу required
            if ($rules['required']){

                if (empty($_POST[$nameRules])){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'Поле ' . $rules['name'] . ' обязательно для заполнения.';
                    break;
                }
            }

            //  проверяем телефон
            if (isset($rules['type']) && $rules['type'] == 'phone'){

                if (!$this->validation->checkPhone($_POST[$nameRules])){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'В поле ' . $rules['name'] . ' используйте только цифры';
                    break;
                }
            }

            //  проверяем mail
            if (isset($rules['type']) && $rules['type'] == 'email'){

                if (!$this->validation->checkEmail($_POST[$nameRules])){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'В поле ' . $rules['name'] . ' введите e-mail адресс.';
                    break;
                }
            }

            //  проверяем на длинну
            if (isset($rules['max'])){

                if(strlen($_POST[$nameRules]) > $rules['max']){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'В поле ' . $rules['name'] . ' максимум ' . $rules['max'] . ' символов.';
                    break;
                }
            }

            //  проверяем на длинну
            if (isset($rules['min'])){

                if(strlen($_POST[$nameRules]) < $rules['min']){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'В поле ' . $rules['name'] . ' минимум ' . $rules['min'] . ' символов.';
                    break;
                }
            }

            /*
            *      проверка пароля
            *      если проверка с подтверждением то имя поля password-confirm
            *      если проверка просто пароля то поле password-confirm не надо
            */
            if (isset($rules['type']) && $rules['type'] == 'password'){

                if (isset($_POST['password_confirm'])){ //  проверка пароля с подтверждением

                    if ($_POST['password'] != $_POST['password_confirm']){

                        $this->message['error'] = true;
                        $this->message['errors'] = 'Пароли не совпадают';
                        break;
                    }

                }

                //  проверка на наличие цыфр в поле
                if (!$this->validation->checkDigits($_POST['password'])){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'Используйте хотя бы одну цифру';
                    break;
                }

                //  проверка на наличие нижнего регистра букв
                if (!$this->validation->checkLowercase($_POST['password'])){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'Используйте буквы в нижнем(прописную) регистре';
                    break;
                }

                //  проверка на наличие верхнего регистра букв
                if (!$this->validation->checkUppercase($_POST['password'])){

                    $this->message['error'] = true;
                    $this->message['errors'] = 'Используйте буквы в верхнем(заглавные) регистре';
                    break;
                }
            }
        }

        /*
         *  если нет ошибок то проверка на капчу и отправляем сообщение
         */
        if (!$this->message['error']){

            //  требуется проверка капчи
            if ($rulesArray['captcha']){

                //  пришла ли капча в массиве пост
                if (isset($_POST['g-recaptcha-response'])) {

                    //  проверяем верна ли капча
                    if ($this->validation->reCaptchaSuccess($_POST['g-recaptcha-response'])) {

                        //  проверка пройдена возврат
                        return $this->message;

                    } else {

                        $this->message['error'] = true;
                        $this->message['errors'] = 'Вы не прошли валидацию reCaptcha';
                    }
                } else {

                    $this->message['error'] = true;
                    $this->message['errors'] = 'В массиве пост не пришла reCaptcha \(0_0)/';
                }
            } else { //  капча не требуется

                //  проверка пройдена возврат
                return $this->message;
            }
        }

        //  проверка НЕ пройдена
        return $this->message;
    }
}