<?php
namespace Octus\App\Controller\Data;

use Octus\App\Model\Entity;
use Octus\App\Utils\Alerts;
use Octus\App\Controller\Data\Dao;

class ImpDao extends Dao
{
    public function __construct(Entity $entity)
    {
        parent::__construct($entity);
    }

    /**
     * Method write data in database after verify is data exists and if isOnly, return json 
     * with two objects, status | entity with info status and data entity respectively 
     * 
     * @param array $params
     * @return string
     */
    public function writeData():array
    {
        //verify is only in database
        $isOnly = $this->isOnly();

        if($isOnly){
            $write = $this->isExists() ? $this->daoUp() : $this->daoIn();
            return [
                'code'   => $write ? Alerts::STATUS_OK : Alerts::STATUS_WARNING,
                'status' => $write
            ];
        }else{
            return [
                'code'   => Alerts::STATUS_DUPLI,
                'status' => false
            ];
        }
    }

    /**
     * Method rescue register in database and return entity null or array
     *
     * @param array $params
     * @param bool $all
     * @param string $order
     * @param string $limit
     * @param string $mode
     * @return null|Entity|array
     */
    public function readData(array $params = [], bool $all = false, string $order = '', string $limit = '', string $mode = ' AND '):null|Entity|array
    {
        return $all ? 
        $this->daoGetAll($params, $order, $limit, $mode) : 
        $this->daoGetOne($params, $mode);
    }

    /**
     * Method rescue register in database with inner join tables and return entity null or array
     *
     * @param array $joins
     * @param array $params
     * @param string $order
     * @param string $limit
     * @param string $mode
     * @return array|null
     */
    public function readDataJoin(array $joins = [], array $params = [], string $order = '', string $limit = '', string $mode = ' AND '):?array
    {
        return $this->daoGetJoin($joins, $params, $order, $limit, $mode);
    }

    /**
     * execute delete and retur string json with status and data entity deleted
     *
     * @param null|array $params
     * @return array
     */
    public function delData(?array $params = null):array
    {

        //execute delete and retur string json with status and data entity deleted
        $delete = $this->daoDel($params);

        //return array two keys -> code exit and status request
        return[
            'code'   => $delete ? Alerts::STATUS_OK : Alerts::STATUS_WARNING,
            'status' => $delete ? 'Exclusão Realizada.' : 'Item referenciado em outras instancias'
        ];
    }
}
