<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Controller\Pages\Components\DataList;
use Octus\App\Utils\Html;
use Octus\App\Utils\Logs;
use Octus\App\Utils\Utils;
use Octus\App\Utils\View;
use Octus\App\Utils\Dates;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Emails;
use Octus\App\Utils\Session;
use Octus\App\Utils\Security;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;

class Admin extends Page
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
        $params = [
            'form_search'      => View::renderView('fragments/forms/search/admin'),
            'search_action'    => Route::route(['action'=>'view']),
            'action'           => Route::route(['action'=>'send']),
            'form_perfis'      => Html::comboBox(EntityUsuario::getPerfilArr()),
            'form_secretarias' => Html::comboBox(DataList::listSecretarias($this->usuario)),
            'form_departamentos' => Html::comboBox(DataList::list('daoDepartamento', 'id', 'departamento')),
            'form_status'      => Html::comboBox(EntityUsuario::getStatusArr()),
            'data_page'        => json_decode($this->datahtml())->{'html'}
        ];

        return $this->getPage('Gestão Sistema', 'pages/admin', $params);
    }

    public function datahtml():string
    {
        $facDAO  = (new FactoryDao)->daoUsuario();
        $getDAO  = $facDAO->readData($this->search(), true, 'nome');
        $tabKeys = ['Identificaçao', 'Perfil', 'Ultimo Acesso', ''];
        $tabBody = [];

        //feed body
        if($getDAO != null)
        {
            foreach($getDAO as $dao)
            {
                //convet array to entity
                $ent  = new EntityUsuario();
                $ent -> feedsEntity($dao);

                $tabBody[] = [
                    Html::pbig($ent->getAttr('nome'))
                   .Html::psmall($ent->getAttr('email')),

                    Html::pbig(EntityUsuario::getPerfilArr()[$ent->getAttr('perfil')])
                   .Html::psmall(EntityUsuario::getStatusArr()[$ent->getAttr('status')], 
                    EntityUsuario::getStatusColorArr()[$ent->getAttr('status')]),

                    Html::psmall(
                        $ent->getAttr('nowlogin') != null 
                        ? Dates::fmttDateTimeView($ent->getAttr('nowlogin'))
                        : 'Nunca Acessou'
                    ),

                    Html::tabAction($ent->getAttr('id'), 'admin')
                ];
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
        
        $facDAO = (new FactoryDao())->daoUsuario();
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
                $lstDAO[$item['id']] = $item['departamento'];
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
        if(Forms::validForm('token', EntityUsuario::getObrPropsClass())){

            $form  = Forms::getPost();
            $user  = new EntityUsuario();
            $user -> feedsEntity($form);
            $user -> setAttr('uid', md5($user->getAttr('cpf')));
            $user -> buildNivel($user->getAttr('perfil'));

            //check is new user and generate randon temp passwd
            $isnew   = $user->getAttr('id') == 0;
            $tmppass = $user->getAttr('cpf');//Security::randonPass();

            if($isnew)
            {
                $user->setAttr('pid', md5($tmppass));
                $user->setAttr('passchange', 1);
            }

            //execute DAO and return alerts states
            $facDAO = (new FactoryDao())->daoUsuario($user);
            $wrtDAO = $facDAO -> writeData();
            
            if($wrtDAO['status']){
            
                // if($isnew){
                //     $smail = Emails::send(Emails::NEWUSER, $facDAO->getEntity(), $this->company, ['tmppass' => $tmppass]);
                //     return Alerts::notify(
                //         $wrtDAO['code'],
                //         $smail ? 'E-mail com senha temporaria enviada' : 'Não foi possível enviar a senha por e-mail',
                //         $facDAO->getEntity(),
                //         $this->usuario
                //     );
                // }

                return Alerts::notify(
                    $wrtDAO['code'],
                    $isnew ? 'Novo usuário adicionado! Senha Inicial é o número de CPF' : 'Dados do usuáio foram alterados',
                    $facDAO->getEntity(),
                    $this->usuario
                );

            }else{
                return Alerts::notify($wrtDAO['code'], '', $facDAO->getEntity(), $this->usuario);
            }
            
        }

        //delete iten
        if(Forms::validForm('token_trash', array_keys($_POST))){
           
            $params = Forms::getPost(['id', 'passconfirm']);

            if(md5($params['passconfirm']) == $this->usuario->getAttr('pid')){
                
                $user =  new EntityUsuario();
                $user -> feedsEntity($params);

                $facDAO = (new FactoryDao())->daoUsuario($user);
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
            return array_filter(Forms::getPost(['nome', 'perfil']));
        }else{
            return [];
        }
    }
}