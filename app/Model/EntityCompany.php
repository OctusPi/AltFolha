<?php
namespace Octus\App\Model;

use Octus\App\Model\Entity;
use Octus\App\Controller\Data\ConnDB;

class EntityCompany extends Entity
{
    protected string  $sistema;
    protected string  $descricao;
    protected string  $company;
    protected ?string $cnpj;
    protected ?string $endereco;
    protected ?string $telefone;
    protected ?string $email;
    protected ?string $logo;
    protected ?string $urlbase;

    /**
     * Construct of class
     */
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
        return ConnDB::TAB_COMPN;
    }

    /**
     * Return array with mandatory propertys of class
     *
     * @return array
     */
    public static function getObrPropsClass():array
    {
        return ['sistema', 'descricao', 'company'];
    }

}
