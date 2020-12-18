<?php
namespace Sentia\Utils\payme;

use DateTime;

class PaymeData {
    // údaj je povinný, formát: 'V=n' ... momentálna verzia V=1,  číslo v intervale 1-9
    public string $version    = "1"; // momentálne jediná platná hodnota
    // údaj je povinný, formát: 'IBAN=AAnnnnnnnnnnnnnnnnnnnnnn'   24 znakov, RegEx pattern ^[A-Z]{2}[0-9]{22}$
    public string $iban       = ""; // údaj je povinný, formát: 'IBAN=AAnnnnnnnnnnnnnnnnnnnnnn'
    /*
     * formát: 'AM=nnnnnnn' alebo 'AM=nnnnnn.nn' oddeľovač je bodka
     * nulová hodnota 'AM=0' je povolená
     * attribute can be left blank in cases requested payment amount is not known, in such cases as donations
     */
    public string $amount     = "";
    /*
     * údaj je povinný (podľa štandardu je povinný, reálne nie je potrebný)
     * formát: 'CC=EUR', default hodnota 'CC=EUR', zatiaľ jediná podporovaná mena
     */
    public string $currency   = "EUR"; // momentálne jediná platná hodnota
    // údaj nie je povinný, formát: 'DD=YYYYMMDD'
    public DateTime $dueDate;
    // údaj nie je povinný, formát: 'PI=/VSnnnnnnnnnn/SSnnnnnnnnnn/KSnnn' => /VS{0,10}/SS{0,10}/KS{0,4}
    public string $varSymbol  = "";
    public string $specSymbol = "";
    public string $konSymbol  = "";
    // údaj nie je povinný, dĺžka max 140 znakov, formát: 'MSG=Thank+you+for+lunch'
    public string $message = "";
    // údaj nie je povinný, formát: 'CN=ALICE+PAYEE', dĺžka max 140 znakov, URL encoding
    public string $creditorsName = "";
}
