<?php
namespace Sentia\Utils;

use DateTime;

class PersonUtil {
    private int $rcDay = 0;
    private int $rcMonth = 0;
    private int $rcYear = 0;

    /**
     * vracia true, ak je rodne cislo kompatibilne s datumom narodenia
     */
    public function isBirthDateCompatibleWithRc(?DateTime $birthDate, ?string $rc):bool {
        $isValidRc = false;
        if(null !== $birthDate && $this->isValidRC($rc)){
            $day = (int)$birthDate->format('d');
            $month = (int)$birthDate->format('m');
            $year = (int)$birthDate->format('Y');

            if(checkdate($month, $day, $year)) {
                if ($this->rcMonth == $month && $this->rcDay == $day && $this->rcYear == $year) {
                    $isValidRc = true;
                }
            }
        }
        return $isValidRc;
    }

    /**
     * vracia true, ak rodne cislo ma korektny format
     */
    public function isValidRC(?string $rc):bool {
        // "be liberal in what you receive"
        if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) {
            return false;
        }
        list(, $year, $month, $day, $ext, $c) = $matches;

        // do roku 1954 pridelovana devitimístna RC nelze overit
        if ($c === '') {
            if ($year < 54) {
                $year += 1900;

                if ($month > 70 && $year > 2003) $month -= 70;
                elseif ($month > 50) $month -= 50;
                elseif ($month > 20 && $year > 2003) $month -= 20;

                $this->rcDay = (int) $day;
                $this->rcMonth = (int) $month;
                $this->rcYear = (int) $year;
                return true;
            } else {
                return false;
            }
        }

        // kontrolní číslice
        $mod = ($year . $month . $day . $ext) % 11;
        if ($mod === 10) $mod = 0;
        if ($mod !== (int) $c) {
            return false;
        }

        // kontrola data
        $year += $year < 54 ? 2000 : 1900;

        // k měsíci může být připočteno 20, 50 nebo 70
        if ($month > 70 && $year > 2003) $month -= 70;
        elseif ($month > 50) $month -= 50;
        elseif ($month > 20 && $year > 2003) $month -= 20;

        if (!checkdate($month, $day, $year)) {
            return false;
        }
        $this->rcDay = (int)$day;
        $this->rcMonth = (int)$month;
        $this->rcYear = (int)$year;

        // cislo je OK
        return true;
    }

    /**
     * konvertuje rodne cislo na datum narodenia (objekt \DateTime) ak je rodne cislo chybne, vracia null
     */
    public function rcToDateTime($rc):?DateTime{
        $datetime = null;
        if($this->isValidRC($rc)){
            $datetime = DateTime::createFromFormat('Y-n-j', $this->rcYear.'-'.$this->rcMonth.'-'.$this->rcDay);
        }
        return $datetime;
    }
}
