<?php
namespace Sentia\Utils;

use DateTime;
use Exception;

class DateTimeUtil {

    /**
     * porovnava datumy - pouziva objekty DateTime, ale berie do uvahy len 'd m Y'
     * -1 .. $date1 < $date2
     *  0 .. $date1 = $date2
     *  1 .. $date1 < $date2
     */
    public function compareDates(DateTime $date1 = null, DateTime $date2 = null): int {
        $s1 = $date1 != null ? $date1->format('Y-m-d') : '';
        $s2 = $date2 != null ? $date2->format('Y-m-d') : '';
        return ($s1 < $s2) ? -1 : ($s1 == $s2 ? 0 : 1);
    }

    /**
     * porovnava datumy - berie do uvahy aj hodiny, minuty, sekundy
     * -1 .. $date1 < $date2
     *  0 .. $date1 = $date2
     *  1 .. $date1 < $date2
     */
    public function compareDateTimes(DateTime $dateTime1 = null, DateTime $dateTime2 = null): int {
        $s1 = $dateTime1 !== null ? $dateTime1->format('Y-m-d H:i:s') : '';
        $s2 = $dateTime2 !== null ? $dateTime2->format('Y-m-d H:i:s') : '';
        return ($s1 < $s2) ? -1 : ($s1 == $s2 ? 0 : 1);
    }

    /**
     * vracia vek podla datumu narodenia k dnesnemu dnu, alebo k nejaku inemu dnu
     * @param DateTime $birthDate
     * @param DateTime|null $toDate - datum, ku ktoremu pocitat vek
     */
    public function getAge(DateTime $birthDate = null, DateTime $toDate = null): ?int {
        $ret = null;
        if(null !== $birthDate){
            if($toDate === null){
                $toDate = new DateTime();
            }
            $ret = floor(($toDate->format("Ymd") - $birthDate->format("Ymd")) / 10000);
        }
        return $ret;
    }

    /**
     * skonvertuje string na DateTime, alebo null, ak nie je alebo je zle zadany.
     * Value moze byt napr. '1.1.2001', '01.01.2001', '31.12.2001', ' 31. 12 . 2001'
     */
    public function stringToDateTime($theValue): ?DateTime {
        if (strlen($theValue) < 8){ // minimum je 8 znakov - '1.1.2001'
            return null;
        }
        // oddelovac je '.'
        $ss = explode('.', $theValue);
        if (count($ss) != 3){
            return null;
        }
        $d = trim($ss[0]);
        $m = trim($ss[1]);
        $y = trim($ss[2]);
        if (!(is_numeric($d) && is_numeric($m) && is_numeric($y))){
            return null;
        }
        $d = (int)$d;
        $m = (int)$m;
        $y = (int)$y;
        if (!($d>=1 && $d<=31 && $m>=1 && $m<=12 && $y>=1900 && $y<=2200)){
            return null;
        }
        try{
            $d = new DateTime($y . '-' . ($m <= 9 ? '0' : '') . $m . '-' . ($d <= 9 ? '0' : '') . $d);
        }catch(Exception $e){

        }
        return $d;
    }

    /**
     * similar to DateTime.modify(), but this method modify result:
     * - if (31.08. +1 month) = 01.10. - from method modify(), but from this method will be result: 30.09.
     * - if (31.05. -1 month) = 01.05. - from method modify(), but from this method will be result: 30.04.
     * @param DateTime $theDateTime - this datetime will be modified
     * @param $thePeriod ('+1 month', '-1 week', '+2 year', ...)
     * @throws Exception
     */
    public function modifyAndAdaptMonth(DateTime $theDateTime, $thePeriod){
        // modify
        $date1 = clone($theDateTime);
        $theDateTime->modify($thePeriod);
        // zisti, ci ide o Year/Month
        $ss = explode(' ', $thePeriod);
        if (count($ss)!=2){
            throw new Exception('Perioda=\''.$thePeriod.'\' je v nespravnom formate!');
        }
        $s = strtolower($ss[1]);
        $isModified = false;
        if ($s=='month' || $s=='year' || $s=='week'){
            // check problematic days
            $d1 = (int)$date1->format('d');
            $d2 = (int)$theDateTime->format('d');
            if ($d1 != $d2){
                // pri pripocitavani moze nastat napr. 31.08. + 1month = 01.10., ale pre nas je spravna hodnota 30.09.
                // pri odpocitavani moze nastat napr. 31.05. - 1month = 01.05., ale pre nas je spravna hodnota 30.04.
                // cize v oboch pripadoch je potrebne odpocitat 1 den (alebo 2, alebo 3)
                $isModified = true;
                do{
                    if (!($d1>=29 && $d1<=31 && $d2>=1 && $d2<=3)){
                        throw new Exception('Nepodarilo sa DateTime("'.$theDateTime->format('d.m.Y').'").modify("'.$thePeriod.'")!');
                    }
                    $date1->modify('-1 day');
                    $theDateTime->modify('-1 day');
                    $d1 = (int)$date1->format('d');
                    $d2 = (int)$theDateTime->format('d');
                }while($d1!=$d2);
            }
        }

        if(isset($thePeriod[0]) && $thePeriod[0] == '+' && !$isModified){
            //echo 'ccc';
            $theDateTime->modify("-1 day");
        }
    }

