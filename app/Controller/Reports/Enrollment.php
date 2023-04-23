<?php
namespace Octus\App\Controller\Reports;

use Octus\App\Controller\Data\FactoryDao;
use Octus\App\Controller\Pages\Components\DataList;
use Octus\App\Model\EntityCompany;
use Octus\App\Model\EntityFolha;
use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Dates;
use Octus\App\Utils\Html;
use Octus\App\Utils\Money;
use Octus\App\Utils\Utils;

class Enrollment extends Report
{

    public function __construct(?EntityCompany $infosys = null){
        parent::__construct($infosys);
    }

    private function getsearch(?array $search):string
    {
        $html = '';

        foreach($search as $k => $v){
            
            $chave = match ($k) {
                'mes'          => 'Mês Folha: ',
                'ano'          => 'Ano Folha: ',
                'secretaria'   => 'Secretaria: ',
                'departamento' => 'Departamento: ',
                'details'      => 'Visualizaçao Detalhada: ',
                default        => ''
            };

            $valor = match ($k) {
                'mes'          => Utils::at($v, Dates::getMesesArr()),
                'secretaria'   => Utils::at($v, DataList::list('daoSecretaria', 'id', 'secretaria')),
                'departamento' => Utils::at($v, DataList::list('daoDepartamento', 'id', 'departamento')),
                'details'      => $v != null ? 'SIM' : 'NAO',
                default        => $v
            };

            $html .= Html::psmall($chave.$valor);
        }

        return $html;
    }

    private function gethtml(?array $content):string
    {
        $params = [
            'list_search'   => $this->getsearch(Utils::at('search', $content)),
            'tab_altfolha'  => $this->getTabAlt($content),
            'list_altfolha' => $this->getListAlt($content)
        ];

        return $this->getReport('Relatório Alteração de Folha', 'reports/enrollment', $params);
    }

    private function getTabAlt(?array $content):string
    {
        $list = $content['list'];
        
        $totalFaltas        = 0;
        $totalAddNoturno    = 0;
        $totalDiaAjudaCusto = 0;
        $totalKmAjudaCusto  = 0;
        $totalHorasExtras   = 0;

        if($list != null)
        {
            foreach ($list as $item) {

                $totalFaltas        += $item['qtfaltas'];
                $totalDiaAjudaCusto += $item['qtajudacusto'];
                $totalKmAjudaCusto  += $item['kmajudacusto'];
                $totalHorasExtras   += Money::getFloat($item['horaextra']);
                if($item['adcnoturno'] == 2){
                    $totalAddNoturno++;
                }
            }
        }

        //initialize table args
        $tabKeys    = ['Faltas Não Justificadas', 'Total Adicional Noturno', 'Total Dias Ajuda de Custo', 'Total KM Ajuda de Custo', 'Total Hotas Extras'];
        $tabBody[]  = [$totalFaltas, $totalAddNoturno, $totalDiaAjudaCusto, $totalKmAjudaCusto, $totalHorasExtras];


        return Html::genericTable($tabBody, $tabKeys, false);
    }

    private function getListAlt(?array $content):string
    {
        if(Utils::at('details', $content['params']) != null){
            
            //list workers folha alt
            $list = $content['list'];

            //initialize table args
            $tabKeys  = ['Departamento', 'Funcionario', 'QT. Faltas', 'Dias Faltas', 'Adicional Noturno', 'Dias Ajuda de Custo', 'KMs Ajuda de Custo', 'Horas Extras', 'Observações'];
            $tabBody  = [];

            if($list != null){
                foreach ($list as $dao) {
    
                    $ent    = new EntityFolha();
                    $ent   -> feedsEntity($dao);
                    $depto =  ((new FactoryDao())->daoDepartamento())->readData(['id' => $ent->getAttr('departamento')]);
                    $sectr =  ((new FactoryDao())->daoSecretaria())->readData(['id' => $ent->getAttr('secretaria')]);
                    $worker = ((new FactoryDao())->daoFuncionario())->readData(['id' => $ent->getAttr('funcionario')]);
    
                    $tabBody[] = [
                        Html::psmall(Utils::attr('departamento', $depto)).
                        Html::psmall(Utils::attr('secretaria', $sectr)),
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
        }else{
            return '';
        }
    }

    public function report(?array $content, string $title, string $mode = 'save'):string
    {
        return $this->exportReport($title, $this->gethtml($content), $mode);
    }

    
}