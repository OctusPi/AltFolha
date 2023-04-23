<?php
namespace Octus\App\Utils;

use Octus\App\Utils\Logs;
use Octus\App\Utils\Utils;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Data\FactoryDao;

class Session
{
    //time activite session
    const SISNAME = 'ALTFOLHA_APP_';
    const TIME = 3600;
    

    private string $sessionName;
    private string $sessionUnq;
    private string $sessionTime;
    private string $sessionUid;
    private string $sessionPid;
    
    /**
     * Method constructor start and crypto name id os sessions system
     */
    public function __construct()
    {
        $this->sessionName  = md5(self::SISNAME.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
        $this->sessionUnq   = md5(self::SISNAME.'IDUNQ');
        $this->sessionTime  = md5(self::SISNAME.'TIME');
        $this->sessionUid   = md5(self::SISNAME.'USER');
        $this->sessionPid   = md5(self::SISNAME.'PASS');

        $this->initialize();
    }

    /**
     * Method checks if the session exists, if not start a new session
     *
     * @return void
     */
    private function initialize():void
    {
        if(!$this->isActive()){
            session_name($this->sessionName);
            session_cache_limiter('no-cache');
        }

        @session_start();
    }

    /**
     * Methos checks is session PHP was started 
     *
     * @return bool
     */
    private function isActive():bool
    {
        return (session_status() == PHP_SESSION_ACTIVE);
    }

    /**
     * Method checks if sessions system properly activated
     *
     * @return bool
     */
    private function isCreate():bool
    {
        $sessions = [$this->sessionUnq, $this->sessionUid, $this->sessionPid, $this->sessionTime];

        foreach ($sessions as $s) {
            if(!isset($_SESSION[$s]))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Method chechs if user start session with correct credentials
     *
     * @param EntityUsuario $usuario
     * @return bool
     */
    private function isAuth(?EntityUsuario $usuario):bool
    {
       return (
                (Utils::at($this->sessionUnq, $_SESSION) == $this->sessionName) &&
                (Utils::at($this->sessionUid, $_SESSION) == $usuario->getAttr('uid')) &&
                (Utils::at($this->sessionPid, $_SESSION) == $usuario->getAttr('pid'))
            );
    }

    /**
     * Method check time activity session and renew if time greater than zero
     *
     * @return bool
     */
    private function inTime():bool
    {
        $sTime = Utils::at($this->sessionTime, $_SESSION) != null ? $_SESSION[$this->sessionTime] : 0;

        if($this->isCreate() && $sTime > time()){
            $_SESSION[$this->sessionTime] = time() + self::TIME;
            return true;
        }else{
            return false;
        }
    }

    public function isAllowed(?EntityUsuario $usuario):bool
    {
        return (
            ($usuario != null)  &&
            ($this->isActive()) &&
            ($this->isCreate()) &&
            ($this->isAuth($usuario)) &&
            ($this->inTime())
        );
    }

    /**
     * Method create a new session to user login in system
     *
     * @param EntityUsuario $usuario
     * @return bool
     */
    public function create(EntityUsuario $usuario):bool
    {

        if($this->isActive()){
            $_SESSION[$this->sessionUnq]  = $this->sessionName;
            $_SESSION[$this->sessionUid]  = $usuario->getAttr('uid');
            $_SESSION[$this->sessionPid]  = $usuario->getAttr('pid');
            $_SESSION[$this->sessionTime] = time() + self::TIME;

            return true;
        }

        return false;
    }

    /**
     * Method destroy sessions and logoff users
     *
     * @return void
     */
    public function destroy():void 
    {
        try{
            session_destroy();
            unset($_SESSION);
        }catch(\Exception $e){
            Logs::writeLog('Erro: Falha ao finalizar sessão - '.$e->getMessage(), $this->getUser());
        }
    }

    /**
     * Method return user session active
     *
     * @return EntityUsuario|null
     */
    public function getUser():?EntityUsuario
    {
        $params = [
            'uid' => Utils::at($this->sessionUid, $_SESSION),
            'pid' => Utils::at($this->sessionPid, $_SESSION)
        ];

        $dao = (new FactoryDao())->daoUsuario();
        return $dao->readData($params);
    }

}