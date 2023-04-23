<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Utils;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Emails;
use Octus\App\Utils\Session;
use Octus\App\Utils\Security;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;


class Passchange extends Page
{
    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario);
        
        //checks request change temp passwd
        if(Utils::attr('passchange', $this->usuario) != 1){
            Security::redirect('?app=home');
        }
    }

    public function viewpage():string
    {
        $params = [
            'action'    => Route::route(['action'=>'send']),
            'user_name' => Utils::attr('nome', $this->usuario),
            'user_mail' => Utils::attr('email', $this->usuario)
        ];

        return $this->getPage('Mudar Senha', 'pages/changepass', $params, false, false);
    }

    public function proccess():string
    {
        if(Forms::validForm('token', array_keys($_POST))){
            
            //rescue and sanitize post key|values
            $posts  = Forms::getPost();
            $ispass = Security::isPassValid(Utils::at('newpass', $posts), Utils::at('reppass', $posts), Utils::at('oldpass', $posts));

            if($ispass['status']){

                $this->usuario->setAttr('pid', md5(Utils::at('newpass', $posts)));
                $this->usuario->setAttr('passchange', 0);
                $facDAO = (new FactoryDao())->daoUsuario($this->usuario);
                $wrtDAO = $facDAO->writeData();

                if($wrtDAO['status']){
                    Emails::send(Emails::CHGPASS, $this->usuario, $this->company);
                    Security::redirect();
                    return Alerts::notify($wrtDAO['code'], 'Senha alterada com sucesso!');
                }else{
                    return Alerts::notify($wrtDAO['code'], 'Não foi possível alterar a senha temporária', null, $this->usuario);
                }

            }else{
                return Alerts::notify(Alerts::STATUS_WARNING, $ispass['message']);
            }

        }else{
            return Alerts::notify(Alerts::STATUS_WARNING, 'Formulario inválido, atualize a página e tente novamente...', null, $this->usuario);
        }
    }
}