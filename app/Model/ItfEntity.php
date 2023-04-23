<?php
namespace Octus\App\Model;

interface ItfEntity {

    /**
     * Smart set value in property class
     *
     * @param string $attr
     * @param mixed $value
     * @return void
     */
    public function setAttr(string $attr, mixed $value):void;

    /**
     * Smart get value of property class
     *
     * @param string $attr
     * @return mixed
     */
    public function getAttr(string $attr):mixed;

    /**
     * Return array name propertys of class
     *
     * @return array
     */
    public function getPropsClass():array;

    /**
     * Return array with some values of class
     *
     * @param array $attrs
     * @return array
     */
    public function getSomeValuesClass(array $attrs):array;

    /**
     * Return array with values of class
     *
     * @return array
     */
    public function getValuesClass():array;

    /**
     * Return array key|value class propertys
     *
     * @return array
     */
    public function getPropsAndValues():array;
    
    /**
     * Method return array with exclusive properties to entity
     *
     * @return array
     */
    public function getExclusivePropsClass():array;

    /**
     * Return array with mandatory propertys of class
     *
     * @return array
     */
    public static function getObrPropsClass():array;

    /**
     * Return name of table in database reference entity
     *
     * @return string|null
     */
    public function getDataTableEntity(): ?string;

    /**
     * Feeds entities with parameters by origin
     *
     * @param array|null $params
     * @param string|null $origin
     * @return void
     */
    public function feedsEntity(?array $params):void;

}