<?php
namespace Octus\App\Model;

use Octus\App\Controller\Data\ConnDB;
use Octus\App\Model\Entity;

class EntityFolha extends Entity
{
    protected int $mes;
    protected int $ano;
    protected int $secretaria;
    protected int $departamento;
    protected int $funcionario;
    protected ?int $qtfaltas;
    protected ?string $dtfaltas;
    protected ?int $adcnoturno;
    protected ?int $qtajudacusto;
    protected ?int $kmajudacusto;
    protected ?string $horaextra;
    protected ?string $observacoes;
    protected string $dtinfo;
    protected int $responsavel;
    

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return name of table in database reference entity
     * @override
     * @return string|null
     */
    public function getDataTableEntity(): ?string 
    {
        return ConnDB::TAB_FOLHA;
    }

     /**
     * Method return array with exclusive properties to entity
     *
     * @return array
     */
    public function getExclusivePropsClass():array
    {
        return ['mes', 'ano', 'funcionario'];
    }

    /**
     * Return array with mandatory propertys of class
     *
     * @return array
     */
    public static function getObrPropsClass():array
    {
        return ['mes', 'ano', 'secretaria', 'departamento'];
    }

    public static function addnoturnoArr():array
    {
        return [
                1 => 'NÃO', 
                2 => 'SIM'
            ];
    }

}