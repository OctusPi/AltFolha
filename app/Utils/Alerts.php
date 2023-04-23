<?php
namespace Octus\App\Utils;

use Octus\App\Model\Entity;
use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Logs;
use Octus\App\Utils\Utils;

class Alerts
{
    const STATUS_ERROR   = 'error';
    const STATUS_OK      = 'success';
    const STATUS_WARNING = 'warning';
    const STATUS_FAIL    = 'rededown';
    const STATUS_FORM    = 'mandatory';
    const STATUS_SEACH   = 'leastone';
    const STATUS_DUPLI   = 'duplici';
    const STATUS_NFOUND  = 'notfound';

    /**
     * array with text pattern to syste messages
     *
     * @return array
     */
    private static function deftxt():array
    {
        return [
            self::STATUS_ERROR   => 'Erro ao processar solicitação... Acione o administrador do sistema!',
            self::STATUS_OK      => 'Operação realizada com sucesso!',
            self::STATUS_WARNING => 'Atenção! Sua soliciação não pode ser processada!',
            self::STATUS_FAIL    => 'Sua solicitação falhou! Verifique sua conexão e tente novamente',
            self::STATUS_FORM    => 'Campos obrigatórios não informados!',
            self::STATUS_SEACH   => 'Informe pelo menos 01 item para realizar a busca!',
            self::STATUS_DUPLI   => 'Solicitação não processada, tentativa de duplicação de dados!',
            self::STATUS_NFOUND  => 'Dados não localizados!',
        ];
    }

    /**
     * json_encode enconder msg alert in two objects status, entity and register log action
     *
     * @param string $code
     * @param string|null $details
     * @param Entity|null $entity
     * @param EntityUsuario|null $usuario
     * @return string
     */
    public static function notify(string $code, ?string $details = null, ?Entity $entity = null, ?EntityUsuario $usuario = null):string
    {
        //define message alert and entity feed form callback
        $status      = ['statuscode' => $code, 'message' => Utils::at($code, self::deftxt()), 'details' => $details];
        $propsentity = $entity != null ? $entity->getPropsAndValues() : null;

        //wirte log according to action taken
        Logs::writeLog($code.': '.Utils::at($code, self::deftxt()).' - '.$details, $usuario);

        return json_encode([
            'status' => $status,
            'entity' => $propsentity
        ]);
    }
}