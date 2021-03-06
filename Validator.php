<?php
namespace Sentia\Utils;

class Validator {

    const SLOVAK_PHONE_REGEX = '/^\+(421)(\d){9}$/';
    const NON_SLOVAK_PHONE_REGEX = '/^\+(\d){8,15}$/';

    public function isPhoneNumberIntl(?string $phoneNumber):bool{
        return !empty($phoneNumber) && ($this->isSlovakPhoneNumber($phoneNumber) || $this->isOtherPhoneNumber($phoneNumber));
    }

    public function isSlovakPhoneNumber(?string $phone):bool {
        return preg_match(self::SLOVAK_PHONE_REGEX, $phone);
    }

    public function isOtherPhoneNumber(?string $phone):bool {
        return preg_match(self::NON_SLOVAK_PHONE_REGEX, $phone);
    }

    public function isLatitude(?string $latitude): bool {
        return preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/', $latitude);
    }

    public function isLongitude(?string $longitude): bool {
        return preg_match('/^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/', $longitude);
    }
}
