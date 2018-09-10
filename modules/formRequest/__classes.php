<?php
if (!defined("API")) {
    exit("Main include fail");
}

class Sender
{
    public $data;
    public $lang = array();
    public $to   = "pr@in-site.ru";
    private $fromEmail = "admin@in-site.ru";
    private $from   = "no-reaply@in-site.ru";
    private $subject   = "Сообщение с сайта";
    private $mDir = "formRequest";

    public function __construct()
    {
        $cfgValue = api::getConfig("modules", $this->mDir, "sendEmailTo");
        $this->to = (!empty($cfgValue)) ? $this->to = $cfgValue : $this->to;
    }

    /** отправляем письмо
     *
     * @param $messageBody
     */
    protected function sendMail($messageBody)
    {
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Port = 25;
        $mail->Host = 'smtp.in-site.ru';
        $mail->Username = 'mail@alicedom.ru';
        $mail->Password = '5GcUzoOU';
        $mail->From = $this->from;
        $mail->SMTPSecure = 'tls';
//        $mail->SMTPDebug  =  2;

        $mail->FromName = $this->fromEmail;
        $mail->AddAddress($this->to);
        $mail->Subject = $this->subject;
        $mail->Body = $messageBody;
        $mail->IsHTML(true);    // возвращаем HTML письмо
        $mail->CharSet = "UTF-8";
        $mail->Send();
    }

    /** возврат данных json
     *
     * @param $data
     * @return string
     */
    protected function responseEncodeData($data)
    {
        return $this->data['content'] = json_encode($data);
    }

}