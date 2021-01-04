<?php
namespace Sentia\Utils;

use FilesystemIterator;
use Symfony\Component\HttpFoundation\Response;

class FileUtil {
    private StringUtil $stringUtils;

    public static array $EXTENSIONS_FORBIDDEN = ['php', 'exe'];
    // tato classa dovoli vytvorit (create, copy, move) subory len s tymito koncovkami
    public static array $EXTENSIONS_DOC = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pps', 'ppsx', 'pdf', 'csv', 'rtf'];
    public static array $EXTENSIONS_IMG = ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'tif'];
    public static array $EXTENSIONS_VIDEO = ['flv', 'mp4', 'avi', 'wmv'];
    public static array $EXTENSIONS_PACKAGE = ['zip', 'rar', 'gz'];
    public static array $EXTENSIONS_OTHER = ['data', 'log', 'xml', 'xsd', 'dtd', 'tmp', 'txt', 'rpu', 'htm', 'html', 'js', 'mdb', 'css', 'war'];

    public function __construct(StringUtil $stringUtils){
        $this->stringUtils = $stringUtils;
    }

    //------------------ extension -------------------
    /**
     * @param $filename
     * @throws \Exception
     */
    private function checkExtension($filename){
        $this->splitFilename($filename, $path, $baseName, $filename2, $extension);
        $extension = $this->stringUtils->toLower($extension);
        if($extension != '' && in_array($extension, self::$EXTENSIONS_FORBIDDEN)){
            throw new \Exception('Nie je povolene vytvorit (kopirovat, premenovat) subor \'' . $baseName . '\' (pripona \'' . $extension . '\' je nepovolena)!');
        }
    }
    
    public function checkExtensionType(string $extension, array $type): bool {
        return in_array($this->stringUtils->toLower($extension), $type);
    }

    //------------------ splitFilename -------------------
    /**
     *
     * @param $filenameIn - '/www/htdocs/inc/lib.inc.php'
     * @param $path - '/www/htdocs/inc' - bez posledneho /
     * @param $baseName - 'lib.inc.php'
     * @param $filename - 'lib.inc'
     * @param $extension - 'php' - bez bodky
     */
    public function splitFilename($filenameIn, &$path, &$baseName, &$filename, &$extension){
        $s = pathinfo($filenameIn);
        $path = $s['dirname'];
        $baseName = $s['basename'];
        $filename = $s['filename'];
        $extension = array_key_exists('extension', $s) ? $s['extension'] : null;
    }

    /**
     * vracia pole s nazvami suborov a adresarov
     */
    public function getFilesAndDirs(string $dir): array {
        if(is_dir($dir)){
            return array_diff(scandir($dir), ['..', '.']);
        }
        return [];
    }

    /**
     * vracia pole suborov, ktore vyhovuju pozadovanej koncovke suboru. Ak null, tak vracia vsetky subory. '.' a '..' sý vynechané.
     */
    public function getFiles($dir, $expectedExtension = null): array {
        $filesDirs = $this->getFilesAndDirs($dir);
        $files = [];
        foreach($filesDirs as $fileDir){
            if(is_file($dir.'/'.$fileDir)){
                $extension = $this->getExtension($fileDir);
                if($expectedExtension === null || (mb_strtolower($expectedExtension) == mb_strtolower($extension))){
                    $files[] = $fileDir;
                }
            }
        }
        return $files;
    }

    /**
     * @return string - basename vid. splitFilename()
     */
    public function getBasename($filenameIn): string {
        $path = $baseName = $filename = $extension = null;
        $this->splitFilename($filenameIn, $path, $baseName, $filename, $extension);
        return $baseName;
    }

    /**
     * @param $filenameIn
     * @return string - filename vid. splitFilename()
     */
    public function getFilename($filenameIn): string {
        $path = $baseName = $filename = $extension = null;
        $this->splitFilename($filenameIn, $path, $baseName, $filename, $extension);
        return $filename;
    }

    /**
     *
     * @return string - extension bez bodky.
     */
    public function getExtension($filename){
        $this->splitFilename($filename, $path, $baseName, $filename2, $extension);
        return $extension;
    }

    /**
     * vracia formatovanu velkost suboru v bytoch|KB|MB|GB|TB
     * @param $fileName
     * @return int|null|string
     */
    public function getFileSizeFormatted($fileName){
        $bytes = null;
        if(file_exists($fileName)){
            $bytes = filesize($fileName);
            if ($bytes >= 1099511627776){
                $bytes = number_format($bytes / 1099511627776, 2) . ' TB';
            }elseif ($bytes >= 1073741824){
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
            }elseif ($bytes >= 1048576){
                $bytes = number_format($bytes / 1048576, 2) . ' MB';
            }elseif ($bytes >= 1024){
                $bytes = number_format($bytes / 1024, 2) . ' KB';
            }elseif ($bytes > 1){
                $bytes = $bytes . ' bytes';
            }elseif ($bytes == 1){
                $bytes = $bytes . ' byte';
            }else{
                $bytes = '0 bytes';
            }
            $bytes = str_replace('.', ',', $bytes);
        }
        return $bytes;
    }

    public function getDirSize($dir){
        $size = 0;
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : self::getDirSize($each);
        }
        return $size;
    }

    public function formatDiscSize($bytes): string {
        if ($bytes >= 1099511627776){
            $bytes = number_format($bytes / 1099511627776, 2) . ' TB';
        }elseif ($bytes >= 1073741824){
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }elseif ($bytes >= 1048576){
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }elseif ($bytes >= 1024){
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }elseif ($bytes > 1){
            $bytes = $bytes . ' bytes';
        }elseif ($bytes == 1){
            $bytes = $bytes . ' byte';
        }else{
            $bytes = '0 bytes';
        }
        return str_replace('.', ',', $bytes);
    }

    //------------------ copy, move, delete -------------------

    /**
     * @param $filenameFrom
     * @param $filenameTo
     * @throws \Exception
     */
    public function copy($filenameFrom, $filenameTo): bool {
        if(!file_exists($filenameFrom)){
            throw new \Exception('Neexistuje subor \'' . $filenameFrom . '\'!');
        }
        if(file_exists($filenameTo)){
            throw new \Exception('Subor \'' . $filenameTo . '\' uz existuje!');
        }
        $this->checkExtension($filenameTo);
        if(!copy($filenameFrom, $filenameTo)){
            throw new \Exception('Nepodarilo sa skopirovat subor \'' . $filenameFrom . '\' do \'' . $filenameTo . '\'!');
        }
        return true;
    }

    /**
     * @param $filenameFrom
     * @param $filenameTo
     * @throws \Exception
     */
    public function move($filenameFrom, $filenameTo){
        if(!file_exists($filenameFrom)){
            throw new \Exception('Neexistuje subor \'' . $filenameFrom . '\'!');
        }
        if(file_exists($filenameTo)){
            throw new \Exception('Subor \'' . $filenameTo . '\' uz existuje!');
        }
        $this->checkExtension($filenameTo);
        if(!rename($filenameFrom, $filenameTo)){
            throw new \Exception('Nepodarilo sa presunut subor z \'' . $filenameFrom . '\' do \'' . $filenameTo . '\'!');
        }
    }

    /**
     * delete one file
     */
    public function delete(string $filename): bool {
        if(file_exists($filename)){
            if(!unlink($filename)){
                return false;
            }
        }
        return true;
    }

    /**
     * delete directory recursivelly
     */
    public function deleteDir(string $dir): bool {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir($dir.'/'.$file)) ? $this->deleteDir($dir.'/'.$file) : unlink($dir.'/'.$file);
        }
        return rmdir($dir);
    }

    /**
     * z adresara vymaze vsetky subory a adresare. Zakladny vstupny adresar ponecha
     */
    public function emptyDir(string $dir): void {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir($dir.'/'.$file)) ? $this->deleteDir($dir.'/'.$file) : unlink($dir.'/'.$file);
        }
    }

    //------------------ dir -------------------
    public function isDir(string $dirname): bool {
        return is_dir($dirname);
    }

    /**
     * skontroluje, ci adresar existuje. Ak nie, tak ho vytvori.
     * @param $dirname
     * @deprecated [4.1.2021] Pouzit metodu mkdirIfNotExists
     */
    public function checkDirExistence(string $dirname){
        if(!file_exists($dirname)){
            mkdir($dirname);
        }
    }
    public function mkdirIfNotExists(string $dirname): bool {
        if(!is_dir($dirname)){
            return mkdir($dirname);
        }
        return true;
    }

    /**
     * vytvori adresar
     * @param $dirname - cesta k pozadovanemu adresaru, na konci nesmie byt lomitko
     * @deprecated [4.1.2021] Pouzit metodu mkdirIfNotExists
     */
    public function createDir(string $dirname):bool{
        // kontrola, ci adresar uz existuje
        if(is_dir($dirname)){
            return true;
        }

        // vytvorenie adresara
        if(!mkdir($dirname)){
            return false;
        }
        return true;
    }

    //------------------ generateRandomFilename -------------------
    /**
     * TODO pouzitie nahradit cez UUID
     * Vygeneruje nahodny filename (aky este neexistuje).
     * @param $path
     * @param $extension
     * @return string
     */
    public function generateRandomFilename($path, $extension) : string {
        do{
            $extension2 = empty($extension) ? '' : '.' . $extension;
            $filename = $path . '/' . uniqid('', true) . mt_rand() . $extension2;
        }while(file_exists($filename));
        return $filename;
    }

    //------------------ filePutContents -------------------
    /**
     * @param $filename
     * @param $data
     * @param int $flags
     * @return bool|int
     * @throws \Exception
     */
    public function filePutContents($filename, $data, $flags = 0){
        $this->checkExtension($filename);
        if(($bytes = file_put_contents($filename, $data, $flags)) === false){
            throw new \Exception('Padlo file_put_contents() \'' . $filename . '\'!');
        }
        return $bytes;
    }

    /**
     * @param $path
     * @param $extension - bez bodky
     * @param $data
     * @param int $flags
     * @throws \Exception
     */
    public function generateRandomFilenameAndPutContents($path, $extension, $data, $flags = 0): string {
        $filename = $this->generateRandomFilename($path, $extension);
        $this->filePutContents($filename, $data);
        return $filename;
    }

    //------------------ download -------------------
    /**
     *
     * @param $filename - filename aj s cestou
     * @param $filenameClient - filename pre clienta
     */
    public function download($filename, $filenameClient): Response {
        // response
        $response = new Response();
        // header
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', $this->getMimeType($filename));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filenameClient . '";');
        $response->headers->set('Content-length', filesize($filename));
        // send header before outputting anything
        $response->sendHeaders();
        $response->setContent(file_get_contents($filename));
        return $response;
    }

    public function getMimeType($filename){
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimeType;
    }

    /**
     * download
     */
    public function downloadByContentMimeType($fileContent, $mimeType, $filenameClient):Response{
        // response
        $response = new Response();
        // header
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', $mimeType);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filenameClient . '";');
        $response->headers->set('Content-length', strlen($fileContent));
        // send header before outputting anything
        $response->sendHeaders();
        $response->setContent($fileContent);
        return $response;
    }

    /**
     * Pouziva sa napriklad pri Union WS
     * @param $urlFile
     * @param $pathToCert
     * @param $certPasswd
     * @return mixed
     */
    public function getFileContentFromSecuredUrl($urlFile, $pathToCert, $certPasswd){
        $curlhandle = curl_init();
        curl_setopt($curlhandle, CURLOPT_URL, $urlFile);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlhandle, CURLOPT_SSLVERSION, 6);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlhandle, CURLOPT_SSLCERT, $pathToCert);
        curl_setopt($curlhandle, CURLOPT_SSLCERTPASSWD, $certPasswd);
        $response = curl_exec($curlhandle);
        curl_close($curlhandle);
        return $response;
    }

    /**
     * check if directory is empty (no files, no subdirs)
     */
    public function isEmptyDir(string $pathToDir):bool{
        return !(new FilesystemIterator($pathToDir))->valid();
    }

    /**
     * create directories: ww/xx/yy/zz/uuid ... only if directories does not exist
     * $basePath - without '/' at the end
     */
    public function createDirsForUuid4(string $basePath, string $uuid, bool $includeUUidDir = true):string{
        $dirPart1 = $uuid[0].$uuid[1];
        $dirPart2 = $uuid[2].$uuid[3];
        $dirPart3 = $uuid[4].$uuid[5];
        $dirPart4 = $uuid[6].$uuid[7];

        $basePath .= '/'.$dirPart1;
        if(!$this->isDir($basePath)){
            $this->createDir($basePath);
        }

        $basePath .= '/'.$dirPart2;
        if(!$this->isDir($basePath)){
            $this->createDir($basePath);
        }

        $basePath .= '/'.$dirPart3;
        if(!$this->isDir($basePath)){
            $this->createDir($basePath);
        }

        $basePath .= '/'.$dirPart4;
        if(!$this->isDir($basePath)){
            $this->createDir($basePath);
        }
        if($includeUUidDir){
            $basePath .= '/'.$uuid;
            if(!$this->isDir($basePath)){
                $this->createDir($basePath);
            }
        }
        return $basePath;
    }

    /**
     * use this method if uuid file is deleted. Empty folders will be deleted
     */
    public function clearUuid4Dirs(string $basePath, string $uuid):void{
        $dirPart1 = $uuid[0].$uuid[1];
        $dirPart2 = $uuid[2].$uuid[3];
        $dirPart3 = $uuid[4].$uuid[5];
        $dirPart4 = $uuid[6].$uuid[7];

        $path = $basePath.'/'.$dirPart1.'/'.$dirPart2.'/'.$dirPart3.'/'.$dirPart4;
        if($this->isDir($path) && $this->isEmptyDir($path)){
            rmdir($path);
        }

        $path = $basePath.'/'.$dirPart1.'/'.$dirPart2.'/'.$dirPart3;
        if($this->isDir($path) && $this->isEmptyDir($path)){
            rmdir($path);
        }

        $path = $basePath.'/'.$dirPart1.'/'.$dirPart2;
        if($this->isDir($path) && $this->isEmptyDir($path)){
            rmdir($path);
        }

        $path = $basePath.'/'.$dirPart1;
        if($this->isDir($path) && $this->isEmptyDir($path)){
            rmdir($path);
        }
    }

    public function getPathByUuid(string $basePath, string $uuid, bool $includeUuidDir = true): ?string {
        $dirPart1 = $uuid[0] . $uuid[1];
        $dirPart2 = $uuid[2] . $uuid[3];
        $dirPart3 = $uuid[4] . $uuid[5];
        $dirPart4 = $uuid[6] . $uuid[7];

        $path = $basePath . '/' . $dirPart1 . '/' . $dirPart2 . '/' . $dirPart3 . '/' . $dirPart4;
        if($includeUuidDir){
            $path .= '/' . $uuid;
        }
        return $this->isDir($path) ? $path : null;
    }
    
    /**
     * @param $file
     * @param $text
     */
    public function writeToEnd($file, $text){
        if($handle = fopen($file, 'a')){
            fwrite($handle, $text);
            fclose($handle);
        }
    }

}
