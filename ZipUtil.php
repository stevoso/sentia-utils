<?php
namespace Sentia\Utils;

class ZipUtil {
    /**
     * rozbali ZIP subor do urceneho adresara
     * @param $zipFile - cesta k zip suboru
     * @param $dir - adresar (bez koncoveho lomitka), kde sa ma zip odzipovat
     */
    public function extract(string $zipFile, string $dir, bool $overwrite = false): bool {
        if(file_exists($zipFile)){
            $zip = new \ZipArchive;
            if($zip->open($zipFile)) {
                $zip->extractTo($dir);
                $zip->close();
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}
