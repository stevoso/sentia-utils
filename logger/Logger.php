<?php
namespace Sentia\Utils\logger;

use DateTime;
use Exception;
use Sentia\Utils\DateTimeUtil;
use Sentia\Utils\FileUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;

class Logger {
    private FileUtil $fileUtil;
    private RequestStack $requestStack;
    private DateTimeUtil $dateTimeUtil;

    public function __construct(RequestStack $requestStack, FileUtil $fileUtil, DateTimeUtil $dateTimeUtil){
        $this->fileUtil = $fileUtil;
        $this->requestStack = $requestStack;
        $this->dateTimeUtil = $dateTimeUtil;
    }

    // ### log by date ###
    public function addLineByDate(string $basePath, Uuid $uuid, string $message, int $type = LogItem::TYPE_DEFAULT): void {
        $this->logByDate($message, $uuid, LogItem::LINE_ONE, $type, $basePath);
    }

    public function addLinesByDate(string $basePath, Uuid $uuid, string $message, int $type = LogItem::TYPE_DEFAULT): void {
        $this->logByDate($message, $uuid, LogItem::LINE_MULTI, $type, $basePath);
    }

    private function logByDate(string $message, Uuid $uuid, int $lineType, int $type, string $basePath): void{
        $logItem = $this->prepareLogItem($message, $lineType, $type, $basePath);
        // prepare subdirs by Uuid
        $finalPath = $this->fileUtil->createDirsForUuid4($basePath, $uuid->toRfc4122());
        $finalPath .= '/'.$logItem->dateTime->format('Y');
        if(!is_dir($finalPath)){
            $this->fileUtil->createDir($finalPath);
        }
        $finalPath .= '/'.$logItem->dateTime->format("m-d").'.log';
        file_put_contents($finalPath, $logItem->generateLogItem(), FILE_APPEND | LOCK_EX);
    }

    // ### log by UUID ###
    public function addLineByUuid(string $basePath, Uuid $uuid, string $message, int $type = LogItem::TYPE_DEFAULT): void {
        $this->logByUuid($message, $uuid, LogItem::LINE_ONE, $type, $basePath);
    }

    public function addLinesByUuid(string $basePath, Uuid $uuid, string $message, int $type = LogItem::TYPE_DEFAULT): void {
        $this->logByUuid($message, $uuid, LogItem::LINE_MULTI, $type, $basePath);
    }

    private function logByUuid(string $message, Uuid $uuid, int $lineType, int $type, string $basePath): void{
        $logItem = $this->prepareLogItem($message, $lineType, $type, $basePath);
        // prepare subdirs by Uuid
        $finalPath = $this->fileUtil->createDirsForUuid4($basePath, $uuid->toRfc4122(), false);
        $finalPath .= '/'.$uuid->toRfc4122().'.log';
        file_put_contents($finalPath, $logItem->generateLogItem(), FILE_APPEND | LOCK_EX);
    }

    private function prepareLogItem(string $message, int $lineType, int $type, string $basePath):LogItem{
        $logItem = new LogItem();
        $logItem->message = $message;
        $logItem->lineType = $lineType;
        $logItem->dateTime = new DateTime();
        $logItem->ip = (string)$this->requestStack->getCurrentRequest()->getClientIp();
        $logItem->type = $type;
        // check basePath existence ... must exists
        if(!is_dir($basePath)){
            $logItem->type = LogItem::TYPE_ERROR;
            $logItem->lineType = LogItem::LINE_ONE;
            $logItem->message = 'Base path neexistuje: '.$basePath;
        }
        return $logItem;
    }

    // ### GET methods ###
    /**
     * ziskanie zaznamov z casozbernych logov
     * @throws Exception
     * @return LogItem[]
     */
    public function getLogsByDate(string $basePath, Uuid $uuid, DateTime $from, DateTime $till): array {
        if(!is_dir($basePath)){
            throw new Exception('Base path neexistuje:'.$basePath);
        }

        $finalPath = $this->fileUtil->getPathByUuid($basePath, $uuid->toRfc4122());
        if(!is_dir($finalPath)){
            throw new Exception('Cesta k adresáru neexistuje:'.$finalPath);
        }

        $ret = [];
        $years = $this->dateTimeUtil->getYearsFromInterval($from, $till);
        // prechadzame vsetkymi vyhovujucimi rokmi
        foreach($years as $year){
            $files = $this->fileUtil->getFiles($finalPath.'/'.$year);
            foreach($files as $file){
                // citame len subory vyhovujuce datumovemu intervalu
                if($file >= $from->format("m-d").'.log' && $file <= $till->format("m-d").'.log'){
                    $this->getLogItemsFromOneFile($ret, $finalPath.'/'.$year.'/'.$file, $from, $till);
                }
            }
        }
        return $ret;
    }

    /**
     * ziskanie zaznamov zo sekvencnych logov
     * @return LogItem[]
     * @throws Exception
     */
    public function getLogsByUuid(string $basePath, Uuid $uuid, DateTime $from, DateTime $till): array {
        if(!is_dir($basePath)){
            throw new Exception('Base path neexistuje:'.$basePath);
        }

        $finalPath = $this->fileUtil->getPathByUuid($basePath, $uuid->toRfc4122(), false).'/'.$uuid->toRfc4122().'.log';
        if(!file_exists($finalPath)){
            throw new Exception('Cesta k súboru neexistuje:'.$finalPath);
        }

        $ret = [];
        $this->getLogItemsFromOneFile($ret, $finalPath, $from, $till);
        return $ret;
    }

    private function getLogItemsFromOneFile(array &$ret, string $pathToFile, DateTime $from, DateTime $till): void {
        $handle = fopen($pathToFile, "r");
        if ($handle) {
            $logMultiline = null; // toto je bud null alebo objekt LogItem reprezentujuci multiline log zaznam
            while (($line = fgets($handle)) !== false) {
                if(LogItem::isLogItemFormat($line)){
                    // existuje neulozeny multiline log zaznam, tak ho ulozit do pola a pokracujeme
                    if($logMultiline !== null){
                        $ret[] = $logMultiline;
                        $logMultiline = null;
                    }

                    // spracujeme aktualne nacitany riadok zo suboru
                    $logItem = LogItem::createFromString($line);
                    if($logItem->dateTime->format("Y-m-d H:i:s") >= $from->format("Y-m-d H:i:s")
                        && $logItem->dateTime->format("Y-m-d H:i:s") <= $till->format("Y-m-d H:i:s"))
                    {
                        if($logItem->lineType === LogItem::LINE_MULTI){
                            $logMultiline = clone $logItem;
                        }else{
                            $ret[] = $logItem;
                        }
                    }
                }else{
                    if($logMultiline !== null){
                        $logMultiline->message .= "\n".$line;
                    }
                }
            }
            fclose($handle);

            if($logMultiline !== null){
                $ret[] = $logMultiline;
            }
        }
    }

    /**
     * vymazanie celeho adresara alebo suboru identifikovaneho podla $uuid
     */
    public function deleteLog(string $basePath, Uuid $uuid): void {
        $baseDir = $this->fileUtil->getPathByUuid($basePath, $uuid->toRfc4122(), false);
        if(file_exists($baseDir.'/'.$uuid->toRfc4122().'.log')){
            $this->fileUtil->delete($baseDir.'/'.$uuid->toRfc4122().'.log');
        }

        if(is_dir($baseDir.'/'.$uuid->toRfc4122())){
            $this->fileUtil->deleteDir($baseDir.'/'.$uuid->toRfc4122());
        }

        $this->fileUtil->clearUuid4Dirs($basePath, $uuid->toRfc4122());
    }
}