    /**
     * vracia (true), ak je retazec platnym datumom (napr.: 14.5.2017), vracia (false) ak retazec nie je platny datum.
     */
    public function isDate(string $text): bool {
        $pieces = explode(".", $text);
        $den = isset($pieces[0]) ? $pieces[0] : "";
        $mesiac = isset($pieces[1]) ? $pieces[1] : "";
        $rok = isset($pieces[2]) ? $pieces[2] : "";

        return ((int)$den >= 1 && (int)$den <= 31 && (int)$mesiac >= 1 && (int)$mesiac <= 12 && (int)$rok >= 1901 && (int)$rok <= 3000);
    }

    /**
     * vracia rozdiel medzi dvoma datumami v dnoch
     */
    public function differenceDays(DateTime $datetime1, DateTime $datetime2): int {
        $interval = $datetime1->diff($datetime2);
        return (int)$interval->format('%a');
    }

    /**
     * creates new DateTime object modified by modifyString and timePart
     */
    public function newDateTimeFromNow(string $modifyString, ?string $timePart = null):DateTime{
        $dateTime = new DateTime();
        $dateTime->modify($modifyString);
        if($timePart !== null){
            $timeParts = explode(':', $timePart);
            $hours = isset($timeParts[0]) ? $timeParts[0] : 0;
            $minutes = isset($timeParts[1]) ? $timeParts[1] : 0;
            $seconds = isset($timeParts[2]) ? $timeParts[2] : 0;
            $dateTime->setTime($hours, $minutes, $seconds);
        }
        return $dateTime;
    }

    public function getHoursForForm(): array {
        $ret = [];
        for($i=0; $i<=23; $i++){
            $h = str_pad($i, 2, "0", STR_PAD_LEFT);
            $ret[$h] = $h;
        }
        return $ret;
    }

    public function getMinutesForForm(): array {
        $ret = [];
        for($i=0; $i<=59; $i++){
            $m = str_pad($i, 2, "0", STR_PAD_LEFT);
            $ret[$m] = $m;
        }
        return $ret;
    }

    /**
     * vracia pole rokov medzi datumami
     */
    public function getYearsFromInterval(DateTime $dateFrom, DateTime $dateTill): array {
        if($dateFrom->format("Y") === $dateTill->format("Y")){
            return [(int)$dateFrom->format("Y")];
        }
        $ret = [];
        $yearFrom = (int)$dateFrom->format("Y") < (int)$dateTill->format("Y") ? (int)$dateFrom->format("Y") : (int)$dateTill->format("Y");
        $yearTill = (int)$dateFrom->format("Y") > (int)$dateTill->format("Y") ? (int)$dateFrom->format("Y") : (int)$dateTill->format("Y");
        for($i=$yearFrom; $i<=$yearTill; $i++){
            $ret[] = $i;
        }
        return $ret;
    }

    /**
     * vracia DateTime vhodny na ukoncenie zmluvy.
     * Ak je na vstupe dnesny datum, tak sa vrati aj aktualna casova zlozka
     * Ak je na vstupe iny ako dnesny datum, tak casova zlozka bude 00:00:00
     */
    public function getForInsuranceStart(DateTime $dateTime): DateTime{
        if($dateTime->format("Y-m-d") === date("Y-m-d")){
            return new DateTime();
        }else{
            return new DateTime($dateTime->format("Y-m-d").'00:00:00');
        }
    }
}
