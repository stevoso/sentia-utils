<?php
namespace Sentia\Utils;

use DOMDocument;
use DOMXPath;
use Exception;

class ArrayUtil {
    /**
     * Vymeni kluc a hodnotu ... kluc sa stane hodnotou a hodnota klucom
     * @param array $keyToValue
     * @return array
     * @throws Exception
     */
    public function toggleKeyValue(array $keyToValue){
        $xs = array();
        foreach($keyToValue as $key => $value){
            if(array_key_exists($value, $keyToValue)){
                throw new Exception('Key=\'' . $value . '\' uz je v array!');
            }
            $xs[$value] = $key;
        }
        return $xs;
    }

    /**
     * prehodi 2 polozky podla kluca
     * @param $key1
     * @param $key2
     * @param $array
     * @return array
     */
    public function swapByKeys($key1, $key2, $array){
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

    public function countValues(array $data){
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
    public function mergeArrays(array $arrs){
        $xs = array();
        foreach($arrs as $arr){
            $xs = array_merge($xs, $arr);
        }
        return $xs;
    }

    /**
     * Spravi merge a potom unique.
     * @param array $arrs
     * @return array
     */
    public function mergeUniqueArrays(array $arrs){
        $xs = array();
        foreach($arrs as $arr){
            $xs = array_unique(array_merge($xs, $arr));
        }
        return $xs;
    }

    /**
     * pripoji jedno pole za druhe (nie merge!)
     * @param array $baseArray - pole, ku ktoremu sa pripaja ine pole.
     * @param array $arrayToAppend
     */
    public function appendArray(array &$baseArray, array $arrayToAppend){
        foreach($arrayToAppend as $item){
            $baseArray[] = $item;
        }
    }

    //----- get values -----
    /**
     * @param array $arrs
     * @param $valueKey
     * @return array
     */
    public function getValuesFromArrays(array $arrs, $valueKey){
        $xs = array();
        foreach($arrs as $arr){
            $xs[] = $arr[$valueKey];
        }
        return $xs;
    }

    // --------------------------- get members --------------------------------
    public function getItemByPropertyValue_eval(array $items, $property){

    }

    public function getMembersFromItems(array $items, $memberName){
        $xs = array();
        foreach($items as $item){
            $xs[] = $item->$memberName;
        }
        return $xs;
    }

    public function getMembersFromItems_eval(array $items, $memberName){
        $xs = array();
        foreach($items as $item){
             eval('$xs[] = $item->'.$memberName.';');
        }
        return $xs;
    }

    public function getKeyToValueFromItems(array $items, $keyMember, $valueMember): array{
        $xs = [];
        foreach($items as $item){
            $xs[$item->$keyMember] = $item->$valueMember;
        }
        return $xs;
    }

    public function getKeyToValueFromItems_eval(array $items, $keyMember, $valueMember): array {
        $xs = [];
        foreach($items as $item){
            eval('$xs[$item->'.$keyMember.'] = $item->'.$valueMember.';');
        }
        return $xs;
    }

    /**
     * vracia pole, kde polozky su napriklad: $x['key1']['key2'] = $value
     * @param array $items
     * @param $key1Member
     * @param $key2Member
     * @param $valueMember
     * @return array
     */
    public function getKey1Key2ToValueFromItems_eval(array $items, $key1Member, $key2Member, $valueMember){
        $xs = array();
        foreach($items as $item){
            eval('$xs[$item->'.$key1Member.'][$item->'.$key2Member.'] = $item->'.$valueMember.';');
        }
        return $xs;
    }

    // --------------------------- key to item --------------------------------
    public function getKeyToItemByItems(array $items, $keyMember){
        $xs = array();
        foreach($items as $item){
            $xs[$item->$keyMember] = $item;
        }
        return $xs;
    }

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
    public function getKeyToValuesFromArrays(array $arrs, $keyKey, $valueKey){
        $xs = array();
        foreach($arrs as $arr){
            $key = $arr[$keyKey];
            if(!array_key_exists($key, $xs)){
                $xs[$key] = array();
            }
            $xs[$key][] = $arr[$valueKey];
        }
        return $xs;
    }

    public function getKeyToValueFromArrays(array $arrs, $keyKey, $valueKey){
        $xs = array();
        foreach($arrs as $arr){
            $xs[$arr[$keyKey]] = $arr[$valueKey];
        }
        return $xs;
    }

    /**
     * @param array $keyToValue
     * @param $key
     * @return mixed
     * @throws Exception
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
        $xs = array();
        foreach($keyToValue as $keyOld => $value){
            $xs[$keyOldToNew[$keyOld]] = $value;
        }
        return $xs;
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

    // Convert html string like this
    // '<ul><li>First</li><li>Second</li></ul>' to array [0 => '<li>First</li>', 1 => '<li>Second</li>'] ... only on 1st level!
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

    public function isArraysEqual(array $array1, array $array2){
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
