<?php
namespace Octus\App\Controller\Data;

use PDO;
use Octus\App\Utils\Logs;
use PDOException;

class ConnDB
{

    //defines tables in database
    const TAB_COMPN = 'tabaltf_company';
    const TAB_USER  = 'tabaltf_usuarios';
    const TAB_SECTR = 'tabaltf_secretarias';
    const TAB_DEPTS = 'tabaltf_departamentos';
    const TAB_FUNCS = 'tabaltf_funcionarios';
    const TAB_FOLHA = 'tabaltf_folhas';

    //defines params to create local connection
    private static string $conntype = 'mysql';
    private static string $connhost = 'database';
    private static string $connport = '3306';
    private static string $conndata = 'data_altfolha';
    private static string $connuser = 'root';
    private static string $connpass = 'tiger';


    /**
     * PDO instace connectio DB
     *
     * @var PDO|null
     */
    private static ?PDO $conn = null;

    /**
     * Return instace PDO conn
     *
     * @return PDO
     */
    public static function openConn(): PDO
    {
        if (self::$conn == null) {
            try {
                self::$conn = new PDO(
                        self::$conntype . ':host=' . self::$connhost . ';port=' . self::$connport . ';dbname=' . self::$conndata,
                        self::$connuser,
                        self::$connpass
                );

            } catch (PDOException $e) {
                Logs::writeLog('Erro: ' . $e->getMessage());
                die('Falha ao conectar com banco de dados...');
            }
        }

        return self::$conn;
    }

    /**
     * Close connectio unset PDO instance
     *
     * @return void
     */
    public static function closeConn(): void
    {
        if (self::$conn != null) {
            self::$conn = null;
        }
    }
}