<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Controller\Pages\Components\DataList;
use Octus\App\Model\EntityDepartamento;
use Octus\App\Model\EntityFuncionario;
use Octus\App\Utils\Html;
use Octus\App\Utils\Security;
use Octus\App\Utils\Utils;
use Octus\App\Utils\View;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Session;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;

class Funcionarios extends Page
{
    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario, true, EntityUsuario::PRF_DEPTO, EntityUsuario::NVL_FUNCIONARIOS);
    }

    /**
     * Render view page html
     *
     * @return string
     */
    public function viewpage():string
    {
        $params = [
            'form_search'         => View::renderView('fragments/forms/search/funcionarios'),
            'search_action'       => Route::route(['action'=>'view']),
            'form_secretarias'    => Html::comboBox(DataList::listSecretarias($this->usuario)),
            'form_departamentos'  => Html::comboBox(DataList::listDepartamentos($this->usuario)),
            'form_vinculos'       => Html::comboBox(EntityFuncionario::vinculoArr()),
            'form_cargashorarias' => Html::comboBox(EntityFuncionario::cargahorariaArr()),
            'action'              => Route::route(['action'=>'send']),
            'data_page'           => json_decode($this->datahtml())->{'html'}
        ];

        return $this->getPage('Gestão de Funcionarios', 'pages/funcionarios', $params);
    }

    public function datahtml():string
    {
        $facDAO  = (new FactoryDao)->daoFuncionario();
        $getDAO  = $facDAO->readData($this->search(), true, 'funcionario');
        $tabKeys = ['Identificaçao', 'Lotação', 'Vinculo', 'Contato', ''];
        $tabBody = [];

        //feed body
        if($getDAO != null)
        {
            $secretarias   = DataList::listSecretarias($this->usuario);
            $departamentos = DataList::listDepartamentos($this->usuario);

            foreach($getDAO as $dao)
            {
                //convet array to entity
                $ent  = new EntityFuncionario();
                $ent -> feedsEntity($dao);

                if(Security::isAuthList($this->usuario, $ent->getAttr('secretaria'), $ent->getAttr('departamento')))
                {
                    $tabBody[] = [
                        Html::pbig($ent->getAttr('funcionario'))
                       .Html::psmall('CPF: '.$ent->getAttr('cpf')),

                        Html::psmall(Utils::at($ent->getAttr('secretaria'), $secretarias))
                       .Html::psmall(Utils::at($ent->getAttr('departamento'), $departamentos)),
    
                        Html::psmall(EntityFuncionario::vinculoArr()[$ent->getAttr('vinculo')])
                       .Html::psmall(EntityFuncionario::cargahorariaArr()[$ent->getAttr('cargahoraria')]),
    
                        Html::psmall($ent->getAttr('telefone'))
                       .Html::psmall($ent->getAttr('email'))
                       .Html::psmall($ent->getAttr('endereco')),
    
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
        
        $facDAO = (new FactoryDao())->daoFuncionario();
        $getDAO = $facDAO->readData($params);

        return Alerts::notify(
            $getDAO != null ? 'success' : 'warning',
            $getDAO != null ? 'Dados recuperados para ediçao' : 'Falha ao recuperar dados',
            $getDAO,
            $this->usuario
        );
    }

    /**
     * Request departamentos by id secretaria to load async
     *
     * @return string
     */
    public function dataselect():string
    {
        $lstDAO = [];
        $facDAO = (new FactoryDao())->daoDepartamento();
        $getDAO = $facDAO->readData(['secretaria' => Route::gets()['key']], true, 'departamento');

        if($getDAO != null)
        {
            foreach ($getDAO as $item) {
                if(Security::isAuthList($this->usuario, $item['secretaria'], $item['id']))
                {
                    $lstDAO[$item['id']] = $item['departamento'];
                }
            }
        }

        return json_encode([
            'html' => Html::comboBox($lstDAO)
        ]);
    }

    /**
     * Proccess request form insert or up data in page
     *
     * @return string
     */
    public function proccess():string
    {

        //insert and update
        if(Forms::validForm('token', EntityFuncionario::getObrPropsClass())){

            //execute DAO and return alerts states
            $facDAO = (new FactoryDao())->daoFuncionario();
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
                
                $entity =  new EntityFuncionario();
                $entity -> feedsEntity($params);

                $facDAO = (new FactoryDao())->daoFuncionario($entity);
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
            return array_filter(Forms::getPost(['secretaria', 'departamento', 'funcionario', 'cpf']));
        }else{
            return [];
        }
    }
}