<?php
namespace Octus\App\Controller\Data;

use Octus\App\Model\Entity;

interface ItfDao
{

    /**
     * Method return entity initialized with data access object
     *
     * @return Entity
     */
    public function getEntity():Entity;

    /**
     * Method checks if entity exists in the database by its unique identifier
     *
     * @return bool
     */
    public function isExists():bool;

    /**
     * Checks is entity is single in data base with param search
     *
     * @param array $params | these parameters are to check if values ​​that cannot be replicated already exist in the database
     * @return bool
     */
    public function isOnly():bool;

    /**
     * Method insert entity in data base and return your self
     *
     * @param array $params  | these parameters are to check if values ​​that cannot be replicated already exist in the database
     * @return string|null
     */
    public function daoIn():bool;

    /**
     * Method update entity in data base with params and return your self
     *
     * @param array $params | these parameters are to check if values ​​that cannot be replicated already exist in the database
     * @return Entity|null
     */
    public function daoUp():bool;

    /**
     * Method delete entity in database
     *
     * @param array $params | params search entity to delete
     * @return bool
     */
    public function daoDel():bool;

    /**
     * method search and return one entity in table of database
     *
     * @param array $params | seach entity in table of database
     * @return Entity|null
     */
    public function daoGetOne(array $params = [], string $mode = ' AND '):?Entity;

    /**
     * method search and return so many entitys in table of database
     *
     * @param array $params | parameters of search 
     * @param string $order | order list values located
     * @param string $limit | limite lines rescue at a time
     * @param string $mode  | mode search params AND -- OR
     * @return array|null
     */
    public function daoGetAll(array $params = [], string $order = '', string $limit = '', string $mode = ' AND '):?array;

    /**
     * method search and return so many entitys in tables joined of database 
     *
     * @param array $joins  | tables to join
     * @param array $equals | compare equals forest keys in tables
     * @param array $params | parameters of search 
     * @param string $order | order list values located
     * @param string $limit | limite lines rescue at a time
     * @param string $mode  | mode search params AND -- OR
     * @return array|null
     */
    public function daoGetJoin(array $joins, array $params = [], string $order = '', string $limit = '', string $mode = ' AND '):?array;

    /**
     * Method return json with values to entity
     *
     * @return string
     */
    public function daoGetJson():string;
}