<?php
namespace Octus\App\Utils;

class Money
{
    /**
     * Method convet money numeric float to string currency pt-br
     *
     * @param float|null $money
     * @return string|null
     */
    public static function getCurrency(?float $money):?string
    {
        return $money != null ? 'R$'.number_format($money, 2,',','.') : null;
    }

    /**
     * Method convert string currency to flot numeric
     *
     * @param string|null $money
     * @return float|null
     */
    public static function getFloat(?string $money):?float
    {
        $money = str_replace('R', '',  $money); //remove R string
        $money = str_replace('$', '',  $money); //remove $ string
        $money = str_replace(' ', '',  $money); //remove _ string
        $money = str_replace('.', '',  $money); //remove dot string
        $money = str_replace(',', '.', $money); //change string comma to dot string
    
        return $money != null ? floatval($money) : 0.0;
    }
    
}