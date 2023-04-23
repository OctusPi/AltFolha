<?php
namespace Octus\App\Controller\Data;

use Octus\App\Model\EntityCompany;
use Octus\App\Model\EntityDepartamento;
use Octus\App\Model\EntityFolha;
use Octus\App\Model\EntityFuncionario;
use Octus\App\Model\EntitySecretaria;
use Octus\App\Model\EntityUsuario;
use Octus\App\Controller\Data\ImpDao;

class FactoryDao
{
    /**
     * Method return new instance of implementation Data Access Object using Entity Info
     *
     * @return ImpDao
     */
    public function daoCompany(?EntityCompany $entity = null):ImpDao
    {
        return new ImpDao($entity != null ? $entity : new EntityCompany());
    }

    /**
     * Method return new instance of implementation Data Access Object using Entity Usuario
     *
     * @return ImpDao
     */
    public function daoUsuario(?EntityUsuario $entity = null):ImpDao
    {
        return new ImpDao($entity != null ? $entity : new EntityUsuario());
    }

    /**
     * Method return new instance of implementation Data Access Object using Entity Usuario
     *
     * @return ImpDao
     */
    public function daoDepartamento(?EntityDepartamento $entity = null):ImpDao
    {
        return new ImpDao($entity != null ? $entity : new EntityDepartamento());
    }

    /**
     * Method return new instance of implementation Data Access Object using Entity Usuario
     *
     * @return ImpDao
     */
    public function daoFolha(?EntityFolha $entity = null):ImpDao
    {
        return new ImpDao($entity != null ? $entity : new EntityFolha());
    }

    /**
     * Method return new instance of implementation Data Access Object using Entity Usuario
     *
     * @return ImpDao
     */
    public function daoFuncionario(?EntityFuncionario $entity = null):ImpDao
    {
        return new ImpDao($entity != null ? $entity : new EntityFuncionario());
    }

    /**
     * Method return new instance of implementation Data Access Object using Entity Usuario
     *
     * @return ImpDao
     */
    public function daoSecretaria(?EntitySecretaria $entity = null):ImpDao
    {
        return new ImpDao($entity != null ? $entity : new EntitySecretaria());
    }
}