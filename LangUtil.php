<?php
namespace Sentia\Utils;

class LangUtil {
    private array $langCodeShortToLong = [
        'sk' => 'sk_SK',
        'cs' => 'cs_CZ',
        'en' => 'en_US'
    ];

    /**
     * vracia pole locations, kde hodnotou je percentualne hodnota (koeficient) klucom je v tvare x_y kde:
     * x je language code podla ISO 639-1 (https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)
     * y je kod podla ISO 3166-1 alpha 2 (https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes)
     * pole je zaradene od najvyssieho koeficientu
     */
    public function getLanguageCodesByBrowser(): array {
        $retArray = [];
        $langs = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach($langs as $lang){
            $tmp = explode(";", $lang);
            $codelang = $tmp[0];
            $quoficient = isset($tmp[1]) ? $tmp[1] : 1;
            if(mb_strlen($codelang) < 4 && array_key_exists($codelang, $this->langCodeShortToLong)){
                $codelang = $this->langCodeShortToLong[$codelang];
            }else{
                continue;
            }
            $retArray[$codelang] = $quoficient;
        }
        return $retArray;
    }

}
