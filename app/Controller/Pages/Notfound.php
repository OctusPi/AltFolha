<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Logs;
use Octus\App\Utils\Session;
use Octus\App\Controller\Pages\Page;

class Notfound extends Page
{
    /**
     * Constructor class call constructor parent abstract class Page
     *
     * @param Session $session
     * @param EntityUsuario|null $usuario
     */
    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario, false);
    }

    /**
     * Method main to renderize page html
     *
     * @return string
     */
    public function viewpage():string
    {
        return $this->getPage('Rota Desconhecida', 'pages/404', [], false, false);
    }

    /**
     * method callback render message in html
     *
     * @return string
     */
    public function callBack():string
    {
        Logs::writeLog('ERROR: Tentativa de acesso a rota desconhecida!', $this->usuario);
        return $this->viewpage();
    }


}