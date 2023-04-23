<?php
namespace Octus\App\Utils;
use Octus\App\Model\Entity;


class Utils
{
    /**
     * Method checks if array is nul and if key existis in array en return value of key
     *
     * @param int|string $key
     * @param array|null $dados
     * @return mixed
     */
    public static function at(null|int|string $key, ?array $dados):mixed
    {
        if($key != null && $dados != null){
            return array_key_exists($key, $dados) ? $dados[$key] : null;
        }else{
            return null;
        }
    }

    /**
     * Method checks if object Entity was defined and rescue attribute to return case not exists return null
     *
     * @param string|null $attr
     * @param Entity|null $object
     * @return mixed
     */
    public static function attr(?string $attr, ?Entity $object):mixed
    {
        return $object != null ? $object->getAttr($attr) : null;
    }

    /**
     * Method conevert sting to array using different pattern separator
     * @param string|null $data
     * @param string $pattern
     * @return array
     */
    public static function toArray(?string $data, string $pattern = ','):array
    {
        return $data != null ? explode($pattern, $data) : [];
    }

    /**
     * Method conevert array to string using different pattern separator
     * @param array|null $data
     * @param string $pattern
     * @return array
     */
    public static function toString(?array $data, string $pattern = ','):?string
    {
        return $data != null ? implode($pattern, $data) : null;
    }

    /**
     * Create a randon code to identify unique
     *
     * @return string
     */
    public static function code():string
    {
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $digits  = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $size = 16;
        $code = '';

        for ($i = 0; $i < $size; $i++) {
            $matriz = ($i > 0 && $i % rand(2, 5) === 0) ? $digits : $letters;
            $key    = rand(0, count($matriz)-1);
            $code  .= $matriz[$key];
        }

        return $code;
    }
}