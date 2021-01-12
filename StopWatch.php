<?php
namespace Sentia\Utils;

/**
 * PHP stopky, je mozne zapnut viac stopiek
 */
class StopWatch {
    private array $startTimes = [];

    /**
     * spustenie stopiek
     */
    public function start(string $timerName = 'default'): void {
        $this->startTimes[$timerName] = microtime(true);
    }

    /**
     * zastavenie stopiek, vracia ubehnuty cas od spustenia (v sekundach)
     * @param int $round - pocet desatinnych miest na zaokruhlovanie. null-ak nechceme zaokruhlit, ale ponechat plny pocet desatrinnych miest
     */
    public function stop(string $timerName = 'default', int $round = 2): int {
        $elapsedTimeInSeconds = microtime(true) - $this->startTimes[$timerName];
        if($round !== null){
            $elapsedTimeInSeconds = round($elapsedTimeInSeconds, $round);
        }
        return $elapsedTimeInSeconds;
    }
}
