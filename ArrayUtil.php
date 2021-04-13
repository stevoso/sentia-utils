<?php
namespace Sentia\Utils;

use DOMDocument;
use DOMXPath;
use Exception;

class ArrayUtil {
    /**
     * Vymeni kluc a hodnotu ... kluc sa stane hodnotou a hodnota klucom
     */
    public function toggleKeyValue(array $keyToValue): array {
        return array_flip($keyToValue);
    }

    /**
     * prehodi 2 polozky podla kluca
     */
    public function swapByKeys($key1, $key2, $array): array {
        $newArray = [];
        foreach ($array as $key => $value) {
            if($key == $key1){
                $newArray[$key2] = $array[$key2];
            }elseif($key == $key2){
                $newArray[$key1] = $array[$key1];
            }else{
                $newArray[$key] = $value;
            }
        }
        return $newArray;
    }

    /**
     * na vystupe je pole, kde key=hodnoty zo vstupu a value=pocet hodnot v poli na vstupe
     */
    public function countValues(array $data): array {
        $result = [];
        foreach($data as $key => $value){
            if (array_key_exists($value, $result)){
                $result[$value] += 1;
            }else{
                $result[$value] = 1;
            }
        }
        asort($result);
        return $result;
    }

    //----- merge, unique -----
    public function mergeArrays(array $arrs): array {
        $ret = [];
        foreach($arrs as $arr){
            $ret = array_merge($ret, $arr);
        }
        return $ret;
    }

    /**
     * Spravi merge a potom unique.
     */
    public function mergeUniqueArrays(array $arrs): array {
        $ret = [];
        foreach($arrs as $arr){
            $ret = array_unique(array_merge($ret, $arr));
        }
        return $ret;
    }

    /**
     * pripoji jedno pole za druhe (nie merge!)
     * @param array $baseArray - pole, ku ktoremu sa pripaja ine pole.
     */
    public function appendArray(array &$baseArray, array $arrayToAppend){
        foreach($arrayToAppend as $item){
            $baseArray[] = $item;
        }
    }

    //----- get values -----
    /**
     *
     */
    public function getValuesFromArrays(array $arrs, $valueKey): array {
        $ret = [];
        foreach($arrs as $arr){
            $ret[] = $arr[$valueKey];
        }
        return $ret;
    }

    // --------------------------- get members --------------------------------
    /**
     * najprv sa pokusi zavolat $item->getMemberName() a ked sa nepodari, tak $item->memberName
     * Vrati objekt, kde konkretny parameter ma konkretnu hodnotu
     */
    public function getObjectFromObjectsByValue(array $objects, string $memberName, $memberValue){
        $ret = null;
        $method = "get" . ucwords($memberName);
        foreach($objects as $object){
            try{
                $value = $object->$method();
            }catch(Exception $e){
                $value = $object->$memberName;
            }
            if($value === $memberValue){
                return $object;
            }
        }
        return $ret;
    }

    /**
     * najprv sa pokusi zavolat $item->getMemberName() a ked sa nepodari, tak $item->memberName
     */
    public function getMembersFromObjects(array $objects, string $memberName): array {
        $ret = [];
        $method = "get" . ucwords($memberName);
        foreach($objects as $object){
            try{
                $value = $object->$method();
            }catch(Exception $e){
                $value = $object->$memberName;
            }
            $ret[] = $value;
        }
        return $ret;
    }

    /**
     * @deprecated [11.1.2021] Pouzi $this->getMembersFromObjects
     */
    public function getMembersFromItems(array $items, string $memberName): array {
        $xs = [];
        foreach($items as $item){
            $xs[] = $item->$memberName;
        }
        return $xs;
    }

    /**
     * @deprecated [11.1.2021] Pouzi $this->getMembersFromObjects
     */
    public function getMembersFromItems_eval(array $items, $memberName){
        $xs = [];
        foreach($items as $item){
             eval('$xs[] = $item->'.$memberName.';');
        }
        return $xs;
    }

    /**
     * najprv sa pokusi zavolat $item->getMemberName() a ked sa nepodari, tak $item->memberName
     */
    public function getKeyToValueFromObjects(array $objects, string $keyMember, string $valueMember): array {
        $ret = [];
        $methodKey = "get" . ucwords($keyMember);
        $methodValue = "get" . ucwords($valueMember);
        foreach($objects as $object){
            try{
                $key = $object->$methodKey();
            }catch(Exception $e){
                $key = $object->$keyMember;
            }

            try{
                $value = $object->$methodValue();
            }catch(Exception $e){
                $value = $object->$valueMember;
            }
            $ret[$key] = $value;
        }
        return $ret;
    }

    /**
     * @deprecated [11.1.2021] pouzit $this->getKeyToValueFromObjects
     */
    public function getKeyToValueFromItems(array $items, $keyMember, $valueMember): array{
        $xs = [];
        foreach($items as $item){
            $xs[$item->$keyMember] = $item->$valueMember;
        }
        return $xs;
    }

    /**
     * @deprecated [11.1.2021] pouzit $this->getKeyToValueFromObjects
     */
    public function getKeyToValueFromItems_eval(array $items, $keyMember, $valueMember): array {
        $xs = [];
        foreach($items as $item){
            eval('$xs[$item->'.$keyMember.'] = $item->'.$valueMember.';');
        }
        return $xs;
    }

    //---------------------------------------------------------------

