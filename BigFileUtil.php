<?php
namespace Sentia\Utils;

use NoRewindIterator;
use SplFileObject;

class BigFileUtil{

    protected ?SplFileObject $file;

    public function __construct(){
    }

    public function load(string $filename, string $mode = "r"):void{
        if (!file_exists($filename)) {
            $this->file = null;
        }
        $this->file = new SplFileObject($filename, $mode);
    }

    public function isFileLoaded():bool{
        return $this->file !== null;
    }

    protected function iterateText(){
        $count = 0;
        while (!$this->file->eof()) {
            yield $this->file->fgets();
            $count++;
        }
        return $count;
    }

    protected function iterateBinary(int $bytes){
        $count = 0;
        while (!$this->file->eof()) {
            yield $this->file->fread($bytes);
            $count++;
        }
    }

    public function iterate(string $type = "Text", $bytes = NULL){
        if ($type == "Text") {
            return new NoRewindIterator($this->iterateText());
        } else {
            return new NoRewindIterator($this->iterateBinary($bytes));
        }
    }
}
