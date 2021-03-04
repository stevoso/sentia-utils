<?php
namespace Sentia\Utils;

class JsonUtil {

    public function arrayToJson(array $arr): string {
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    public function jsonToArray($json): array {
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    public function addToJson($json, $key, $value): string {
        $arr = [];
        if(!empty($json)){
            $arr = $this->jsonToArray($json);
        }
        $arr[$key] = $value;
        return $this->arrayToJson($arr);
    }

    public function getItemFromJson($json, $key){
        $arr = $this->jsonToArray($json);
        return (isset($arr[$key]) ? $arr[$key] : null);
    }

}
