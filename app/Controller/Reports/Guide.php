<?php
namespace Octus\App\Controller\Reports;
use Octus\App\Controller\Data\FactoryDao;
use Octus\App\Controller\Pages\Components\DataList;
use Octus\App\Model\EntityDepartamento;
use Octus\App\Model\EntityFolha;
use Octus\App\Model\EntityCompany;
use Octus\App\Utils\Dates;
use Octus\App\Utils\Html;
use Octus\App\Utils\Utils;

class Guide extends Report
{
    public function __construct(?EntityCompany $infosys = null){
        parent::__construct($infosys);
    }

    private function gethtml(EntityFolha $request):string
    {
        $resposavel   = ((new FactoryDao())->daoUsuario())->readData(['id'=>$request->getAttr('responsavel')]);
        $secretaria   = ((new FactoryDao())->daoSecretaria())->readData(['id'=>$request->getAttr('secretaria')]);
        $departamento = ((new FactoryDao())->daoDepartamento())->readData(['id'=>$request->getAttr('departamento')]);
        

        $params = [
            'folha_data'       => Utils::at($request->getAttr('mes'), Dates::getMesesArr()).' de '.$request->getAttr('ano'),
            'folha_resp'       => Utils::attr('nome', $resposavel),
            'folha_envio'      => Dates::fmttDateTimeView($request->getAttr('dtinfo')),
            'depto_tipo'       => Utils::at(Utils::attr('tipo', $departamento), EntityDepartamento::tipoArr()),
            'depto_nome'       => Utils::attr('departamento', $departamento),
            'depto_secretaria' => Utils::attr('secretaria', $secretaria),
            'depto_endereco'   => Utils::attr('endereco', $departamento),
            'depto_telefone'   => Utils::attr('telefone', $departamento),
            'depto_email'      => Utils::attr('email', $departamento),
            'list_alteracoes'  => $this->listAlts($request)

        ];

        return $this->getReport('Alteracao Detalhada de Folha', 'reports/guide', $params);
    }

    private function listAlts(EntityFolha $folha):string
    {
        $facDAO = (new FactoryDao())->daoFolha();
        $getDAO = $facDAO->readData(
            ['mes'=>$folha->getAttr('mes'), 'ano'=>$folha->getAttr('ano'), 'departamento'=>$folha->getAttr('departamento')],
            true,
            'id'
        );

        //initialize table args
        $tabKeys  = ['Funcionario', 'QT. Faltas', 'Dias Faltas', 'Adicional Noturno', 'Dias Ajuda de Custo', 'KMs Ajuda de Custo', 'Horas Extras', 'Observações'];
        $tabBody  = [];

        if($getDAO != null){
            foreach ($getDAO as $dao) {

                $ent    = new EntityFolha();
                $ent   -> feedsEntity($dao);
                $worker = ((new FactoryDao())->daoFuncionario())->readData(['id' => $ent->getAttr('funcionario')]);

                $tabBody[] = [
                    Utils::attr('funcionario', $worker),
                    $ent->getAttr('qtfaltas'),
                    $ent->getAttr('dtfaltas'),
                    Utils::at($ent->getAttr('adcnoturno'), EntityFolha::addnoturnoArr()),
                    $ent->getAttr('qtajudacusto'),
                    $ent->getAttr('kmajudacusto'),
                    $ent->getAttr('horaextra'),
                    $ent->getAttr('observacoes'),
                ];

            }
        }

        return Html::genericTable($tabBody, $tabKeys);
    }

    public function report(?EntityFolha $request, ?string $title = 'Altercao de Folha Export PDF', string $mode = 'open'):string
    {
        return $this->exportReport($title, $this->gethtml($request), $mode);
    }

    
}