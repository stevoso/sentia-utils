<?php
namespace Sentia\Utils\payme;

use Sentia\Utils\StringUtil;

class Payme {

    public const PAYME_SERVICE_URL = "https://payme.sk?";
    public const VERSION_MIN = 1;
    public const VERSION_MAX = 1;    // V budúcnosti maximalna verzia je 9 (formát podporuje jedno platné miesto)
    
    private StringUtil $stringUtils;

    public function __construct(StringUtil $stringUtils){
        $this->stringUtils = $stringUtils;
    }

    /**
     * cistenie udajov: medzery v IBAN, zaporna suma, desatinny oddelovac, medzery v Message, medzery v CreditorsName
     */
    public function sanitizePaymeInputData(PaymeData $paymeData):void {
        $paymeData->version = trim($paymeData->version);
        $paymeData->version = substr($paymeData->version,0,1);
        $paymeData->version = (string)((int)$paymeData->version);
        $paymeData->iban =  str_replace(' ', '', $paymeData->iban);
        $paymeData->iban =  strtoupper($paymeData->iban);
        $paymeData->amount = str_replace('-', '', $paymeData->amount);
        $paymeData->amount = str_replace(' ', '',  $paymeData->amount);
        $paymeData->amount = str_replace(',', '.', $paymeData->amount);
        $paymeData->amount = str_replace('O', '0', $paymeData->amount);
        $paymeData->amount = str_replace('o', '0', $paymeData->amount);
        if ( !$paymeData->amount ) {
            $paymeData->amount = '0';
        }
        // VS len číslice, 0 až 10 pozícií
        $paymeData->varSymbol = str_replace(" ", "", $paymeData->varSymbol);
        $paymeData->varSymbol = substr($paymeData->varSymbol,0,10);
        // SS len číslice, 0 až 10 pozícií
        $paymeData->specSymbol = str_replace(" ", "", $paymeData->specSymbol);
        $paymeData->specSymbol = substr($paymeData->specSymbol,0,10);
        // KS len číslice, 0 až 4 pozície
        $paymeData->konSymbol = str_replace(" ", "", $paymeData->konSymbol);
        $paymeData->konSymbol = substr($paymeData->konSymbol,0,4);

        $paymeData->message = trim($paymeData->message);
        $paymeData->message = substr($paymeData->message,0,140);
        $paymeData->message = $this->stringUtils->utf8ToAscii($paymeData->message);

        $paymeData->creditorsName = trim( $paymeData->creditorsName);
        $paymeData->creditorsName = substr($paymeData->creditorsName,0,70);
        $paymeData->creditorsName = $this->stringUtils->utf8ToAscii($paymeData->creditorsName);
    }

    /**
     * validacia
     */
    public function isPaymeLinkValid (PaymeData $paymeData):bool {
        $regexString = "/[". self::VERSION_MIN ."-". self::VERSION_MAX ."]/";
        if(1 !== preg_match($regexString, $paymeData->version)){
            return false;
        }

        $regexString = "/^[A-Z]{2}[0-9]{22}$/";
        if(1 !== preg_match($regexString, $paymeData->iban)){
            return false;
        }

        $regexString = "/^[0-9]{1,6}[.,]{0,1}[0-9]{0,2}$/";
        if(1 !== preg_match($regexString, $paymeData->amount)){
            return false;
        }

        $regexString = "/^[0-9]{0,10}$/";
        if(1 !== preg_match($regexString, $paymeData->varSymbol)){
            return false;
        }

        $regexString = "/^[0-9]{0,10}$/";
        if(1 !== preg_match($regexString, $paymeData->specSymbol) ) {
            return false;
        }

        $regexString = '/^[0-9]{0,4}$/';
        if (1 !== preg_match($regexString, $paymeData->konSymbol)) {
            return false;
        }

        $regexString = '/^[A-Za-z0-9\s\.+-]+$/';
        if (1 !== preg_match($regexString, $paymeData->message)) {
            return false;
        }        

        $regexString = '/^[A-Za-z0-9\s\.+-]+$/';
        if (1 !== preg_match($regexString, $paymeData->creditorsName)) {
            return false;
        }

        return true;
    }

    /**
     * sanitacia, validacia a generovanie Payme linku
     */
    public function generatePaymeLink(PaymeData $paymeData):string {
        $fullLink = "";
        $this->sanitizePaymeInputData($paymeData);

        if ($this->isPaymeLinkValid($paymeData)) {
            $fullLink  = self::PAYME_SERVICE_URL;
            $fullLink .=  "V=". $paymeData->version;
            $fullLink .=  "&IBAN=". $paymeData->iban;
            $fullLink .= "&AM=".( $paymeData->amount == "" ? "0" : $paymeData->amount);
            $fullLink .= "&CC=".$paymeData->currency;
            $fullLink .= $paymeData->dueDate === null ? "" : "&DT=".$paymeData->dueDate->format("Ymd");

            if ($paymeData->varSymbol != '' or $paymeData->specSymbol != '' or $paymeData->konSymbol != '') {
                $fullLink  .= "&PI=";
                $fullLink  .= "%2FVS". $paymeData->varSymbol;
                $fullLink  .= "%2FSS". $paymeData->specSymbol;
                $fullLink  .= "%2FKS". $paymeData->konSymbol;
            }

            $fullLink .= $paymeData->message == "" ? "" : "&MSG=". urlencode($paymeData->message);
            $fullLink .= $paymeData->creditorsName == "" ? "" : "&CN=".  urlencode($paymeData->creditorsName);
        }
        return $fullLink;
    }

    
}
