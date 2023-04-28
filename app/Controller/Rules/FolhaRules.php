<?php
namespace Octus\App\Controller\Rules;

use Octus\App\Utils\Alerts;
use Octus\App\Utils\Logs;


class FolhaRules
{
    private int $mes;
    private int $ano;

    public function __construct(int $mes, int $ano)
    {
        $this->mes = $mes;
        $this->ano = $ano;
    }

    private function validateDay():array
    {
        $status = date('j') <= 20;
        $notify = $status ? Alerts::STATUS_OK : Alerts::STATUS_WARNING;
        $detail = $status ? '' : 'Não se pode enviar alterações após o dia 20'; 

        return[
            'status' => $status,
            'notify' => $notify,
            'detail' => $detail
        ];
    }

    private function validateMes():array
    {
        $mesfolha = date('n') == 1 ? 12 : date('n') - 1;
        
        $status = $mesfolha == $this->mes;
        $notify = $status ? Alerts::STATUS_OK : Alerts::STATUS_WARNING;
        $detail = $status ? '' : 'Não se pode enviar alterações para meses anteriores ou posteriores a folha de pagamento atual'; 

        return[
            'status' => $status,
            'notify' => $notify,
            'detail' => $detail
        ];

    }

    private function validateAno():array
    {
        $anofolha = date('n') == 1 ? (date('Y') - 1) : date('Y');

        $status = $anofolha == $this->ano;
        $notify = $status ? Alerts::STATUS_OK : Alerts::STATUS_WARNING;
        $detail = $status ? '' : 'Não se pode enviar alterações para anos anteriores'; 

        return[
            'status' => $status,
            'notify' => $notify,
            'detail' => $detail
        ];
    }

    public function validate():array
    {
        //initialize returns
        $status = true;
        $notify = Alerts::STATUS_OK;
        $detail = '';

        //run tests in validation fnuctions
        $tests  = [$this->validateDay(), $this->validateMes(), $this->validateAno()];
        foreach ($tests as $test) {
            if($test['status'] == false){
                $status = $test['status'];
                $notify = $test['notify'];
                $detail = $test['detail'];
                break;
            }
        }

        return[
            'status' => $status,
            'notify' => $notify,
            'detail' => $detail
        ];
    }
}