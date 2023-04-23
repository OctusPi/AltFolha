<?php
namespace Octus\App\Controller\Pages;


use Octus\App\Controller\Pages\Components\DataList;
use Octus\App\Controller\Reports\Guide;
use Octus\App\Controller\Rules\FolhaRules;
use Octus\App\Model\EntityDepartamento;
use Octus\App\Model\EntityFolha;
use Octus\App\Model\EntityFuncionario;
use Octus\App\Utils\Dates;
use Octus\App\Utils\Html;
use Octus\App\Utils\Logs;
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

class Folhas extends Page
{
    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario, true, EntityUsuario::PRF_DEPTO, EntityUsuario::NVL_FOLHA);
    }

    /**
     * Proccess request form insert or up data in page
     *
     * @return string
     */
    public function proccess():string
    {

        //insert and update
        if(Forms::validForm('token', EntityFolha::getObrPropsClass())){

            $form   = Forms::getPost();
            $mes    = intval(Utils::at('mes', $form));
            $ano    = intval(Utils::at('ano', $form));
            $sect   = intval(Utils::at('secretaria', $form));
            $depto  = intval(Utils::at('departamento', $form));
            $folhaRule = (new FolhaRules($mes, $ano))->validate(); 
            
            if($folhaRule['status']){
                
                //initialize array returns
                $return = [];

                //rescue array workers
                $facDAO  = (new FactoryDao())->daoFuncionario();
                $workers = $facDAO->readData(['secretaria'=>$sect, 'departamento'=>$depto], true, 'funcionario');

                //run array workers to contruct folha
                if($workers != null){
                    foreach ($workers as $worker) {
                        //rescue worker ID
                        $workerID = $worker['id'];

                        //initialize DAO and rescue database
                        $daoFolha = (new FactoryDao())->daoFolha();
                        $daoFolha ->readData(['mes'=>$mes, 'ano'=>$ano, 'funcionario'=>$workerID]);

                        //create feed entity folha
                        $feed = [
                            'mes'           => $mes,
                            'ano'           => $ano,
                            'secretaria'    => $sect,
                            'departamento'  => $depto,
                            'funcionario'   => $workerID,
                            'qtfaltas'      => intval(Utils::at('qtfaltas'.$workerID, $form)),
                            'dtfaltas'      => Utils::at('dtfaltas'.$workerID, $form),
                            'adcnoturno'    => Utils::at('adcnoturno'.$workerID, $form),
                            'qtajudacusto'  => intval(Utils::at('qtajudacusto'.$workerID, $form)),
                            'kmajudacusto'  => intval(Utils::at('kmajudacusto'.$workerID, $form)),
                            'horaextra'     => Utils::at('horaextra'.$workerID, $form),
                            'observacoes'   => Utils::at('observacoes'.$workerID, $form),
                            'responsavel'   => $this->usuario->getAttr('id')
                        ];

                        $daoFolha-> getEntity()->feedsEntity($feed);
                        $wrtFolha = $daoFolha->writeData();

                        $return[] = $wrtFolha['code'];
                    }

                    $stsReturn = match($return){
                        in_array(Alerts::STATUS_WARNING, $return) => ['status'=>Alerts::STATUS_WARNING, 'details'=>'Falha ao Processar alguns dados'],
                        default => ['status'=>Alerts::STATUS_OK, 'details'=>'Alterações Registradas']
                    };
                    
                    return Alerts::notify($stsReturn['status'], $stsReturn['details'], null, $this->usuario);

                }else{
                    return Alerts::notify(Alerts::STATUS_WARNING, 'Nao existem funcionarios vinculados ao departamento', null, $this->usuario);
                }

            }else{
                
                return Alerts::notify($folhaRule['notify'], $folhaRule['detail'], null, $this->usuario);
            }
        }

        return Alerts::notify(Alerts::STATUS_WARNING, 'Formulario inválido, atualize a página e tente novamente...', null, $this->usuario);
    }

    /**
     * Render view page html
     *
     * @return string
     */
    public function viewpage():string
    {
        $mesfolha = date('n') == 1 ? 12 : date('n') - 1;
        $anofolha = date('n') == 1 ? (date('Y') - 1) : date('Y');

        $params = [
            'form_search'         => View::renderView('fragments/forms/search/folhas'),
            'search_action'       => Route::route(['action'=>'view']),
            'form_meses'          => Html::comboBox(Dates::getMesesArr(), $mesfolha, true),
            'form_anos'           => Html::comboBox(Dates::listYears(), $anofolha, true),
            'form_secretarias'    => Html::comboBox(DataList::listSecretarias($this->usuario)),
            'form_departamentos'  => Html::comboBox(DataList::listDepartamentos($this->usuario)),
            'action'              => Route::route(['action'=>'send']),
            'data_page'           => json_decode($this->datahtml())->{'html'}
        ];

        return $this->getPage('Registro de Alteração', 'pages/folhas', $params);
    }

    public function datahtml():string
    {
        $search      = $this->search();
        $searchDepto = Utils::at('departamento', $search) != null ? ['id'=>$search['departamento']] : [];

        //initialize table args
        $tabKeys  = ['Departamento', 'Folha', 'Com Alteração', 'Sem Alteração', 'Data Envio', 'Responsável', ''];
        $tabBody  = [];

        $usuarios      = DataList::list('daoUsuario');
        $secretarias   = DataList::listSecretarias($this->usuario);
        $departamentos = ((new FactoryDao())->daoDepartamento())->readData($searchDepto, true, 'departamento');
        

        //data access object Folha
        $facDAO = (new FactoryDao())->daoFolha();
        $getDAO = $facDAO->readData($search, true, 'departamento');

        if($departamentos != null)
        {
            foreach ($departamentos as $depto) {
                $entDepto = new EntityDepartamento();
                $entDepto-> feedsEntity($depto);

                if(Security::isAuthList($this->usuario, $entDepto->getAttr('secretaria'), $entDepto->getAttr('id')))
                {
                    //initialize vars to feed body table
                    $altTotal   = 0;
                    $noaltTotal = 0;
                    $dataSend   = null;
                    $respSend   = '';
                    $idFolha    = 0;

                    //condense values in database altfolha
                    if($getDAO != null)
                    {
                        foreach ($getDAO as $dao)
                        {
                            $entFolha = new EntityFolha();
                            $entFolha-> feedsEntity($dao);

                            if($entDepto->getAttr('id') == $entFolha->getAttr('departamento'))
                            {
                                $dataSend = $entFolha->getAttr('dtinfo');
                                $respSend = $entFolha->getAttr('responsavel');
                                $idFolha  = $entFolha->getAttr('id');

                                if( $entFolha->getAttr('qtfaltas') || 
                                    $entFolha->getAttr('adcnoturno') == 2 ||
                                    $entFolha->getAttr('qtajudacusto') ||
                                    $entFolha->getAttr('horaextra')){
                                        $altTotal++;
                                    }else{
                                        $noaltTotal++;
                                    }
                            }
                        }
                    }

                    //feeds body table
                    $tabBody[] = [
                        Html::pbig($entDepto->getAttr('departamento')).
                        Html::psmall(Utils::at($entDepto->getAttr('secretaria'), $secretarias)),

                        Html::psmall(Dates::getMesesArr()[$search['mes']].' de '.$search['ano']),

                        Html::pbig($altTotal),
                        Html::pbig($noaltTotal),
                        Html::psmall($dataSend != null ? Dates::fmttDateTimeView($dataSend) : 'Não Enviado'),
                        Html::psmall(Utils::at($respSend, $usuarios)),
                        $idFolha > 0 ? Html::tabAction($idFolha, 'adminfolha') : ''
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

    /**
     * Request departamentos by id secretaria to load async
     *
     * @return string
     */
    public function dataselect():string
    {
        switch(Route::gets()['type']){
            case 'workers':
                return $this->dataselectWorkers();
            
            default:
                return $this->dataselectDeptos();
        }
    }

    public function report():string
    {
        $facDao = (new FactoryDao())->daoFolha();
        $facDao->readData(['id' => intval(Route::gets()['key'])]);

        return (new Guide($this->company))->report($facDao->getEntity());
    }

    private function dataselectWorkers():string
    {

        $listWorker = '';
        $facDAO     = (new FactoryDao())->daoFuncionario();
        $getDAO     = $facDAO->readData(['departamento' => Route::gets()['key']], true, 'funcionario');

        if($getDAO != null)
        {
            foreach ($getDAO as $item) {
                if(Security::isAuthList($this->usuario, $item['secretaria'], $item['departamento']))
                {
                    $worker =  new EntityFuncionario();
                    $worker -> feedsEntity($item);

                    $params = [
                        'inp_worker'     => Html::pbig($worker->getAttr('funcionario')).
                                            Html::psmall($worker->getAttr('cpf')),
                        'inp_faltas'     => Html::input('qtfaltas', $worker->getAttr('id'), class:'ocp-input-tab', title:'Total de Faltas não justificadas'),
                        'inp_dfaltas'    => Html::input('dtfaltas', $worker->getAttr('id'), class:'ocp-input-tab', title:'Dias do mês com faltas não justificadas (separe com virgula)'),
                        'inp_adnorturno' => Html::select('adcnoturno', $worker->getAttr('id'), EntityFolha::addnoturnoArr(), class:'ocp-input-tab'),
                        'inp_qthelp'     => Html::input('qtajudacusto', $worker->getAttr('id'), class:'ocp-input-tab'),
                        'inp_kmhelp'     => Html::input('kmajudacusto', $worker->getAttr('id'), class:'ocp-input-tab'),
                        'inp_hextra'     => Html::input('horaextra', $worker->getAttr('id'), class:'ocp-input-tab'),
                        'inp_obs'        => Html::input('observacoes', $worker->getAttr('id'), class:'ocp-input-tab text-start', defvalue:'')
                    ];

                    $listWorker.= View::renderView('fragments/forms/folhasfuncionariosline', $params);
                }
            }

            return json_encode([
                'html' => View::renderView('fragments/forms/folhasfuncionarios', ['list_workersline' => $listWorker])
            ]);
        }

        return json_encode([
            'html' => Html::defmsg(' Não existem funcionarios vinculados ao departamento. '.Html::hrefmsg('Add. Funcionarios', '?app=funcionarios'))
        ]);
    }

    private function dataselectDeptos():string
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
     * method proccess form search
     *
     * @return array
     */
    private function search():array
    {
        if(Forms::validForm('token_search')){
            return array_filter(Forms::getPost(['secretaria', 'departamento', 'mes', 'ano']));
        }else{
            $mesfolha = date('n') == 1 ? 12 : date('n') - 1;
            $anofolha = date('n') == 1 ? (date('Y') - 1) : date('Y');
            return ['mes'=>$mesfolha,'ano'=>$anofolha];
        }
    }
}