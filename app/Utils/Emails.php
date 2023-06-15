<?php
namespace Octus\App\Utils;

use Octus\App\Model\EntityCompany;
use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Logs;
use Octus\App\Utils\View;
use Octus\App\Utils\Utils;
use PHPMailer\PHPMailer\PHPMailer;

class Emails
{
    //default params to send email
    const MAILHOST = 'smtps.uhserver.com';
    const MAILDISP = 'smtpserver@araripe.ce.gov.br';
    

    //types msgs
    const GENERIC = 0;
    const NEWUSER = 1;
    const RSCPASS = 2;
    const CHGPASS = 3;
    
    /**
     * Method return path to html file => type msg
     *
     * @param int $type
     * @return string
     */
    private static function getMsg(int $type):string
    {
        return match($type){
            self::NEWUSER => 'emails/newuser',
            self::RSCPASS => 'emails/rescuepass',
            self::CHGPASS => 'emails/changepass',
            default       => 'email/generic'
        };
    }

    /**
     * Method return array to composite msg 
     *
     * @param EntityUsuario|null $usuario
     * @param array|null $eparams
     * @return array
     */
    private static function getParams(?EntityUsuario $usuario, ?EntityCompany $company, ?array $params = null):array
    {
        return [
            'user_name'   => Utils::attr('nome', $usuario),
            'sys_link'    => Utils::attr('urlbase', $company),
            'sys_name'    => Utils::attr('sistema', $company),
            'user_uid'    => Utils::attr('email', $usuario),
            'user_pid'    => Utils::at('tmppass', $params),
            'sys_company' => Utils::attr('company', $company),
        ];
    }

    /**
     * Method send email by type msg and data info user
     *
     * @param int $type
     * @param EntityUsuario|null $usuario
     * @param array|null $eparams
     * @return bool
     */
    public static function send(int $type, ?EntityUsuario $usuario, ?EntityCompany $company,  ?array $params = null):bool
    {
        //send email

        if($usuario != null)
        {
            //send email
            $to   = Utils::attr('email', $usuario);
            $msg  = View::renderView(self::getMsg($type), self::getParams($usuario, $company, $params));

            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Host = self::MAILHOST;
            $mail->Port = 587;
            $mail->SMTPAuth = true; 

            $mail->Username = self::MAILDISP; 
            $mail->Password = '@ara1Cid10';

            //$mail->SMTPOptions = array('ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ));
            $mail->setFrom(self::MAILDISP,"Prefeitura Araripe");
            $mail->AddAddress($to, 'Usuario');
            $mail->IsHTML(true); 
            $mail->CharSet = 'UTF-8';
            $mail->Subject = Utils::attr('sistema', $company);
            $mail->msgHTML($msg); 

            $send = $mail->send();

            //writelog
            $log  = ($send ? 'SUCCESS: ' : 'ERROR: ').' falha ao enviar email para'.$usuario->getAttr('email'); 
            Logs::writeLog($log, $usuario);

            return $send;
        }else{
            return false;
        }

    }
}