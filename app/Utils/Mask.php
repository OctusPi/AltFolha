<?php
namespace Octus\App\Utils;

class Mask
{

    /**
     * Create path CPF to save in DB or View in Page
     *
     * @param string|null $cpf
     * @return string
     */
    public static function maskCPF(?string $cpf):string
    {
        $maskCPF  = '';
        if($cpf != null){
            $maskCPF = str_replace(['.', '-'], '', $cpf);
            $maskCPF = substr_replace($maskCPF, '.', 4, 0);
            $maskCPF = substr_replace($maskCPF, '.', 8, 0);
            $maskCPF = substr_replace($maskCPF, '-', 12, 0);
        }
        return $maskCPF;
        
    }

    public static function maskVinculo(?string $vinculo):mixed
    {
        $clausure = $vinculo != null ? substr($vinculo, 1, 1) : ''; 
        
        return match($clausure){
            'e','E' => 1,
            't','T' => 3,
            default => 2
        };
    }

    public static function maskCarga(?string $carga):mixed
    {
        $clausure = $carga != null ? substr($carga, 1, 1) : ''; 
        
        return match($clausure){
            3,'3' => 3,
            1,'1' => 1,
            default => 2
        };
    }
    
}