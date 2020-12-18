<?php
namespace Sentia\Utils;

class JsonUtil {
    /**
     * @param array $arr
     * @return string
     */
    public function arrayToJson(array $arr){
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $json
     * @return mixed
     */
    public function jsonToArray($json){
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    /**
     * @param $json
     * @param $key
     * @param $value
     * @return string
     */
    public function addToJson($json, $key, $value){
        $arr = [];
        if(!empty($json)){
            $arr = $this->jsonToArray($json);
        }
        $arr[$key] = $value;
        return $this->arrayToJson($arr);
    }

    /**
     * @param $json
     * @param $key
     * @return null
     */
    public function getItemFromJson($json, $key){
        $arr = $this->jsonToArray($json);
        return (isset($arr[$key]) ? $arr[$key] : null);
    }

}
