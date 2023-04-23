<?php
namespace Octus\App\Utils;

use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Route;
use Octus\App\Utils\Utils;
use Octus\App\Controller\Pages\Page;

class Security
{
    private static string $token = '7589f0ce482e98034354fb5e83fb1c4a';
    private static int $offtime  = 0;
    private static int $attempts = 5;

    /**
     * Method force redirec users access nor granted in system
     * This method is mandatory implemented in all pages
     * @param Page $page
     * @param Session|null $session
     * @param EntityUsuario|null $usuario
     * @param bool $security
     * @return void
     */
    public static function guardian(Page $page, ?Session $session, ?EntityUsuario $usuario = null, bool $security = true):void
    {
        if($security){

            //case attempt use brute force login
            if(self::isBrute()){
                self::redirect(Route::route(['app'=>'jail']));
                exit;
            }

            //case user null or session denied
            if($usuario == null || !$session->isAllowed($usuario)){
                self::redirect();
                exit;
            }

            //case user change password mandatory
            if(Utils::attr('passchange', $usuario) == 1 && Route::gets()['app'] != 'passchange'){
                self::redirect(Route::route(['app'=>'passchange']));
                exit;
            }

            //case access deined or user bloqued
            if(!self::isAuth($page, $usuario)){
                self::redirect(Route::route(['app'=>'deined']));
                exit;
            }
        }
    }

    /**
    * Method checks credential access user and page to grant access if authorized
    *
    * @param Page $page
    * @param EntityUsuario|null $usuario
    * @return bool
    */
    public static function isAuth(Page $page, ?EntityUsuario $usuario):bool
    {
        if($usuario != null && $usuario->getAttr('status') == 1){
            
            $crUser = $usuario->getCredentials();
            $crPage = $page->getCredentials();

            return (
                (($crPage['profile'] == 0 || $crUser['profile'] <= $crPage['profile'])) &&
                (in_array($crPage['level'], $crUser['level']))
            );

        }else{
            return false;
        }
    }

    /**
     * Method clean malicius text of external fonts (manual clean and sanitize string PHP)
     *
     * @param mixed $input
     * @return null|string
     */
    public static function sanitize(mixed $input):?string
    {
        if($input != null){
            
            if(is_array($input)){
                
                $clear = [];
                foreach($input as $item){
                    $clear[] = self::sanitize($item);
                }
                return Utils::toString($clear);

            }else{
                $input = preg_replace("/(from|FROM|script|SCRIPT|select|SELECT|insert|INSERT|delete|DELETE|truncate|TRUNCATE|where|WHERE|drop|DROP|drop table|DROP TABLE|show tables|SHOW TABLES|#|\$|-\$-|\*|--|\\\\)/","",$input);
                return filter_var($input, FILTER_SANITIZE_STRING);
            }
            
        }else{
            return null;
        }
        
    }

    /**
     * Method redirect page url using heders PHP
     *
     * @param string $url
     * @return void
     */
    public static function redirect(string $url = 'index.php'):void
    {
        header("Location:$url");
        exit;
    }

    /**
     * Method generate security password randomic
     *
     * @return string
     */
    public static function randonPass(): string
    {
        $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@!%';
        $pass = '';
        for($i=0; $i<12; $i++):
            $pass .= $char[rand(0, strlen($char)-1)];
        endfor;

        return $pass;
    }

    /**
     * Method apply security policy in change passward user request
     * The password is validate with 8 characters or more and numeric and symbols
     * @param string $newPass
     * @param string $repPass
     * @param string $oldPass
     * @return array
     */
    public static function isPassValid(string $newPass, string $repPass, string $oldPass = ''): array
    {

        //checks if new password equal temp password
        if($newPass == $oldPass){
            return [
                'status' =>false, 
                'message'=>'A nova senha não pode ser igual a senha temporária'
            ];
        }

        if($newPass != $repPass){
            return [
                'status' =>false, 
                'message'=>'Falha ao validar nova senha, senhas não conferem!'
            ];
        }

        return [
            'status' => true, 
            'message'  => 'Nova senha aceita'
        ];
    }

    /**
     * Method count and record in cookie number attempts access login fail in system
     *
     * @return void
     */
    public static function countAttempts():void
    {
        $attempt = isset($_COOKIE[self::$token]) ? $_COOKIE[self::$token] + 1 : 1;
        setcookie(
            self::$token, //name
            $attempt, //value
            [
                'expires'  => self::$offtime,
                'path'     => '/',
                'secure'   => false,
                'httponly' => false,
                'samesite' => 'Strict',
            ]
        );
    }

    /**
     * Show rest attempts logind before bloq
     *
     * @return string|null
     */
    public static function viewAttempts():?string
    {
        return isset($_COOKIE[self::$token])
                ? 'Tentativa '.$_COOKIE[self::$token].' de '.self::$attempts
                : null;
    }

    /**
     * Method ficalize if numb attempts logins fail is not greater than allowed
     *
     * @return bool
     */
    public static function isBrute():bool
    {
        return Utils::at(self::$token, $_COOKIE) > self::$attempts;
    }

    /**
     * checks if user is auth to list data content view
     * @param EntityUsuario|null $user
     * @param int $secretaria
     * @return bool
     */
    public static function isAuthList(?EntityUsuario $user, int $secretaria, int $departamento = 0):bool
    {
        if($user != null){
            $credentials = $user->getCredentials();

            return
                ($credentials['profile'] == EntityUsuario::PRF_ADMIN)

                ||  ($credentials['profile'] == EntityUsuario::PRF_GESTOR 
                    && $secretaria == $credentials['secretaria'])

                ||  ($credentials['profile'] == EntityUsuario::PRF_DEPTO 
                    && $secretaria == $credentials['secretaria'] 
                    && in_array($departamento, $credentials['deptos']));

        }else{
            return false;
        }
    }
}