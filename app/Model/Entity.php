<?php
namespace Octus\App\Model;


abstract class Entity implements ItfEntity{

    /**
     * only indicator instance entity
     *
     * @var int
     */
    protected int $id;


    /**
     * Methid construct childrens of class
     */
    public function __construct()
    {
        $this->setAttr('id', 0);
    }
    
    /**
     * Smart set value in property class
     *
     * @param string $attr
     * @param mixed $value
     * @return void
     */
    public function setAttr(string $attr, mixed $value):void 
    {
        //make string reference method
        $ref = 'set'.ucfirst($attr);

        //verify if exist property and method to set value
        if(property_exists($this, $attr)){
            method_exists($this, $ref) ? $this->$ref($value) : $this->$attr = $value;
        }
    }

    /**
     * Smart get value of property class
     *
     * @param string $attr
     * @return mixed
     */
    public function getAttr(string $attr):mixed 
    {
        
        //mage string reference method
        $ref = 'get'.ucfirst($attr);

        //verify if exist property and method to set value
        if(property_exists($this, $attr)){
            if(isset($this->$attr)){
                return method_exists($this, $ref) ? $this->$ref() : $this->$attr;
            }else{
                return null;
            }
            
        }else{
            return null;
        }
        
    }

    /**
     * Return array name propertys of class
     *
     * @return array
     */
    public function getPropsClass():array 
    {
        return array_keys(get_class_vars(get_class($this)));
    }

    /**
     * Return array with some values of class
     *
     * @param array $attrs
     * @return array
     */
    public function getSomeValuesClass(array $attrs):array 
    {
        $exec = [];
        foreach ($attrs as $value) {
            $exec[$value] = $this->getAttr($value);
        }

        return $exec;
    }

    /**
     * Return array with values of class
     *
     * @return array
     */
    public function getValuesClass():array 
    {
        return array_values($this->getPropsAndValues());
    }

    /**
     * Return array key|value class propertys
     *
     * @return array
     */
    public function getPropsAndValues():array 
    {
        return get_object_vars($this);
    }

    /**
     * Method encoded properties and values of entity in json estructure
     *
     * @return string
     */
    public function getJsonPropsAndValues():string
    {
        return json_encode($this->getPropsAndValues());
    }

    /**
     * Return name of table in database reference entity
     *
     * @return string|null
     */
    public function getDataTableEntity(): ?string 
    {
        return null;
    }

    /**
     * Method return array with exclusive properties to entity
     *
     * @return array
     */
    public function getExclusivePropsClass():array
    {
        return [];
    }

    /**
     * Feeds entities with parameters by origin
     *
     * @param array|null $params
     * @param string|null $origin
     * @return void
     */
    public function feedsEntity(?array $params):void 
    {

        if($params != null){
            foreach ($params as $attr => $value) {
                $this->setAttr($attr, $value);
            }
        }

    }

    /**
     * Return array with mandatory propertys of class
     *
     * @return array
     */
    public static function getObrPropsClass():array 
    {
        return [];
    }
}