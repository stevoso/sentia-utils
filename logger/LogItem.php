<?php
namespace Sentia\Utils\logger;

use DateTime;

class LogItem {
    const LINE_ONE = 1;
    const LINE_MULTI = 2;

    const TYPE_DEFAULT = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;
    const TYPE_SYSTEM_ERROR = 4; // pri tejto chybe by mohol ist napriklad email, aby sme vedeli o tejto chybe co najskor

    private static array $colors = [
        self::TYPE_DEFAULT => '#000000',
        self::TYPE_WARNING => '#f7aa04',
        self::TYPE_ERROR => '#db0a0a',
        self::TYPE_SYSTEM_ERROR => '#bb029a',
    ];

    public int $lineType;
    public int $type;
    public string $ip;
    public string $title;
    public ?DateTime $dateTime;
    public ?string $message = '';

    public function toArray(): array {
        return [
            'lineType' => $this->lineType,
            'type' => $this->type,
            'ip' => $this->ip,
            'title' => $this->title,
            'dateTime' => $this->dateTime->format("Y-m-d H:i:s"),
            'message' => $this->message
        ];
    }

    /**
     * generuje retazec vhodny na zapis do suboru
     */
    public function generateLogItem(): string {
        $ret = $this->lineType.'|'.$this->type.'|'.$this->dateTime->format("Y-m-d H:i:s").'|'.$this->ip.'|'.$this->title;
        if($this->lineType === self::LINE_ONE){
            $ret .= '|'.$this->message."\n";
        }else{
            $ret .= "\n".$this->message."\n";
        }
        return $ret;
    }

    public function getColor():string{
        return self::$colors[$this->type];
    }

    /**
     * vytvori tento objekt z retazca (v pripade multiline message sa musi nacitat dodatocne)
     */
    public static function createFromString(string $line): LogItem{
        $arr = explode('|', $line);
        $logItem = new LogItem();
        $logItem->lineType = isset($arr[0]) ? (int)$arr[0] : 0;
        $logItem->type = isset($arr[1]) ? (int)$arr[1] : 0;
        $logItem->dateTime = isset($arr[2]) ? new DateTime($arr[2]) : null;
        $logItem->ip = isset($arr[3]) ? $arr[3] : '';
        $logItem->title = isset($arr[4]) ? $arr[4] : '';
        if($logItem->lineType === LogItem::LINE_ONE && isset($arr[5])){
            $logItem->message = implode('|', array_slice($arr, 5));
        }
        return $logItem;
    }

    /**
     * kontrola, ci retazec pravdepodobne reprezentuje zaznam logu
     */
    public static function isLogItemFormat(string $string): bool {
        $is1stCharOk = isset($string[0]) && in_array((int)$string[0], [self::LINE_ONE, self::LINE_MULTI]);
        $is2ndCharOk = isset($string[1]) && $string[1] === '|';
        $is3rdCharOk = isset($string[2]) && in_array((int)$string[2], [self::TYPE_DEFAULT, self::TYPE_WARNING, self::TYPE_ERROR, self::TYPE_SYSTEM_ERROR]);
        $is4thCharOk = isset($string[3]) && $string[3] === '|';
        return $is1stCharOk && $is2ndCharOk && $is3rdCharOk && $is4thCharOk;
    }
}
