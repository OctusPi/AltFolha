<?php
namespace Octus\App\Utils;
use DateTimeImmutable;


class Dates
{
    /**
     * Method return array months pt-br
     * @return string[]
     */
    public static function getMesesArr(): array
    {
        return [
            1=>'Janeiro',
            2=>'Fevereiro',
            3=>'Março',
            4=>'Abril',
            5=>'Maio',
            6=>'Junho',
            7=>'Julho',
            8=>'Agosto',
            9=>'Setembro',
            10=>'Outubro',
            11=>'Novembro',
            12=>'Dezembro'
        ];
    }

    /**
     * return long number time unix by dateUTC English format
     * @param $dataUtc
     * @return int
     */
    public static function getUnixByDate(?string $dataUtc): int
    {
        return $dataUtc != null ? strtotime($dataUtc) : 0;
    }

    /**
     * Method return date local Brazilian PT-BR
     * @return string
     */
    public static function getDateNow(): string
    {
        return date('d-m-Y');
    }

    /**
     * Method return date and time local Brazilian PT-BR
     * @return string
     */
    public static function getDateTimeNow(): string
    {
        return date('d-m-Y H:i:s');
    }

    /**
     * Method returns string extense date now local Brazilian PT-BR
     * @return string
     */
    public static function getExtDateNow(): string
    {
        return date('d').' de '.self::getMesesArr()[date('n')].' de '.date('Y');
    }

    /**
     * Method returns string extense date informated in local Brazilian PT-BR
     * @param $dataUtc //English format yyyy-mm-dd
     * @return string
     */
    public static function getExtDate(string $dataUtc): string
    {
        $unix = self::getUnixByDate($dataUtc);
        $d = date('d', $unix);
        $m = date('n', $unix);
        $y = date('Y', $unix);

        return $d.' de '.self::getMesesArr()[$m].' de '.$y;
    }

    /**
     * return mounth - year by utc date
     *
     * @param string|null $dataUtc
     * @return string|null
     */
    public static function getMonthYearByDate(?string $dataUtc): ?string
    {

        $unix = self::getUnixByDate($dataUtc);
        return $dataUtc != null ? date('n', $unix).'-'.date('Y', $unix) : null;

    }

    /**
     * Methor calc age in years
     * @param $dataNascimento
     * @return int|string
     */
    public static function ageCalculator($dataNascimento): int
    {
        $unixNasc  = self::getUnixByDate($dataNascimento);
        $unixAtual = self::getUnixByDate(self::getDateTimeNow());
        $unixIdade = $unixAtual - $unixNasc;

        return (int)date('Y', $unixIdade) - 1970;
    }

    /**
     * Calcule diff in days in two dates UTC format
     * @param string $dtorigin
     * @param string $dttarget
     * @return string
     */
    public static function diffDays(string $dtorigin, string $dttarget):int
    {
        $origin = new DateTimeImmutable($dtorigin);
        $target = new DateTimeImmutable($dttarget);
        $interval = $origin->diff($target);

        return $interval->invert ? - (int)$interval->days : (int)$interval->days;
    }

    /**
     * Method formatter date UTC English to Brazilian PT-BR
     * @param $dataUtc
     * @return string|null
     */
    public static function fmttDateView($dataUtc): ?string
    {
        return $dataUtc != null ? date('d/m/Y', strtotime($dataUtc)) : null;
    }

    /**
     * Method formatter datetime UTC English to Brazilian PT-BR
     * @param $dataUtc
     * @return string|null
     */
    public static function fmttDateTimeView($dataUtc):?string
    {
        return $dataUtc != null ? date('d/m/Y H:i:s', strtotime($dataUtc)) : null;
    }

    /**
     * Method formatter date UTC Brazilian PT-BR to DataBase UTC English
     * @param $dataLocal
     * @return string|null
     */
    public static function fmttDateDB($dataLocal):?string
    {
        return $dataLocal != null ? date('Y-m-d', strtotime(str_replace('/', '-', $dataLocal))) : null;
    }

    /**
     * Method formatter datetime UTC Brazilian PT-BR to DataBase UTC English
     * @param $dataLocal
     * @return string|null
     */
    public static function fmttDateTimeDB($dataLocal): ?string
    {
        return $dataLocal != null ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $dataLocal))) : null;
    }

    //validate dates
    public static function validDate(?string $dataUtc):bool
    {

        if($dataUtc != null)
        {
            $mount = date('n', self::getUnixByDate($dataUtc));
            $day   = date('j', self::getUnixByDate($dataUtc));
            $year  = date('Y', self::getUnixByDate($dataUtc));

            return checkdate($mount, $day, $year);
        }
        
        return false;

    }

    /**
     * Return list years by year start
     *
     * @param integer $start
     * @return array
     */
    public static function listYears(int $start = 2023):array
    {
        $years = [];
        while($start <= date('Y')){
            $years[$start] = $start;
            $start++;
        }

        return $years;
    }
}