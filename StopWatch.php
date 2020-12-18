<?php
namespace Sentia\Utils;

/**
 * PHP stopky, je mozne zapnut viac stopiek
 * Class StopWatch
 * @package Sentia\UtilsBundle\Lib
 */
class StopWatch {
    private array $startTimes = [];

    /**
     * StopWatch constructor.
     */
    public function __construct(){
    }

    /**
     * spustenie stopiek
     * @param string $timerName
     */
    public function start($timerName = 'default'){
        $this->startTimes[$timerName] = microtime(true);
    }

    /**
     * zastavenie stopiek, vracia ubehnuty cas od spustenia (v sekundach)
     * @param string $timerName
     * @param int $round - pocet desatinnych miest na zaokruhlovanie. null-ak nechceme zaokruhlit, ale ponechat plny pocet desatrinnych miest
     * @return mixed
     */
    public function stop($timerName = 'default', $round = 2){
        $elapsedTimeInSeconds = microtime(true) - $this->startTimes[$timerName];
        if($round !== null){
            $elapsedTimeInSeconds = round($elapsedTimeInSeconds, $round);
        }
        return $elapsedTimeInSeconds;
    }
}
