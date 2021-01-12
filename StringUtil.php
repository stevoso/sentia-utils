<?php
namespace Sentia\Utils;

use Exception;

class StringUtil {
    /**
     * @deprecated [12.1.2021] tuto konstantu dat prec
     */
    const PASSWORD_STRENGTH_WARNING = "Heslo musí byť aspoň 8 znakov dlhé, musí obsahovať číslo a písmeno";

    public function toUpper(string $string):string{
        $s = mb_strtoupper($string);
        $s = strtr($s, "ľščťžýáíéňäúôüöĺŕřěďó", "ĽŠČŤŽÝÁÍÉŇÄÚÔÜÖĹŔŘĚĎÓ");
        return $s;
    }

    public function toLower(string $string):string{
        $s = mb_strtolower($string);
        $s = strtr($s, "ĽŠČŤŽÝÁÍÉŇÄÚÔÜÖĹŔŘĚĎÓ", "ľščťžýáíéňäúôüöĺŕřěďó");
        return $s;
    }

    /**
     * konvertuje CP1250 na UTF8
     */
    public function cp1250ToUtf8($string): ?string {
        try {
            if (($s = iconv('CP1250', 'UTF-8//TRANSLIT', $string)) === false){
                throw new Exception('Neda sa previest string ('.$string.') na utf8!');
            }
            return $s;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * konvertuje UTF na CP1250
     */
    public function utf8ToCp1250($string): ?string {
        $string = str_replace('ȍ', 'o', $string);
        if (($s = iconv('UTF-8', 'CP1250//TRANSLIT', $string)) === false){
            return null;
        }
        if (strlen($string) > 0 && strlen($s)==0){
            // ak nastane tato chyba - pravdepodobne niekto zadal do formulara znak z nemeckej/... klavesnice
            // potom by slo este pouzit //TRANSLIT//IGNORE, co sa pokusi skonvertovat, pripadne zignoruje problematicke znaky.
            return null;
        }
        return $s;
    }

    /**
     * string to Ascii (odstranenie diakritiky)
     */
    public function utf8ToAscii(string $string): string {
        //return iconv("UTF-8", "ASCII//TRANSLIT", $string);
        $prevodni_tabulka = [
            'ä'=>'a',
            'Ä'=>'A',
            'á'=>'a',
            'Á'=>'A',
            'à'=>'a',
            'À'=>'A',
            'ã'=>'a',
            'Ã'=>'A',
            'â'=>'a',
            'Â'=>'A',
            'č'=>'c',
            'Č'=>'C',
            'ć'=>'c',
            'Ć'=>'C',
            'ď'=>'d',
            'Ď'=>'D',
            'ě'=>'e',
            'Ě'=>'E',
            'é'=>'e',
            'É'=>'E',
            'ë'=>'e',
            'Ë'=>'E',
            'è'=>'e',
            'È'=>'E',
            'ê'=>'e',
            'Ê'=>'E',
            'í'=>'i',
            'Í'=>'I',
            'ï'=>'i',
            'Ï'=>'I',
            'ì'=>'i',
            'Ì'=>'I',
            'î'=>'i',
            'Î'=>'I',
            'ľ'=>'l',
            'Ľ'=>'L',
            'ĺ'=>'l',
            'Ĺ'=>'L',
            'ń'=>'n',
            'Ń'=>'N',
            'ň'=>'n',
            'Ň'=>'N',
            'ñ'=>'n',
            'Ñ'=>'N',
            'ó'=>'o',
            'Ó'=>'O',
            'ö'=>'o',
            'Ö'=>'O',
            'ô'=>'o',
            'Ô'=>'O',
            'ò'=>'o',
            'Ò'=>'O',
            'õ'=>'o',
            'Õ'=>'O',
            'ő'=>'o',
            'Ő'=>'O',
            'ř'=>'r',
            'Ř'=>'R',
            'ŕ'=>'r',
            'Ŕ'=>'R',
            'š'=>'s',
            'Š'=>'S',
            'ś'=>'s',
            'Ś'=>'S',
            'ť'=>'t',
            'Ť'=>'T',
            'ú'=>'u',
            'Ú'=>'U',
            'ů'=>'u',
            'Ů'=>'U',
            'ü'=>'u',
            'Ü'=>'U',
            'ù'=>'u',
            'Ù'=>'U',
            'ũ'=>'u',
            'Ũ'=>'U',
            'û'=>'u',
            'Û'=>'U',
            'ý'=>'y',
            'Ý'=>'Y',
            'ž'=>'z',
            'Ž'=>'Z',
            'ź'=>'z',
            'Ź'=>'Z'
        ];
        return strtr($string, $prevodni_tabulka);
    }

    /**
     * pred retazec doplni zvoleny znak na celkovu zvolenu dlzku
     * pred String prida Chars, aby vysledna dlzka bola LengthRequired (a vrati taky string).
     */
    public function prependChars(string $string, string $char, int $lengthRequired):string{
        return str_pad($string, $lengthRequired, $char, STR_PAD_LEFT);
    }

    /**
     * za retazec doplni zvoleny znak na celkovu zvolenu dlzku
     * pred String prida Chars, aby vysledna dlzka bola LengthRequired (a vrati taky string).
     */
    public function appendChars(string $string, string $char, int $lengthRequired):string{
        return str_pad($string, $lengthRequired, $char, STR_PAD_RIGHT);
    }

    /**
     * generuje nahodny retazec urcitej dlzky. Vdaka 'random_bytes' by mala byt funkcia vhodna aj na generovanie unikatnych tokenov
     */
    public function randomString(int $length):string{
        try{
            $bytes = random_bytes(round($length / 2));
        }catch(Exception $e){
            $bytes = 0;
        }
        return bin2hex($bytes);
    }

    /**
     * vracia zahashovane heslo vhodne zapisu do DB
     * @param $rawPassword - heslo v surovej forme (nehashovane)
     * @param $salt - doplnujuci retazec pre password
     * @param int $cost
     * @return bool|string
     */
    public function passwordHash($rawPassword, $salt, $cost = 12){
        $options = [
            'cost' => $cost,
        ];
        return password_hash($salt.$rawPassword, PASSWORD_BCRYPT, $options);
    }

    /**
     * vracia true, ak salt+heslo sa rovna hash-u
     * @param $rawStringToVerify - heslo v surovej forme (nehashovane), ktore sa ma porovnat s hashom
     */
    public function isPasswordVerified(string $rawStringToVerify, string $salt, string $hash): bool {
        return password_verify($salt.$rawStringToVerify, $hash);
    }

    /**
     * vracia true, ak heslo splna nizsie uvedene podmienky
     * @param $passwordRaw
     * @return bool
     * @deprecated [12.1.2021] Tato metoda by tu nemala byt v utiloch.
     */
    public function hasPasswordMinStrength($passwordRaw){
        return !(
            strlen($passwordRaw) < 8  // aspon 8 znakov
            || !preg_match("#[0-9]+#", $passwordRaw) // aspon jedno cislo
            || !preg_match("#[a-z]+#", $passwordRaw) // aspon jeden znak
            || !preg_match("#[A-Z]+#", $passwordRaw) // aspon jedno velke pismeno
        );
    }

    public function isEmail(string $string): bool {
        return filter_var($string, FILTER_VALIDATE_EMAIL);
    }

    public function toSafeNumber(?string $string, string $valueIfEmpty = '0'):?string{
        if($string === null){
            return null;
        }
        $string = str_replace([',', ' '], ['.', ''], $string);
        if(empty($string)){
            $string = $valueIfEmpty;
        }
        return $string;
    }
}
