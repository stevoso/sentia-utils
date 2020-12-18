<?php
namespace Sentia\Utils;

class HolidaysUtil {
    // fixne statne sviatky v tvare '24.12.' (pre pohyblive (velka noc) vid. IsFlexibleHoliday())
    private static array $fixedHolidays = ['01.01.', '06.01.', '01.05.', '08.05.', '05.07.', '29.08.', '01.09.', '15.09.', '01.11.', '17.11.', '24.12.', '25.12.', '26.12.'];

    public function isSaturdayOrSunday(\DateTime $theDate):bool{
        $s = $theDate->format('N'); // pondelok=1, nedela=7
        return $s == '6' || $s == '7';
    }

    public function isHoliday(\DateTime $theDate):bool {
        $s = $theDate->format('d.m.'); // '24.12.'
        return in_array($s, self::$fixedHolidays) || $this->isFlexibleHoliday($theDate);
    }

    private function isFlexibleHoliday(\DateTime $theDate):bool {
        //-- velka noc (velkonocna nedela)
        $year = $theDate->format('Y');
        $dx = easter_days($year);
        $easterSunday = new \DateTime($year."-03-21"); // vid. metodu easter_days()
        $easterSunday->modify("+$dx day");
        // velky piatok
        $easterFriday = clone($easterSunday);
        $easterFriday->modify('-2 day');
        // velkonocny pondelok
        $easterMonday = clone($easterSunday);
        $easterMonday->modify('+1 day');
        // test
        $s = $theDate->format('d.m.'); // '24.04.'
        return $s == $easterFriday->format('d.m.') || $s == $easterMonday->format('d.m.');
    }

    public function isDayOff(\DateTime $theDate):bool{
        return $this->isSaturdayOrSunday($theDate) || $this->isHoliday($theDate);
    }

}
