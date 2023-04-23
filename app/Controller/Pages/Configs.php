<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Utils\Html;
use Octus\App\Utils\Files;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Logs;
use Octus\App\Utils\Route;
use Octus\App\Utils\Utils;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Session;
use Octus\App\Model\EntityCompany;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;

class Configs extends Page
{
    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario, true, EntityUsuario::PRF_ADMIN, EntityUsuario::NVL_BIGBOSS);
    }

    /**
     * Render view page html
     *
     * @return string
     */
    public function viewpage():string
    {

        $formvls =$this->company->getPropsAndValues();

        $params = [
            'action'         => Route::route(['action'=>'send']),
            'info_logo'      => Html::imgView(Utils::at('logo', $formvls)),
            'info_sistema'   => Utils::at('sistema', $formvls),
            'info_descricao' => Utils::at('descricao', $formvls),
            'info_company'   => Utils::at('company', $formvls),
            'info_cnpj'      => Utils::at('cnpj', $formvls),
            'info_endereco'  => Utils::at('endereco', $formvls),
            'info_telefone'  => Utils::at('telefone', $formvls),
            'info_email'     => Utils::at('email', $formvls),
            'info_url'       => Utils::at('urlbase', $formvls),
            'info_renew'     => Utils::at('daysrenew', $formvls),
            'info_id'        => Utils::at('id', $formvls) != null ?Utils::at('id', $formvls) : 0
        ];

        return $this->getPage('Configuraçoes', 'pages/config', $params);
    }

    /**
     * Proccess request form insert or up data in page
     *
     * @return string
     */
    public function proccess():string
    {

        //insert and update
        if(Forms::validForm('token', EntityCompany::getObrPropsClass())){

            //execute DAO and return alerts states
           $this->company->feedsEntity(Forms::getPost());

            //upload logo
            if(isset($_FILES) && !empty($_FILES))
            {
                $files =  new Files();
                $files -> up($_FILES['imglogo']);

                if($files -> getstatus()['status'][0]){
                   $this->company->setAttr('logo', $files->getstatus()['file'][0]);
                } 
            }

            $facDAO = (new FactoryDao())->daoCompany($this->company);
            $wrtDAO = $facDAO -> writeData();
            return Alerts::notify(
                $wrtDAO['code'], 
                $wrtDAO['status'] ? 'Infos do sistema atualizadas' : 'Falha ao atualizar infos do sistema', 
               $this->company,
                $this->usuario
            );
        }

        return Alerts::notify(Alerts::STATUS_WARNING, 'Formulario inválido, atualize a página e tente novamente...', $this->usuario);
    }
}