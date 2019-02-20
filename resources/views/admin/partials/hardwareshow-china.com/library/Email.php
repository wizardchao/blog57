<?php
/**
 * 发送邮件类库
 */
require('Phpmailer/PHPMailer.php');

class Email{

    public  static $set;
    private static $host;
    private static $port;
    private static $username;
    private static $receive;
    private static $nick;
    private static $password;

    public function __construct()
    {
        self::$set=new WebSetService();
        $email=self::$set->getWebSettingByCache('email');
        //$email=json_decode($email,true);

        self::$host=$email['email_smtp_sever'];
        self::$port=$email['email_smtp_port'];
        self::$nick=$email['email_nickname'];
        self::$username=$email['email_smtp_accout'];
        self::$password=$email['email_smtp_pwd'];
        self::$receive=$email['email_smtp_receive'];


        //print_r($email);exit;
    }

    /**
     * @param $to
     * @param $title
     * @param $content
     * @return bool
     */
    public  static function send($title, $content,$to='') {
        date_default_timezone_set('PRC');//set time

        if(empty($to)) {
            $to=self::$receive;
        }
        try {
            //Create a new PHPMailer instance
            $mail = new PHPMailer;
            //Tell PHPMailer to use SMTP
            $mail->isSMTP();

            $mail->Debugoutput = 'html';
            //Set the hostname of the mail server
            $mail->Host = self::$host;
            //Set the SMTP port number - likely to be 25, 465 or 587
            $mail->Port = self::$port;
            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //Username to use for SMTP authentication
            $mail->Username = self::$username;
            //Password to use for SMTP authentication
            $mail->Password = self::$password;
            //Set who the message is to be sent from
            $mail->setFrom(self::$username, self::$nick);
            //Set an alternative reply-to address
            //$mail->addReplyTo('replyto@example.com', 'First Last');
            //Set who the message is to be sent to
            $mail->addAddress($to);
            //Set the subject line
            $mail->Subject = $title;
            //Read an HTML message body from an external file, convert referenced images to embedded,
            //convert HTML into a basic plain-text alternative body
            $mail->msgHTML($content);
            //Replace the plain text body with one created manually
            //$mail->AltBody = 'This is a plain-text message body';
            //Attach an image file
            //$mail->addAttachment('images/phpmailer_mini.png');

            //send the message, check for errors
            if (!$mail->send()) {
                return false;
                //echo "Mailer Error: " . $mail->ErrorInfo;
            } else {
                return true;
            }
        }catch(phpmailerException $e) {
            return false;
        }
    }
}