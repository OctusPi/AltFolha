<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Emails;
use Octus\App\Utils\Session;
use Octus\App\Utils\Security;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;

class Recover extends Page
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
        $params = [
            'action' => Route::route(['action'=>'send'])
        ];
        return $this->getPage('Recuperar Senha', 'pages/recover', $params, false, false);
    }

    /**
     * method main proccess form requests
     *
     * @return string|null
     */
    public function proccess():?string
    {
        if(Forms::validForm('token', ['email'])){
            
            $facDAO = (new FactoryDao())->daoUsuario();
            $objDAO = $facDAO->readData(Forms::getPost(['email']));

            //not found user by e-mail
            if($objDAO == null){
                return Alerts::notify(Alerts::STATUS_NFOUND, 'E-mail não associado a nenhum usuário...');
            }

            //if found user, set new temp password and send email
            $tmpPass = Security::randonPass();
            $facDAO -> getEntity()->setAttr('pid', md5($tmpPass));
            $facDAO -> getEntity()->setAttr('passchange', 1);
            $wrtDAO  = $facDAO->writeData();

            if($wrtDAO['status']){

                $smail = Emails::send(Emails::RSCPASS, $facDAO->getEntity(), ['tmppass' => $tmpPass]);
                return $smail
                       ? Alerts::notify(Alerts::STATUS_OK, 'Um e-mail foi enviado com instruções para acesso!')
                       : Alerts::notify(Alerts::STATUS_WARNING, 'Estamos tendo problemas para enviar uma senha temporária por email');

            }else{
                return Alerts::notify($wrtDAO['code'], 'Não foi possível gerar uma senha temporária');
            }

        }else{
            return Alerts::notify(Alerts::STATUS_WARNING, 'Formuário inválido...');
        }
    }
}