    /**
     * vracia pole, kde polozky su napriklad: $x['key1']['key2'] = $value
     */
    public function getKey1Key2ToValueFromObjects(array $objects, string $key1Member, string $key2Member, string $valueMember): array {
        $ret = [];
        $methodKey1 = "get" . ucwords($key1Member);
        $methodKey2 = "get" . ucwords($key2Member);
        $methodValue = "get" . ucwords($valueMember);
        foreach($objects as $object){
            try{
                $key1 = $object->$methodKey1();
            }catch(Exception $e){
                $key1 = $object->$key1Member;
            }

            try{
                $key2 = $object->$methodKey2();
            }catch(Exception $e){
                $key2 = $object->$key2Member;
            }

            try{
                $value = $object->$methodValue();
            }catch(Exception $e){
                $value = $object->$valueMember;
            }
            $ret[$key1][$key2] = $value;
        }
        return $ret;
    }

    /**
     * vracia pole, kde polozky su napriklad: $x['key1']['key2'] = $value
     * @deprecated [12.1.2021] pouzi $this->getKey1Key2ToValueFromObjects
     */
    public function getKey1Key2ToValueFromItems_eval(array $items, $key1Member, $key2Member, $valueMember){
        $xs = array();
        foreach($items as $item){
            eval('$xs[$item->'.$key1Member.'][$item->'.$key2Member.'] = $item->'.$valueMember.';');
        }
        return $xs;
    }

    // --------------------------- key to item --------------------------------
    public function getKeyToObjectFromObjects(array $objects, string $keyMember): array {
        $ret = [];
        $methodKey = "get" . ucwords($keyMember);
        foreach($objects as $object){
            try{
                $key = $object->$methodKey();
            }catch(Exception $e){
                $key = $object->$keyMember;
            }
            $ret[$key] = $object;
        }
        return $ret;
    }

    /**
     * @deprecated [12.1.2021] pouzi $this->getKeyToObjectFromObjects
     */
    public function getKeyToItemByItems(array $items, $keyMember){
        $xs = array();
        foreach($items as $item){
            $xs[$item->$keyMember] = $item;
        }
        return $xs;
    }

    /**
     * @deprecated [12.1.2021] pouzi $this->getKeyToObjectFromObjects
     */
    public function getKeyToItemByItems_eval(array $items, $keyMember){
        $xs = array();
        foreach($items as $item){
            eval('$xs[$item->'.$keyMember.'] = $item;');
        }
        return $xs;
    }

    // --------------------------- ine --------------------------------
    /**
     * @param array $arrs - array(array(key=>value))
     * @param $keyKey
     * @param $valueKey
     * @return array - (key => array(value))
     */
    public function getKeyToValuesFromArrays(array $arrs, $keyKey, $valueKey): array {
        $ret = [];
        foreach($arrs as $arr){
            $key = $arr[$keyKey];
            if(!array_key_exists($key, $ret)){
                $ret[$key] = [];
            }
            $ret[$key][] = $arr[$valueKey];
        }
        return $ret;
    }

    public function getKeyToValueFromArrays(array $arrs, $keyKey, $valueKey): array {
        $ret = [];
        foreach($arrs as $arr){
            $ret[$arr[$keyKey]] = $arr[$valueKey];
        }
        return $ret;
    }

    /**
     * @deprecated [12.1.2021] toto asi aj zmazat...
     */
    public function getValueByKey(array &$keyToValue, $key){
        if(!key_exists($key, $keyToValue)){
            throw new Exception('Neexistuje polozka s key=\'' . $key . '\'!');
        }
        return $keyToValue[$key];
    }

    /**
     *
     * @param $keyToValue - array(keyOld => value)
     * @param $keyOldToNew - array(keyOld => keyNew)
     * @return array(keyNew => value)
     */
    public function changeKeys(array &$keyToValue, array &$keyOldToNew){
        $ret = [];
        foreach($keyToValue as $keyOld => $value){
            $ret[$keyOldToNew[$keyOld]] = $value;
        }
        return $ret;
    }

    /**
     * add value into array, if value not exists
     * @param $value
     * @param array $arr
     */
    public function addIfNotExist($value, array &$arr){
        if(!in_array($value, $arr)){
            $arr[] = $value;
        }
    }

    /**
     * remove value from array
     * @param $value
     * @param $arr
     */
    public function removeValue($value, &$arr){
        if(($key = array_search($value, $arr)) !== false){
            unset($arr[$key]);
        }
    }

    /**
     * Convert html string like this
     * <ul><li>First</li><li>Second</li></ul>' to array [0 => '<li>First</li>', 1 => '<li>Second</li>'] ... only on 1st level!
     * @deprecated [12.1.2021] nepouzivat... dat to inde a zmazat
     */
    public function htmlStringToTagArray(string $html, string $tag):array {
        $dom = new DOMDocument();
        $dom->loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>'.$html.'</body></html>');

        $xpath = new DOMXPath($dom);
        $elements = $xpath->query("/html/body/ul/li");

        $results = [];
        foreach($elements as $element){
            $results[] = $element->ownerDocument->saveHTML($element);
        }
        return is_array($results) && count($results) > 0 ? $results : [];
    }

    public function isArraysEqual(array $array1, array $array2): bool {
        if (count($array1) != count($array2)){
            return false;
        }
        foreach($array1 as $key => $value){
           if (array_key_exists($key, $array2)){
               if ($value != $array2[$key]){
                   return $value;
               }
           }
        }
        return true;
    }

    /**
     * simple key, value array converts to url query string
     * keys must be safe, without special characters
     */
    public function simpleArrayToUrlQueryString(array $simpleArray):string {
        $arr = [];
        foreach($simpleArray as $key => $value) {
            $arr[] = $key.'='.rawurlencode($value);
        }
        return implode('&', $arr);
    }

}
