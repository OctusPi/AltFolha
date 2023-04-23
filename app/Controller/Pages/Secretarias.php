<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Model\EntitySecretaria;
use Octus\App\Utils\Html;
use Octus\App\Utils\Security;
use Octus\App\Utils\View;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Session;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;

class Secretarias extends Page
{
    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario, true, EntityUsuario::PRF_GESTOR, EntityUsuario::NVL_ESTRUTURA);
    }

    /**
     * Render view page html
     *
     * @return string
     */
    public function viewpage():string
    {
        $params = [
            'form_search'   => View::renderView('fragments/forms/search/secretarias'),
            'search_action' => Route::route(['action'=>'view']),
            'action'        => Route::route(['action'=>'send']),
            'data_page'     => json_decode($this->datahtml())->{'html'}
        ];

        return $this->getPage('Gestão de Secretarias', 'pages/secretarias', $params);
    }

    public function datahtml():string
    {
        $facDAO  = (new FactoryDao)->daoSecretaria();
        $getDAO  = $facDAO->readData($this->search(), true, 'secretaria');
        $tabKeys = ['Identificaçao', 'Contato', 'Endereco', ''];
        $tabBody = [];

        //feed body
        if($getDAO != null)
        {
            foreach($getDAO as $dao)
            {
                //convet array to entity
                $ent  = new EntitySecretaria();
                $ent -> feedsEntity($dao);

                if(Security::isAuthList($this->usuario, $ent->getAttr('id')))
                {
                    $tabBody[] = [
                        Html::pbig($ent->getAttr('secretaria'))
                       .Html::psmall($ent->getAttr('cnpj')),
    
                        Html::psmall($ent->getAttr('telefone'))
                       .Html::psmall($ent->getAttr('email')),
    
                        Html::psmall($ent->getAttr('endereco')),
    
                        Html::tabAction($ent->getAttr('id'), 'admin')
                    ];
                }
            }
        }

        return json_encode(
            [
                'html' => Html::genericTable($tabBody, $tabKeys)
            ]
        );
    }

    public function datajson(?array $params = null):string
    {
        $getid  = ['id'=>Route::gets()['key']];
        $params = $params == null ? $getid : $params;
        
        $facDAO = (new FactoryDao())->daoSecretaria();
        $getDAO = $facDAO->readData($params);

        return Alerts::notify(
            $getDAO != null ? 'success' : 'warning',
            $getDAO != null ? 'Dados recuperados para ediçao' : 'Falha ao recuperar dados',
            $getDAO,
            $this->usuario
        );
    }

    /**
     * Proccess request form insert or up data in page
     *
     * @return string
     */
    public function proccess():string
    {

        //insert and update
        if(Forms::validForm('token', EntitySecretaria::getObrPropsClass())){

            //execute DAO and return alerts states
            $facDAO = (new FactoryDao())->daoSecretaria();
            $facDAO->getEntity()->feedsEntity(Forms::getPost());
            $wrtDAO = $facDAO -> writeData();
            
            return Alerts::notify(
                $wrtDAO['code'],
                '',
                $facDAO->getEntity(),
                $this->usuario
            );
            
        }

        //delete iten
        if(Forms::validForm('token_trash', array_keys($_POST))){
           
            $params = Forms::getPost(['id', 'passconfirm']);

            if(md5($params['passconfirm']) == $this->usuario->getAttr('pid')){
                
                $entity =  new EntitySecretaria();
                $entity -> feedsEntity($params);

                $facDAO = (new FactoryDao())->daoSecretaria($entity);
                $excDAO = $facDAO->delData();

                return Alerts::notify(
                    $excDAO['code'],
                    $excDAO['status'],
                    $facDAO->getEntity(),
                    $this->usuario
                );

            }else{
                return Alerts::notify(Alerts::STATUS_WARNING, 'Senha de validaçao incorreta', null, $this->usuario);
            }
        }

        return Alerts::notify(Alerts::STATUS_WARNING, 'Formulario inválido, atualize a página e tente novamente...', null, $this->usuario);
    }

    /**
     * method proccess form search
     *
     * @return array
     */
    private function search():array
    {
        if(Forms::validForm('token_search')){
            return array_filter(Forms::getPost(['secretaria', 'cnpj']));
        }else{
            return [];
        }
    }
}