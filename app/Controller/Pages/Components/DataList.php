<?php
namespace Octus\App\Controller\Pages\Components;

use Octus\App\Controller\Data\FactoryDao;
use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Security;
use Octus\App\Utils\Utils;

class DataList
{

    /**
     * Data list generic entity
     * @param string $molde
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function list(string $molde, string $key = 'id', string $value = 'nome'):array
    {
        $facDAO = (new FactoryDao())->$molde();
        $getDAO = $facDAO->readData(all:true, order:$value);
        $lstDAO = [];
        
        //feed list
        foreach ($getDAO as $item) {
            $lstDAO[$item[$key]] = $item[$value];
        }

        return $lstDAO;
    }

    public static function listArr(string $molde, string $key = 'id', array $values = [], $order = ''):array
    {
        $facDAO = (new FactoryDao())->$molde();
        $getDAO = $facDAO->readData(all: true, order:$order);
        $lstDAO = [];

        //feed list
        foreach ($getDAO as $item){
            $concat = '';
            foreach($values as $value){ $concat .= $item[$value].' ';}
            $lstDAO[$item[$key]] = $concat;
        }

        return $lstDAO;
    }

    public static function listSecretarias(?EntityUsuario $user):array
    {
        $facDAO = (new FactoryDao())->daoSecretaria();
        $getDAO = $facDAO->readData(all:true, order:'secretaria');
        $lstDAO = [];

        //feed list
        foreach ($getDAO as $item){
            if(Security::isAuthList($user, $item['id'])){
                $lstDAO[$item['id']] = $item['secretaria'];
            }
        }

        return $lstDAO;
    }

    public static function listDepartamentos(?EntityUsuario $user):array
    {
        $facDAO = (new FactoryDao())->daoDepartamento();
        $getDAO = $facDAO->readData(all:true, order:'departamento');
        $lstDAO = [];

        //feed list
        foreach ($getDAO as $item){
            if(Security::isAuthList($user, $item['secretaria'], $item['id'])){
                $lstDAO[$item['id']] = $item['departamento'];
            }
        }

        return $lstDAO;
    }
}