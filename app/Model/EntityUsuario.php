<?php
namespace Octus\App\Model;

use Octus\App\Utils\Utils;
use Octus\App\Model\Entity;
use Octus\App\Controller\Data\ConnDB;

class EntityUsuario extends Entity
{

    //constantes
    const PRF_ADMIN   = 1;
    const PRF_GESTOR  = 2;
    const PRF_DEPTO   = 3;

    const NVL_INICIAL      = 0;
    const NVL_ESTRUTURA    = 1;
    const NVL_FUNCIONARIOS = 2;
    const NVL_FOLHA        = 3;
    const NVL_RPORTS       = 4;
    const NVL_BIGBOSS      = 5;

    //propertys
    protected string  $nome;
    protected string  $cpf;
    protected string  $email;
    protected ?string $telefone;
    protected string  $uid;
    protected string  $pid;
    protected int     $perfil;
    protected string  $nivel;
    protected ?int    $secretaria;
    protected ?string $departamentos;
    protected int     $status;
    protected ?string $lastlogin;
    protected ?string $nowlogin;
    protected int     $passchange;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * set property nivel value by perfil
     *
     * @param int $perfil
     * @return void
     */
    public function buildNivel(int $perfil):void
    {
        $this->nivel = match($perfil){
            self::PRF_ADMIN   => implode(',', array_keys(self::getNivelArr())),
            self::PRF_GESTOR  => '0,1,2,3,4',
            self::PRF_DEPTO   => '0,2,3',
            default => '0'
        };
    }

    /**
     * Method return credentials to level access witn profile and level of access user
     *
     * @return array
     */
    public function getCredentials():array
    {
        return[
            'profile'    => $this->perfil,
            'level'      => Utils::toArray($this->nivel),
            'secretaria' => $this->secretaria,
            'deptos'     => Utils::toArray($this->departamentos),
        ];
    }

    /**
     * Build Access Nav to user by profile
     *
     * @return array
     */
    public function getAuthNav():array{
        return match($this->perfil){
            self::PRF_ADMIN,     
            self::PRF_GESTOR => [self::NVL_FOLHA, self::NVL_FUNCIONARIOS, self::NVL_ESTRUTURA, self::NVL_RPORTS],
            self::PRF_DEPTO  => [self::NVL_FOLHA, self::NVL_FUNCIONARIOS],
            default          => []
        };
    }


    /**
     * Return name of table in database reference entity
     * @override
     * @return string|null
     */
    public function getDataTableEntity(): ?string 
    {
        return ConnDB::TAB_USER;
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
        return ['nome', 'cpf', 'email', 'perfil', 'status'];
    }

    /**
     * Return array with status
     *
     * @return array
     */
    public static function getStatusArr():array
    {
        return [
            1 => 'Ativo',
            2 => 'Bloqueado'
        ];
    }

    /**
     * Return array with status colors
     *
     * @return array
     */
    public static function getStatusColorArr():array
    {
        return [
            1 => 'text-success',
            2 => 'text-danger'
        ];
    }

    /**
     * Return array with perfis availables
     *
     * @return array
     */
    public static function getPerfilArr():array
    {
        return [
            self::PRF_ADMIN  => 'Administrador Sistema',
            self::PRF_GESTOR => 'Gestor Municipal',
            self::PRF_DEPTO  => 'Chefe ou Diretor Escolar'
        ];
    }

    /**
     * Return array with niveis availables
     *
     * @return array
     */
    public static function getNivelArr():array
    {
        return [
            self::NVL_INICIAL      => 'Acesso Inicial',
            self::NVL_ESTRUTURA    => 'Controle de Secretaria e Departamentos',
            self::NVL_FUNCIONARIOS => 'Registro de Funcionarios',
            self::NVL_FOLHA        => 'Monitoramento Alteração de Folha',
            self::NVL_RPORTS       => 'Relatórios',
            self::NVL_BIGBOSS      => 'Administraçao Geral',
        ];
    }
}