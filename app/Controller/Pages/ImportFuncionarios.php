<?php
namespace Octus\App\Controller\Pages;

use Ark4ne\XlReader\Factory;
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
        $this->path = __DIR__.'/../../../uploads/'.md5($this->usuario->getAttr('id')).'.xlsx';
    }

    /**
     * Render view page html
     *
     * @return string
     */
    public function viewpage():string
    {
        $params = [
            'modelo_import'       => 'resources/base/modelo_import_funcionarios.xlsx',
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
                    Html::psmall(Utils::at('matricula', $line)),
                    Html::psmall(Utils::at('funcionario', $line)),
                    Html::psmall(Utils::at('cpf', $line)),
                    Html::psmall(Utils::at('telefone', $line)),
                    Html::psmall(Utils::at('endereco', $line)),
                    Html::psmall(Utils::at('email', $line)),
                    Html::psmall(Utils::at('funcao', $line)),
                    Html::psmall(EntityFuncionario::vinculoArr()[Utils::at('vinculo', $line)]),
                    Html::psmall(EntityFuncionario::cargahorariaArr()[Utils::at('cargahoraria', $line)])
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

                foreach($import['body'] as $feed)
                {

                    //start DAO and feed with static values
                    $facDAO =  (new FactoryDao())->daoFuncionario();
                    $facDAO -> getEntity()->feedsEntity(Forms::getPost());
                    
                    //feed with dinamic values and write data
                    $facDAO -> getEntity()->feedsEntity($feed);
                    $wrtDAO =  $facDAO->writeData();

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

            //read file and covert array lines with lib ark4ne/xl-reader
        
            try{
                $file   = $this->path;
                $reader = Factory::createReader($file);
                $reader-> load();

                $tabKeys = $reader->read()->current();
                $fields  = [];
                foreach ($reader->read(2) as $row) {
                    
                    $line = [];
                    foreach ($tabKeys as $key => $field) {
                        $line[$key] = $row[$key] ?? null;
                    }
                    $fields[] = $line;
                    
                }

                //mask values to sheet
                foreach ($fields as $value) {
                    if($value['B']!=null && $value['C']!=null && $value['G']!=null && $value['H']!=null && $value['I']!=null)
                    {
                        $temp = [];
                        $cols = [
                            'A'=>'matricula',
                            'B'=>'funcionario',
                            'C'=>'cpf',
                            'D'=>'telefone',
                            'E'=>'endereco',
                            'F'=>'email',
                            'G'=>'funcao',
                            'H'=>'vinculo',
                            'I'=>'cargahoraria',
                        ];

                        foreach ($value as $k => $v) {
                            $index    = $cols[$k];
                            $mskvalue = match($index){
                                'cpf'          => Mask::maskCPF($v),
                                'vinculo'      => Mask::maskVinculo($v),
                                'cargahoraria' => Mask::maskCarga($v),
                                default        => $v
                            };

                            $temp[$index] = Security::sanitize($mskvalue);
                        }
                        $tabBody[] = $temp;
                    }
                }

            }catch(\Exception $e){
                Logs::writeLog('ERROR: Arquivo Import Upload Não Aceito!');
            }
        }

        return [
            'header' => $tabKeys,
            'body'   => $tabBody
        ];
    }

}