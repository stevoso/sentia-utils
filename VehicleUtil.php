<?php
namespace Sentia\Utils;

class VehicleUtil {
    /**
     * vracia true, ak $vin ma korektny format: musi mat 17 znakov a nesmie obsahovat znaky I, O, Q
     */
    public function isVinValid(?string $vin):bool{
        if($vin === null){
            return false;
        }
        return (strlen($vin) == 17 && !strpos($vin, 'I') && !strpos($vin, 'O') && !strpos($vin, 'Q'));
    }

    /**
     * true, if valid ECV was entered. Otherwise false
     */
    public function isValidEcv(?string $ecv):bool{
        $ret = true;
        if($ecv === null || (mb_strlen($ecv) > 0 && mb_strlen($ecv) != 6 && mb_strlen($ecv) != 7)){
            $ret = false;
        }
        return $ret;
    }

    /**
     * true, if valid number of TP was entered. Otherwise false
     */
    public function isValidTp(?string $tp):bool{
        $ret = true;
        if($tp === null || (mb_strlen($tp) > 0 && mb_strlen($tp) != 8)){
            $ret = false;
        }
        return $ret;
    }

    public function getDimensionAndTypeOfTyreAxle(string $dimensionAndTypeOfTyreAxle): array {
        $res = [];
        if ($dimensionAndTypeOfTyreAxle == null || $dimensionAndTypeOfTyreAxle == '') {
            return $res;
        }
        $data = str_replace(['(', ')','*', ','], '', $dimensionAndTypeOfTyreAxle);
        $data = str_replace('  ', ' ', $data);
        $data = explode('/', $data);
        if (count($data) <= 1){
            $res['width'] = null;
            $res['height'] = null;
            $res['construction'] = null;
            $res['diameter'] = null;
            $res['diskCoefficient'] = null;
            $res['indexSpeed'] = null;
            $res['name'] = $dimensionAndTypeOfTyreAxle;
        }else{
            $res['width'] = (int) $data[0];
            $newData = explode(' ', $data[1]);
            $res['height'] = isset($newData[0]) ? (int) $newData[0] : null;
            $i = 1;
            $j = 1;
            if (isset($newData[1]) && strlen($newData[1]) == 1){
                $i += 1;
            }
            if (count($newData) == 2){
                $j -= 1;
                $i -= 1;
            }

            $res['construction'] = isset($newData[$j]) ?
                (substr($newData[$j], 2 - (2 * $j), 1)) : null;
            if ((count($newData) == 2) && (strlen($newData[0]) > 3)){
                $res['diameter'] = (int) substr($newData[0], 3, strlen($newData[0])- 3);
            }else{
                $res['diameter'] = isset($newData[$i]) ?
                    (int) substr($newData[$i], 2 - $i, strlen($newData[$i])- 0) : null;
            }
            if (count($data) > 2){
                $res['diskCoefficient'] = (isset($newData[$i + 1]) ?
                    $newData[$i + 1] : '') . '/' . substr($data[2], 0, strlen($data[2]) - 1);
                $res['indexSpeed'] = substr($data[2], strlen($data[2]) - 1, 1);
            }else{
                $res['diskCoefficient'] = isset($newData[$i + 1]) ?
                    substr($newData[$i + 1], 0, strlen($newData[$i + 1])- 1) : null;
                $res['indexSpeed'] = isset($newData[$i + 1]) ?
                    substr($newData[$i + 1], strlen($newData[$i + 1])- 1, 1) : null;
            }

            $res['name'] = $dimensionAndTypeOfTyreAxle;
        }
        return $res;
    }

    public function getRimDimensionsOnAxle(string $rimDimensionsOnAxle): array {
        $res = [];
        if ($rimDimensionsOnAxle == null || $rimDimensionsOnAxle == '') {
            return $res;
        }
        $formattedString = str_replace([' ', 'X', '(', ')'], '', $rimDimensionsOnAxle);
        $formattedString = str_replace(',', '.', $formattedString);
        $formattedString = str_replace('1/2', '.5', $formattedString);
        $rimEdgeShapes = ['J', 'A', 'B', 'D', 'E', 'F', 'G', 'H', 'P', 'K', 'S', 'T', 'V', 'W', '-'];
        $digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $flag = true;
        for($i=0;($i < strlen($formattedString)) && $flag; $i++){
            if (in_array($formattedString[$i],$rimEdgeShapes)){
                if ($i > 0 ){
                    $res['width'] = ((float)mb_substr($formattedString, 0, $i) * 100) / 100;
                }
                $res['rimEdgeShape'] = $formattedString[$i];
                $flag = false;
            }
        }
        $newString = mb_substr($formattedString, $i, strlen($formattedString) - $i );
        if (!$flag){
            $flagForSecondParams = true;
            for($j = 0; ($j < strlen($newString)) && $flagForSecondParams; $j++){
                if (!in_array($newString[$j], $digits)){
                    $res['diskDiameter'] = (int) mb_substr($newString, 0, $j);
                    $flagForSecondParams = false;
                }
            }
            $flagForSecondParams = true;
            for($j = 0; ($j < strlen($newString)) && $flagForSecondParams; $j++){
                if ($newString[$j]== 'E'){
                    $res['offset'] =  str_replace('=', 'T',
                        mb_substr($newString, $j, strlen($newString) -$j));
                    $res['offset'] = str_replace([ '.'], '', $res['offset']);
                    $flagForSecondParams = false;
                }
            }
        }
        $res['width'] = isset($res['width']) ? $res['width'] : null;
        $res['rimEdgeShape'] = isset($res['rimEdgeShape']) ? $res['rimEdgeShape'] : null;
        $res['diskDiameter'] = isset($res['diskDiameter']) ? $res['diskDiameter'] : null;
        $res['offset'] = isset($res['offset']) ? $res['offset'] : null;
        $res['name'] = $rimDimensionsOnAxle;
        return $res;
    }
}
