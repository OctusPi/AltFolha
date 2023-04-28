<?php
namespace Octus\App\Controller\Pages;

use Exception;
use Octus\App\Utils\Dates;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Utils;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Session;
use Octus\App\Utils\Security;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;


class Login extends Page
{
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

        //case attempt use brute force login
        if(Security::isBrute()){
            Security::redirect('index.php?app=jail');
            exit;
        }

        $params = [
            'action' => Route::route(['action'=>'send'])
        ];
        return $this->getPage('Login', 'pages/login', $params, false, false);
    }

    /**
     * method main proccess form requests
     *
     * @return string|null
     */
    public function proccess():string
    {
        //case attempt use brute force login
        if(Security::isBrute()){
            Security::redirect('index.php?app=jail');
            exit;
        }

        //process form login
        if(Forms::validForm('token', array_keys($_POST))){
            
            //criptex data forn to md5
            $params = [
                'uid' => md5(Utils::at('uid', Forms::getPost(['uid']))),
                'pid' => md5(Utils::at('pid', Forms::getPost(['pid'])))
            ];

            $facDAO  = (new FactoryDao())->daoUsuario();
            $objDAO  =  $facDAO->readData($params);

            if($objDAO != null){
                //update datetim login
                $facDAO -> getEntity()->setAttr('lastlogin', $objDAO->getAttr('nowlogin'));
                $facDAO -> getEntity()->setAttr('nowlogin', Dates::fmttDateTimeDB(Dates::getDateTimeNow()));
                $facDAO -> writeData();
                
                //redirect login by profile
                try{
                    $this->session->create($objDAO);
                    $location = match($objDAO->getAttr('perfil')){
                        EntityUsuario::PRF_ADMIN => '?app=admin',
                        default                  => '?app=folhas'
                    };
                    header("Location:$location");
                    return Alerts::notify(Alerts::STATUS_OK, 'Redirecionando...', null, $facDAO -> getEntity());
                }catch(Exception $e){
                    return Alerts::notify(Alerts::STATUS_ERROR, 'Falha ao processar login...'.$e->getMessage(), null, $facDAO -> getEntity());
                }

            }else{
                Security::countAttempts();
                return Alerts::notify(Alerts::STATUS_WARNING, 'Dados de acesso inválidos. '.Security::viewAttempts());
            }
            
        }else{
            return Alerts::notify(Alerts::STATUS_WARNING, 'Formulario inválido, atualize a página e tente novamente...');
        }
    }

    /**
     * Method destroy session access and redirecto do default page
     *
     * @return void
     */
    public function logoff():void
    {
        $this->usuario = null;
        $this->session->destroy();
        Security::redirect();
    }
}