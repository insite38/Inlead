<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/models/UsersSiteModel.php";

class UsersSite
{
    private $mUser;
    private $userSession = array();

    function __construct()
    {
        if (!isset($_SESSION['auth'])) {
            $_SESSION['auth'] = array();
        }

        $this->userSession = &$_SESSION['auth'];
    }

    /** записываем нового юзера в базу
     *
     * @param $post
     * @return string
     */
    public function putUserDB($post)
    {
        $this->mUser = new UsersSiteModel(DB::getInstance());
        $this->mUser->fillable = array();
        $this->mUser->fillable = array(
            'first_name' => $post['first_name'],
            'last_name' => $post['last_name'],
            'email' => $post['email'],
            'phone' => $post['phone'],
            'password' => $this->cryptPassword($post['password']),
        );
        $this->mUser->insert();
        return $this->mUser->getLastId();
    }

    /** обновление данных юзера и сессии
     *
     * @param $post
     */
    public function updateUserDB($post)
    {
        $this->mUser = new UsersSiteModel(DB::getInstance());
        $this->mUser->fillable = array();
        $this->getFillable($post);
        $this->mUser->where('id', $this->userSession['userId'])->update();
        $this->authSession($this->userSession['userId']);
    }

    /** заполняем поля для записи из массива пост
     *
     * @param $post
     */
    private function getFillable($post)
    {
        foreach ($post as $name => $value){

            //  исключаем ненужные поля
            if ($name == 'pirate' || $name == 'old_password' || $name == 'password_confirm') {
                continue;
            }

            if ($name == 'password') {
                $this->mUser->fillable[$name] = $this->cryptPassword($value);
                continue;
            }

            $this->mUser->fillable[$name] = $value;
        }
    }

    /** получаем юзера по id
     *
     * @param $id
     * @return string
     */
    public function getUserDB($id)
    {
        $this->mUser = new UsersSiteModel(DB::getInstance());
        return $this->mUser->getById($id);
    }

    /** проверка наличия в базе email
     *
     * @param $email
     * @return string
     */
    public function getUserByEmail($email)
    {
        $this->mUser = new UsersSiteModel(DB::getInstance());
        return $this->mUser->where('email', $email)->find(' * ');
    }

    /** создаем сессию
     *
     * @param $id
     */
    public function authSession($id)
    {
        $user = $this->getUserDB($id);

        $this->userSession = array(
            'userId' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'index' => $user['index'],
            'region' => $user['region'],
            'city' => $user['city'],
            'street' => $user['street'],
            'house' => $user['house'],
            'housing' => $user['housing'],
            'room' => $user['room'],
            'floor' => $user['floor'],
        );
    }

    /** очищаем сессию
     *
     */
    public function clearSession()
    {
        $this->userSession = false;
    }

    /**
     * @param $password
     * @return string
     */
    private function cryptPassword($password)
    {
        return crypt($password, $this->genRandomString(60));
    }

    /** проверяем пароль
     *
     * @param $password
     * @param $passwordDB
     * @return bool
     */
    public function checkPassword($password, $passwordDB)
    {
        return ($passwordDB == crypt($password, $passwordDB));
    }

    /** смена пароля по id юзера
     *
     * @param $id
     * @return string
     */
    public function changePassword($id)
    {
        $newPassword = $this->genRandomString(8);
        $this->mUser = new UsersSiteModel(DB::getInstance());
        $this->mUser->fillable = array();
        $this->mUser->fillable = array(
            'password' => $this->cryptPassword($newPassword),
        );
        $this->mUser->where('id', $id)->update();
        return $newPassword;
    }

    /** генерируем рандомную строку
     *
     * @param $len
     * @return string
     */
    private function genRandomString($len)
    {
        $string = "";
        $symbol = array(
            'select' => '',
            1 => '0123456789',
            2 => 'qwertyuiopasdfghjklzxcvbnm',
            3 => 'QWERTYUIOPASDFGHJKLZXCVBNM',
            4 => "qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM"
        );

        $counter = 1;
        $counterSymbol = 1;
        while ($counter <= $len) {

            $symbol['select'] = $symbol[$counterSymbol];
            $string .= substr($symbol['select'], mt_rand(0, strlen($symbol['select'])) - 1, 1);
            $counterSymbol = ($counterSymbol == 4) ? $counterSymbol = 1 : $counterSymbol + 1;
            $counter++;
        }

        return $string;
    }

}