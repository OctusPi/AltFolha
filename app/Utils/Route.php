<?php
namespace Octus\App\Utils;

use Octus\App\Controller\Pages\Page;
use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Utils;
use Octus\App\Utils\Session;
use Octus\App\Utils\Security;
use Octus\App\Controller\Pages\NotFound;


class Route
{
    const NAMESPACE = 'Octus\\App\\Controller\\Pages\\';
    const DEFAULTPG = 'login';

    private Session $session;
    private ?EntityUsuario $usuario;

    /**
     * Constructor class initialize declared vars of session and user
     *
     * @param Session $session
     * @param EntityUsuario|null $usuario
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->usuario = $this->session->getUser();
    }

    /**
     * Method map url and return values to gets
     *
     * @return array
     */
    public static function gets():array
    {
        
        //keys params send through url
        $keys   = ['app', 'action', 'key', 'type', 'code', 'order', 'limit'];
        $values = [];

        foreach ($keys as $k) {
            $param = match($k){
                'app'   => isset($_GET[$k]) ? Security::sanitize($_GET[$k]) : self::DEFAULTPG,
                default => isset($_GET[$k]) ? Security::sanitize($_GET[$k]) : null
            };
            $values[$k] = $param;
        }

        return $values;
    }

    /**
     * Method return string url route with param gets
     *
     * @param array $mod
     * @return string
     */
    public static function route(array $mod):string
    {
        $route = [];

        foreach (self::gets() as $key => $value) {
            $value = Utils::at($key, $mod) != null ? $mod[$key] : $value;
            if($value != null ){
                $route[$key] = $value;
            }
            
        }

        return Security::sanitize('?'.implode('&', array_map(function($key, $value){
            return $key.'='.$value;
        }, array_keys($route), array_values($route))));
    }

    /**
     * Methos create destiny with param app in url
     *
     * @return Page
     */
    private function destiny():Page
    {
        $app  = self::gets()['app'] != null ? self::gets()['app'] : self::DEFAULTPG;
        $page = $app != null ? self::NAMESPACE.ucfirst($app) : null;
        
        if($page != null && class_exists($page)){
            return new $page($this->session, $this->usuario);
        }else{
            return new NotFound($this->session, $this->usuario);
        }
    }

    /**
     * Method identify destiny and execut action required
     *
     * @return string|null
     */
    public function go():?string
    {
        $page    = $this->destiny();
        $action  = self::gets()['action'];
        
        $method  = match($action){
            'send'   => 'proccess',     //execute form proccess (insert, update, delete)
            'find'   => 'search',       //search form proccess
            'json'   => 'datajson',     //rescue json database
            'view'   => 'datahtml',     //render html page data
            'dataslc'=> 'dataselect',    //render select input form,
            'export' => 'report',
            'upload' => 'upfile',       //upload file async
            'unload' => 'unfile',       //remove file async
            'logoff' => 'logoff',       //destroy active session
            default  => 'viewpage'      //render request page html
        };

        return method_exists($page, $method) 
        ? $page->$method()
        : (new NotFound($this->session, $this->usuario))->callBack();
    }
}