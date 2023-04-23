<?php
namespace Octus\App\Model;

use Octus\App\Controller\Data\ConnDB;
use Octus\App\Model\Entity;

class EntitySecretaria extends Entity
{
    protected string $secretaria;
    protected ?string $cnpj;
    protected string $endereco;
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
        return ConnDB::TAB_SECTR;
    }

    /**
     * Method return array with exclusive properties to entity
     *
     * @return array
     */
    public function getExclusivePropsClass():array
    {
        return ['secretaria'];
    }

    /**
     * Return array with mandatory propertys of class
     *
     * @return array
     */
    public static function getObrPropsClass():array
    {
        return ['secretaria', 'endereco'];
    }

}