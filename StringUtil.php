<?php
namespace Sentia\Utils;

use Exception;

class StringUtil {
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
     * @param $theString
     * @return string
     * @throws Exception
     */
    public function cp1250ToUtf8($theString){
        try {
            if (($s = iconv('CP1250', 'UTF-8//TRANSLIT', $theString)) === false){
                throw new Exception('Neda sa previest string ('.$theString.') na utf8!');
            }
        } catch (Exception $e) {
            throw new Exception('Doslo k chybe v \'ToUtf8\'! Neda sa previest string ('.$theString.') na utf8!'. PHP_EOL. $e->getMessage(), NULL, $e);
        }
        return $s;
    }

    /**
     * konvertuje UTF na CP1250
     * @param $theString
     * @return string
     * @throws Exception
     */
    public function utf8ToCp1250($theString){
        $theString = str_replace('ȍ', 'o', $theString);
        if (($s = iconv('UTF-8', 'CP1250//TRANSLIT', $theString)) === false){
            throw new Exception('Neda sa previest string ('.$theString.') na cp1250!');
        }
        if (strlen($theString) > 0 && strlen($s)==0){
            throw new Exception('Neda sa previest retazec ('.$theString.') na cp1250 (skuste pouzit iba znaky z eng/svk klavesnice)!');
            // ak nastane tato chyba - pravdepodobne niekto zadal do formulara znak z nemeckej/... klavesnice
            // potom by slo este pouzit //TRANSLIT//IGNORE, co sa pokusi skonvertovat, pripadne zignoruje problematicke znaky.
        }
        return $s;
    }

    /**
     * string to Ascii (odstranenie diakritiky)
     * @param $string
     * @return string
     */
    public function utf8ToAscii($string){
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
     * @param $string
     * @param $char
     * @param $lengthRequired
     * @return string
     */
    public function appendChars($string, $char, $lengthRequired){
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
     * @param $salt
     * @param $hash
     * @return bool
     */
    public function isPasswordVerified($rawStringToVerify, $salt, $hash){
        return password_verify($salt.$rawStringToVerify, $hash);
    }

    /**
     * vracia true, ak heslo splna nizsie uvedene podmienky
     * @param $passwordRaw
     * @return bool
     */
    public function hasPasswordMinStrength($passwordRaw){
        return !(
            strlen($passwordRaw) < 8  // aspon 8 znakov
            || !preg_match("#[0-9]+#", $passwordRaw) // aspon jedno cislo
            || !preg_match("#[a-z]+#", $passwordRaw) // aspon jeden znak
            || !preg_match("#[A-Z]+#", $passwordRaw) // aspon jedno velke pismeno
        );
    }

    public function isEmail($string){
        return filter_var($string, FILTER_VALIDATE_EMAIL);
    }

    public function toSafeNumber(?string $string, $ifEmpty = 0):?string{
        if($string === null){
            return null;
        }
        $string = str_replace([',', ' '], ['.', ''], $string);
        if(empty($string)){
            $string = $ifEmpty;
        }
        return $string;
    }
}
