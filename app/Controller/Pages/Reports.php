<?php
namespace Octus\App\Controller\Pages;


use Octus\App\Controller\Data\FactoryDao;
use Octus\App\Controller\Pages\Components\DataList;
use Octus\App\Controller\Reports\Enrollment;
use Octus\App\Utils\Dates;
use Octus\App\Utils\Forms;
use Octus\App\Utils\Html;
use Octus\App\Utils\Route;
use Octus\App\Utils\Session;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Pages\Page;
use Octus\App\Utils\Utils;

class Reports extends Page
{
    public function __construct(Session $session, ?EntityUsuario $usuario = null)
    {
        parent::__construct($session, $usuario, true, EntityUsuario::PRF_GESTOR, EntityUsuario::NVL_RPORTS);
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
            'form_meses'          => Html::comboBox(Dates::getMesesArr(), $mesfolha, true),
            'form_anos'           => Html::comboBox(Dates::listYears(), $anofolha, true),
            'form_secretarias'    => Html::comboBox(DataList::listSecretarias($this->usuario)),
            'form_departamentos'  => Html::comboBox(DataList::listDepartamentos($this->usuario)),
            'action'              => Route::route(['action'=>'send'])
        ];

        return $this->getPage('Gerar Relatórios', 'pages/reports', $params);
    }

    public function datasrc():array
    {
        $search  = $this->search();
        $facDAO  = new FactoryDao();
        $folhas  = ($facDAO->daoFolha())->readData($search['search'], true, 'id');
        
        return [
            'search' => array_merge($search ['search'], $search ['params']),
            'params' => $search ['params'],
            'list'   => $folhas
        ];
    }

    /**
     * Proccess request form insert or up data in page
     *
     * @return string
     */
    public function proccess():string
    {
        $report = new Enrollment($this->company);
        return $report->report($this->datasrc(), md5($this->usuario->getAttr('email')));
    }

    /**
     * method proccess form search
     *
     * @return array
     */
    private function search():array
    {
        if(Forms::validForm('token_search')){
            return [
                'search' => array_filter(Forms::getPost(['secretaria', 'departamento', 'mes', 'ano'])),
                'params' => ['details' => Utils::at('details', Forms::getPost())],
            ];
        }else{
            return [];
        }
    }
}