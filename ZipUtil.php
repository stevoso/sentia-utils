<?php
namespace Sentia\Utils;

class ZipUtil {
    /**
     * rozbali ZIP subor do urceneho adresara
     * @param $zipFile - cesta k zip suboru
     * @param $dir - adresar (bez koncoveho lomitka), kde sa ma zip odzipovat
     * @param bool $overwrite
     * @return bool|null
     */
    public function extract($zipFile, $dir, $overwrite=false){
        if(file_exists($zipFile)){
            $zip = new \ZipArchive;
            if($zip->open($zipFile)) {
                $zip->extractTo($dir);
                $zip->close();
                return true;
            } else {
                return false;
            }

//            $files = [];
//            $zip = new \ZipArchive;
//            if($zip->open($zipFile)) {
//                for($i=0; $i<$zip->numFiles; $i++) {
//                    $entry = $zip->getNameIndex($i);
//                    if($overwrite || !file_exists($dir.'/'.$entry)){
//                        $files[] = $entry;
//                    }
//                }
//                $result = $zip->extractTo($dir, $files);
//                $zip->close();
//                return $result;
//            }
        }
        return false;
    }
}
