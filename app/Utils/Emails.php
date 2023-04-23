<?php
namespace Octus\App\Utils;

use Octus\App\Model\EntityCompany;
use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Logs;
use Octus\App\Utils\View;
use Octus\App\Utils\Utils;

class Emails
{
    //default params to send email
    const DISP     = 'dti@campossales.ce.gov.br';
    const HEADER   = 'MIME-Version: 1.0'.
    "\r\n".
    'Content-type: text/html; charset=UTF-8'.
    "\r\n".
    'From: <'.self::DISP.'>'.
    "\r\n";

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
        $to   = Utils::attr('email', $usuario);
        $msg  = View::renderView(self::getMsg($type), self::getParams($usuario, $company, $params));
        $send = mail($to, Utils::attr('sistema', $company), $msg, self::HEADER);

        //writelog
        $log  = ($send ? 'SUCCESS: ' : 'ERROR: ').' falha ao enviar email para'.$to; 
        Logs::writeLog($log, $usuario);

        return $send;
    }
}