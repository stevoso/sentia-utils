<?php
namespace Sentia\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload {
    private ?UploadedFile $uploadedFile = null;
    private bool $isChecked = false;

    public function __construct(){
    }

    public function load(UploadedFile $uploadedFile){
        $this->uploadedFile = $uploadedFile;
    }

    /**
     * @return string - pripona bez bodky k uploadovanemu suboru
     * @throws \Exception
     */
    public function getExtension(){
        $this->check();
        return $this->uploadedFile->getClientOriginalExtension();
    }

    /**
     * Skopiruje uploadnuty subor do path/filename.
     * @param $dirPath
     * @param $filename
     * @throws \Exception
     */
    public function move($dirPath, $filename){
        $this->check();

        if(mb_substr($dirPath, -1) != "/"){
            $dirPath .= "/";
        }

        if(file_exists($dirPath.$filename)){
            throw new \Exception('Rovnaký súbor už existuje! *'.$filename.'*');
        }
        $this->uploadedFile->move($dirPath, $filename);
    }

    private function check(){
        if(!$this->isChecked){
            // valid
            if(!$this->uploadedFile->isValid()){
                $errorMessage = $this->uploadedFile->getErrorMessage();
                throw new \Exception('Subor sa nestiahol korektne (' . $errorMessage . ')!');
            }
            $this->isChecked = true;
        }
    }
}
