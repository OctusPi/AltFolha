<?php
namespace Octus\App\Utils;

use Octus\App\Utils\Logs;
use Octus\App\Utils\Utils;
use Octus\App\Utils\Security;

class Forms
{
    private static string $token = '684dd91936e506dcd34c66fa88b32551';
    private static string $ftoken;
    
    /**
     * Method crete a randon token to validade security integrity form and stores in cookie
     *
     * @return void
     */
    public static function setToken():void
    {
        self::$ftoken = md5(uniqid(rand(), true));
        setcookie(
            self::$token, //name
            self::$ftoken, //value
            [
                'expires'  => 0,
                'path'     => '/',
                'secure'   => false,
                'httponly' => false,
                'samesite' => 'Strict',
            ]
        );
    }

    /**
     * Method return value token in cicle life of request to set in input validade form
     *
     * @return string
     */
    public static function getToken():string
    {
        return isset(self::$ftoken) ? self::$ftoken : '';
    }

    /**
     * Method checks origin and reliability of validation token send with form
     *
     * @param string|null $key
     * @return bool
     */
    public static function validToken(?string $token):bool
    {
        return Utils::at(self::$token, $_COOKIE) === $token;
    }

    /**
     * Methos sanitize and retun key|velue array send with $_POST
     *
     * @return array
     */
    public static function getPost(?array $fkeys = null):array
    {
        $post = [];

        if(isset($_POST) && !empty($_POST)){
            foreach ($_POST as $key => $value) {
                if($fkeys == null){
                    $post[Security::sanitize($key)] = Security::sanitize($value);
                }else{
                    if(in_array($key, $fkeys)){
                        $post[Security::sanitize($key)] = Security::sanitize($value);
                    }
                }
            }
        }

        return $post;
    }

    /**
     * Method checks if mandatory inputs was send with post form request
     *
     * @param array $mandatory
     * @return bool
     */
    private static function checkMandatory(array $mandatory = []):bool
    {
        if($mandatory != null){

            $posts  = self::getPost(); 

            foreach ($mandatory as $key) {
                if(Utils::at($key, $posts) == null):
                    return false;
                endif;
            }

            return true;

        }else{
            return true;
        }
    }

    /**
     * Method checks origin and reliability of send form
     *
     * @param string|null $token
     * @param array $mandatory
     * @return bool
     */
    public static function validForm(?string $tkname, array $mandatory = []):bool
    {
        $isValid = (
            (isset($_POST) && !empty($_POST)) && 
            (self::validToken(Utils::at($tkname, self::getPost()))) && 
            (self::checkMandatory($mandatory))
        );

        //Case form invalid register in log
        if(!$isValid){
            if(!empty($_POST)){
                Logs::writeLog('warning: Send invalid form!'.implode('-', array_keys($_POST)));
            }
        }

        return $isValid;
    }
}