<?php
namespace Octus\App\Utils;

use Octus\App\Model\EntityUsuario;
use Octus\App\Utils\Dates;

class Logs
{
    const LOG_PATH = __DIR__.'/../../exports/logs.txt';

    /**
     * Record log in file path defined in const
     *
     * @param string $log
     * @param EntityUsuario|null $user
     * @return void
     */
    public static function writeLog(string $log, ?EntityUsuario $user = null):void
    {
        //create strig record log
        $data     = Dates::getDateTimeNow();
        $browser  = $_SERVER['HTTP_USER_AGENT'];
        $userid   = $user != null ? $user->getAttr('id') : '';
        $username = $user != null ? $user->getAttr('nome') : '';
        $record   = $data.', '.$log.', '.$browser.', '.$userid.', '.$username.PHP_EOL;

        //rec log in file
        file_put_contents(self::LOG_PATH,$record,FILE_APPEND);
    }

    /**
     * Read log file defined in const
     *
     * @return bool|string
     */
    public static function readLog():bool|string
    {
        return file_get_contents(self::LOG_PATH);
    }
}