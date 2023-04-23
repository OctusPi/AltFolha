<?php
namespace Octus\App\Model;

use Octus\App\Controller\Data\ConnDB;
use Octus\App\Model\Entity;

class EntityFuncionario extends Entity
{
    protected int $secretaria;
    protected int $departamento;
    protected ?string $matricula;
    protected string $funcionario;
    protected string $cpf;
    protected ?string $telefone;
    protected ?string $endereco;
    protected ?string $email;
    protected string $funcao;
    protected int $vinculo;
    protected int $cargahoraria;
    

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
        return ConnDB::TAB_FUNCS;
    }

    /**
     * Method return array with exclusive properties to entity
     *
     * @return array
     */
    public function getExclusivePropsClass():array
    {
        return ['cpf'];
    }

    /**
     * Return array with mandatory propertys of class
     *
     * @return array
     */
    public static function getObrPropsClass():array
    {
        return ['secretaria', 'departamento', 'funcionario', 'funcao', 'vinculo', 'cargahoraria'];
    }

    public static function vinculoArr():array
    {
        return [
            1 => 'Efetivo',
            2 => 'Contratado',
            3 => 'Terceiriado'
        ];
    }

    public static function cargahorariaArr():array
    {
        return [
            1 => '100hs',
            2 => '200hs',
            3 => '300hs',
        ];
    }
}