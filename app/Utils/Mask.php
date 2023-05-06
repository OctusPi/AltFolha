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
            $maskCPF = str_pad($maskCPF,11,'0', STR_PAD_LEFT);
            $maskCPF = substr_replace($maskCPF, '.', 4, 0);
            $maskCPF = substr_replace($maskCPF, '.', 8, 0);
            $maskCPF = substr_replace($maskCPF, '-', 12, 0);
            
        }
        return $maskCPF;
        
    }

    public static function maskVinculo(?string $vinculo):mixed
    {
        $vinculo = str_replace(' ', '',$vinculo);
        $clausure = $vinculo != null ? substr($vinculo, 0,1) : ''; 
        
        return match($clausure){
            'e','E' => 1,
            't','T' => 3,
            default => 2
        };
    }

    public static function maskCarga(?string $carga):mixed
    {
        $carga = str_replace(' ', '',$carga);
        $clausure = $carga != null ? substr($carga, 0,1) : ''; 
        
        return match($clausure){
            3,'3' => 3,
            1,'1' => 1,
            default => 2
        };
    }
    
}