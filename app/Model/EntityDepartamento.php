<?php
namespace Octus\App\Model;

use Octus\App\Controller\Data\ConnDB;
use Octus\App\Model\Entity;

class EntityDepartamento extends Entity
{
    protected int $tipo;
    protected string $departamento;
    protected int $secretaria;
    protected ?string $endereco;
    protected ?string $telefone;
    protected ?string $email;

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
        return ConnDB::TAB_DEPTS;
    }

    /**
     * Method return array with exclusive properties to entity
     *
     * @return array
     */
    public function getExclusivePropsClass():array
    {
        return ['departamento', 'secretaria'];
    }

    /**
     * Return array with mandatory propertys of class
     *
     * @return array
     */
    public static function getObrPropsClass():array
    {
        return ['departamento', 'secretaria'];
    }

    public static function tipoArr():array
    {
        return[
            1 => 'Departamento',
            2 => 'Escola',
            3 => 'Creche'
        ];
    }

}