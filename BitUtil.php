<?php
namespace Sentia\Utils;

class BitUtil {

    /**
     * @param $value - int
     * @param $position - int - pozicia bitu (0,1,2,...30)
     * @return bool - true, ak je bit na Position nastaveny na 1.
     */
    public function isBit(int $value, int $position): bool {
        $x = 1 << (int)$position;
        return ($value & $x) != 0;
    }

    /**
     * @param $value - int
     * @param $position - int - pozicia bitu, ktory sa nastavi (0,1,2,...30)
     * @return int - vo Value nastavi na 1 bit na pozicii Position a vrati Value.
     */
    public function setBit(int $value, int $position): int {
        $x = 1 << (int)$position;
        return $value | $x;
    }

    /**
     * @param $value - int
     * @param $position - int - pozicia bitu, ktory sa nastavi (0,1,2,...30)
     * @return int - vo Value nastavi na 0 bit na pozicii Position a vrati Value.
     */
    public function unsetBit(int $value, int $position): int {
        $x = 1 << (int)$position;
        return $value & ~$x;
    }

    public function setBitValue(int $intValue, int $position, $bitValue){
        return $bitValue ? $this->setBit($intValue, $position) : $this->unsetBit($intValue, $position);
    }

    /**
     * Nastavi viac bitov naraz na 1 (vid.
     * SetBit()).
     * Pouzi napr. takto: SetBits(Value, Position1, Position2,...)
     */
    public function setBits(){
        $args = func_get_args();
        $count = count($args);
        if($count < 2){
            throw new \Exception('Malo argumentov!');
        }
        $value = $args[0];
        for($i = 1; $i < $count; $i++){
            $value = $this->setBit($value, $args[$i]);
        }
        return $value;
    }

    /**
     * Nastavi viac bitov naraz na 0 (vid.
     * SetBit()).
     * Pouzi napr. takto: SetBits(Value, Position1, Position2,...)
     */
    public function unsetBits(){
        $args = func_get_args();
        $count = count($args);
        if($count < 2){
            throw new \Exception('Malo argumentov!');
        }
        $value = $args[0];
        for($i = 1; $i < $count; $i++){
            $value = $this->unsetBit($value, $args[$i]);
        }
        return $value;
    }

    /**
     * Vrati int s nastavenymi bitmi podla Positions.
     * @param $positions - array(int - position)
     * @param $positionsAllowed - array(int - position) - povolene pozicie
     */
    public function setBitsByPositions(array &$positions, array &$positionsAllowed){
        $x = 0;
        foreach($positions as $position){
            if(!in_array($position, $positionsAllowed)){
                throw new \Exception('Nepovolena position=\'' . $position . '\'!');
            }
            $x = $this->setBit($x, $position);
        }
        return $x;
    }

    /**
     * Ako setBitsByPositions(), ale nekontroluje positionsAllowed.
     */
    public function setBitsByPositionsDoNotCheckAllowed(array $positions){
        $x = 0;
        foreach($positions as $position){
            $x = $this->setBit($x, $position);
        }
        return $x;
    }

    /**
     * Vrati array(int-position) s poziciami z Value.
     * @param $value - int - s nastavenymi bitmi
     * @param $positionsAllowed - array(int - position) - povolene pozicie
     */
    public function getPositions($value, array &$positionsAllowed){
        $ps = array();
        $x = 1;
        for($p = 0; $p <= 30; $p++){
            if($value < $x){
                break;
            }
            if(($value & $x) != 0){
                if(!in_array($p, $positionsAllowed)){
                    throw new \Exception('Nepovolena position=\'' . $p . '\'!');
                }
                $ps[] = $p;
            }
            $x = $x << 1;
        }
        return $ps;
    }

    /**
     * Ako GetPositions(), ale nekontroluje positionsAllowed.
     */
    public function getPositionsDoNotCheckAllowed($value){
        $ps = array();
        $x = 1;
        for($p = 0; $p <= 30; $p++){
            if($value < $x){
                break;
            }
            if(($value & $x) != 0){
                $ps[] = $p;
            }
            $x = $x << 1;
        }
        return $ps;
    }
}
