<?php
namespace Octus\App\Controller\Pages;

use Octus\App\Controller\Pages\Components\DataList;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Pages\Page;
use Octus\App\Controller\Data\FactoryDao;
use Octus\App\Model\EntityFuncionario;
use Octus\App\Utils\Files;
use Octus\App\Utils\Html;
use Octus\App\Utils\Logs;
use Octus\App\Utils\Mask;
use Octus\App\Utils\Security;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Route;
use Octus\App\Utils\Alerts;
use Octus\App\Utils\Session;
use Octus\App\Utils\Utils;
use Octus\App\Utils\View;

class ImportFuncionarios extends Page
{
    private string $path;

    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario, true, EntityUsuario::PRF_DEPTO, EntityUsuario::NVL_FUNCIONARIOS);
        $this->path = __DIR__.'/../../../uploads/'.md5($this->usuario->getAttr('id')).'.csv';
    }

    /**
     * Render view page html
     *
     * @return string
     */
    public function viewpage():string
    {
        $params = [
            'modelo_import'       => 'resources/base/modelo_import_funcionarios.csv',
            'form_reloadview'     => Route::route(['action'=>'view']),
            'action'              => Route::route(['action'=>'send']),
        ];

        return $this->getPage('Ferramenta de Importar Funcionários', 'pages/importfuncionarios', $params);
    }

    public function datahtml():string
    {
        $params = $this->prepareFile();

        if($params['body'] != null){
            $body = [];
            foreach ($params['body'] as $line) {
                $body[] = [
                    Html::psmall(Utils::at('0', $line)),
                    Html::psmall(Utils::at('1', $line)),
                    Html::psmall(Utils::at('2', $line)),
                    Html::psmall(EntityFuncionario::vinculoArr()[Utils::at('3', $line)]),
                    Html::psmall(EntityFuncionario::cargahorariaArr()[Utils::at('4', $line)])
                ];
            }

            $paramView = [
                'list_imports'       => Html::genericTable($body, $params['header']),
                'form_secretarias'   => Html::comboBox(DataList::listSecretarias($this->usuario)),
                'form_departamentos' => Html::comboBox(DataList::listDepartamentos($this->usuario))
            ];

            return json_encode([
                'html' => View::renderView('fragments/forms/importfuncionarios', $paramView)
            ]);
        }

        return json_encode([
            'html' => Html::defmsg('Arquivo não localizado ou já processdo!')
        ]);
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
        if(Forms::validForm('token')){

            //execute DAO and return alerts states
           $file   =  new Files();
           $upFile = $file->up($_FILES['lotecsv'], true, md5($this->usuario->getAttr('id')));

            return Alerts::notify(
                $upFile['status'][0] ? Alerts::STATUS_OK : Alerts::STATUS_WARNING,
                $upFile['info'][0],
                null,
                $this->usuario
            );
            
        }

        if(Forms::validForm('token_import', array_keys($_POST))){
           
            $import = $this->prepareFile();

            if($import['body'] != null)
            {
                $fail      = 0;
                $duplicity = 0;
                $success   = 0;

                foreach($import['body'] as $item)
                {
                    $feed = [
                        'funcionario'   => $item[0],
                        'cpf'           => $item[1],
                        'funcao'        => $item[2],
                        'vinculo'       => $item[3],
                        'cargahoraria'  => $item[4],
                    ];

                    //start DAO and feed with static values
                    $facDAO =  (new FactoryDao())->daoFuncionario();
                    $facDAO -> getEntity()->feedsEntity(Forms::getPost());
                    
                    //feed with dinamic values and write data
                    $facDAO -> getEntity()->feedsEntity($feed);
                    $wrtDAO =  $facDAO->writeData();

                    Logs::writeLog(implode(',', $feed));

                    //increment success, duplicitys and fails
                    switch($wrtDAO['code']){
                        case Alerts::STATUS_OK:
                            $success++;
                            break;
                        case Alerts::STATUS_DUPLI:
                            $duplicity++;
                            break;
                        default:
                            $fail++;
                            break;
                    }

                }

                unlink($this->path);

                return Alerts::notify(
                    $success > 0 ? Alerts::STATUS_OK : Alerts::STATUS_WARNING,
                    'Detalhes - Importações: '.$success.', Falhas: '.$fail.', Duplicidades descartadas: '.$duplicity,
                    null,
                    $this->usuario
                );
            }else{
                
                return Alerts::notify(Alerts::STATUS_WARNING, 'Arquivo não localizado ou já processdo!', null, $this->usuario);
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

    private function prepareFile():array
    {
        //start body and header to table
        $tabKeys = [];
        $tabBody = [];

        if(file_exists($this->path)){

            //read file and covert array lines
            $file = file_get_contents($this->path);
            $line = explode(PHP_EOL, $file);

            $tabKeys = explode(',', $line[0]);

            //covert array cols and feed body table
            if($line != null){
                foreach ($line as $key => $value) {
                    if($key > 0){
                        $cols = explode(',', $value);
                        if(!in_array(null, $cols)){
                            $tabBody[] = [
                                Utils::at('0', $cols),
                                Mask::maskCPF(Utils::at('1', $cols)),
                                Utils::at('2', $cols),
                                Mask::maskVinculo(Utils::at('3', $cols)),
                                Mask::maskCarga(Utils::at('4', $cols))
                            ];
                        }
                    }
                }
            }
        }

        return [
            'header' => $tabKeys,
            'body'   => $tabBody
        ];
    }

}