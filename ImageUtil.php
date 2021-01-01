<?php
namespace Sentia\Utils;

class ImageUtil {
    CONST TYPE_GIF = 1;
    CONST TYPE_JPG = 2;
    CONST TYPE_PNG = 3;
    CONST DIRECTION_CW = 'cw';
    CONST DIRECTION_CCW = 'ccw';

    public string $path;  // cesta k obrazku
    public int $width = 0;  // sirka obrazka
    public int $height = 0;  // vyska obrazka
    public int $type;  // vid konstanty

    private FileUtil $fileUtils;
    private string $errorMessage;  // error Message
    private $imageResource;  // obsah obrazku

    /**
     * ImageUtil constructor.
     */
    public function __construct (FileUtil $fileUtils) {
        $this->fileUtils = $fileUtils;
    }

    private function setPastelRGBByString($name, &$r, &$g, &$b) {
        $hash = md5($name);
        $difference = 128;

        $r = hexdec(substr($hash, 8, 2));
        $g = hexdec(substr($hash, 4, 2));
        $b = hexdec(substr($hash, 0, 2));

        if($r < $difference) $r += $difference;
        if($g < $difference) $g += $difference;
        if($b < $difference) $b += $difference;
    }

    /**
     * create rectangle avatar with 2 characters
     * @param string $content - optimized for 2 letters
     * @param int $wh - width / height [recommended: 640]
     * @param string $filePath - path where we want to save the image (including file name)
     */
    public function createAndSaveAvatar(string $content, int $wh, string $filePath, string $pathToFont) {
        $img = imagecreate($wh, $wh);

        // get bounding box size
        $fontSize = round($wh / 2.5);
        $r = 0;
        $g = 0;
        $b = 0;
        $this->setPastelRGBByString($content, $r, $g, $b);
        $textBox = imagettfbbox($fontSize, 0, $pathToFont, $content);

        // get text width and height
        $textWidth = $textBox[2] - $textBox[0];
        $textHeight = $textBox[7] - $textBox[1];

        // calculate coordinates of the text
        $x = ($wh/2) - ($textWidth / 2) - 8;
        $y = ($wh/2) - ($textHeight / 2);

        $textbgcolor = imagecolorallocate($img, $r, $g, $b);
        $textcolor = imagecolorallocate($img, 255, 255, 255);

        imagettftext($img, $fontSize, 0, $x, $y, $textcolor , $pathToFont, $content);
        imagejpeg($img, $filePath);
    }

    /**
     * Nahrajeme (nacitame) existujuci obrazok + udaje o nom
     * @param $filePath
     */
    public function load($filePath) {
        if(!file_exists($filePath)) {  // kontrola, ci subor existuje
            $this->errorMessage = 'File does not exists';
        } elseif(!is_readable($filePath)) {  // kontrola, ci sa da zo suboru citat
            $this->errorMessage = 'File read error';
        } else {  // subor existuje
            $this->path = $filePath;
            $size = getimagesize($this->path);
            $this->width = $size[0];
            $this->height = $size[1];

            switch($size[2]) {  // zistenie typu obrazka
                default:
                    $this->errorMessage = "Unknown image type";
                    break;
                case 3:
                    $this->type = self::TYPE_PNG;
                    $this->imageResource = imagecreatefrompng($this->path);
                    break;
                case 2:
                    $this->type = self::TYPE_JPG;
                    $this->imageResource = imagecreatefromjpeg($this->path);
                    break;
                case 1:
                    $this->type = self::TYPE_GIF;
                    $this->imageResource = imagecreatefromgif($this->path);
                    break;
            }
        }
    }

    /**
     * Rotuje obrazok o 90 stupnov clockwise alebo counter-clockwise
     */
    public function rotate(string $theDirection = self::DIRECTION_CW) {
        if($theDirection == self::DIRECTION_CW) {
            $this->imageResource = imagerotate($this->imageResource,-90,0);
        } else {
            $this->imageResource = imagerotate($this->imageResource,90,0);
        }
        $this->width = $this->height;
        $this->height = $this->width;
    }

    /**
     * Ulozi obrazok ako subor
     * @param $thePathWithFileName - komplet cesta, vratane nazvu suboru, kam sa obrazok z resource ulozi
     * @param int $theNumQuality - kvalita: 0-100    0-najhorsia, 100 najlepsia
     * @throws \Exception
     */
    public function save(string $thePathWithFileName, int $theNumQuality = 100) {
        switch($this->type) {
            default:
                throw new \Exception('Image type \''.$this->type.'\' not supported');
            case self::TYPE_JPG:
                $this->fileUtils->delete($thePathWithFileName);
                imagejpeg($this->imageResource, $thePathWithFileName, $theNumQuality);
                break;
            case self::TYPE_PNG:
                $this->fileUtils->delete($thePathWithFileName);
                imagepng($this->imageResource, $thePathWithFileName, 0);
                break;
            case self::TYPE_GIF:
                $this->fileUtils->delete($thePathWithFileName);
                imagegif($this->imageResource, $thePathWithFileName);
                break;
        }
    }

    /**
     * Zobrazi obrazok na obrazovku
     */
    public function getImage() {
        switch($this->type) {
            default:
                throw new \Exception('Image type \''.$this->type.'\' not supported');
            case self::TYPE_JPG:
                //header("Content-type: image/jpeg");
                return imagejpeg($this->imageResource);
            case self::TYPE_PNG:
                //header("Content-type: image/png");
                return imagepng($this->imageResource);
            case self::TYPE_GIF:
                //header("Content-type: image/gif");
                return imagegif($this->imageResource);
        }
    }

    /**
     * Updatuje rozmery obrazka podla resource obrazka
     */
    private function updateImageDetails() {
        $this->width = imagesx($this->imageResource);
        $this->height = imagesy($this->imageResource);
    }

    /**
     * meni velkost obrazka - je to POMOCNA metoda, ktora sa pouziva v inych metodach
     */
    private function _resize($numWidth, $numHeight) {
        if($this->type == self::TYPE_GIF) {
            $pomImageResource = imagecreate($numWidth, $numHeight);
        } else {
            $pomImageResource = imagecreatetruecolor($numWidth, $numHeight);
        }

        if ( $this->type == self::TYPE_GIF || $this->type == self::TYPE_PNG ) {
            $trnprt_indx = imagecolortransparent($pomImageResource);

            // If we have a specific transparent color
            if ($trnprt_indx >= 0) {

                // Get the original image's transparent color's RGB values
                $trnprt_color    = imagecolorsforindex($this->imageResource, $trnprt_indx);

                // Allocate the same color in the new image resource
                $trnprt_indx    = imagecolorallocate($pomImageResource, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

                // Completely fill the background of the new image with allocated color.
                imagefill($pomImageResource, 0, 0, $trnprt_indx);

                // Set the background color for new image to transparent
                imagecolortransparent($pomImageResource, $trnprt_indx);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif ($this->type == self::TYPE_PNG) {
                // Turn off transparency blending (temporarily)
                imagealphablending($pomImageResource, false);

                // Create a new transparent color for image
                $color = imagecolorallocatealpha($pomImageResource, 0, 0, 0, 127);

                // Completely fill the background of the new image with allocated color.
                imagefill($pomImageResource, 0, 0, $color);

                // Restore transparency blending
                imagesavealpha($pomImageResource, true);
            }
        }

        imagecopyresampled($pomImageResource, $this->imageResource, 0, 0, 0, 0, $numWidth, $numHeight, $this->width, $this->height);

        $this->imageResource = $pomImageResource;
        $this->updateImageDetails();
        $this->width = $numWidth;
        $this->height = $numHeight;
    }

    /**
     * zmena velkosti podla Sirky - vyska sa vypocita automaticky
     * @param $numWidth
     */
    public function resizeToWidth($numWidth) {
        $numHeight=(int)(($numWidth*$this->height)/$this->width);
        $this->_resize($numWidth, $numHeight);
    }

    /**
     * zmena velkosti podla vysky - sirka sa vypocita automaticky
     * @param $numHeight
     */
    public function resizeToHeight($numHeight) {
        $numWidth=(int)(($numHeight*$this->width)/$this->height);
        $this->_resize($numWidth, $numHeight);
    }

    /**
     * zmena velkosti podla potreby
     * @param $size - moze byt pole  array(x, y) alebo 1 hodnota a vytvori sa stvorcova velkost (hodnota x hodnota)
     */
    public function resizeToCustom($size) {
        if(!is_array($size)) {
            $this->_resize((int)$size, (int)$size);
        } else {
            $this->_resize((int)$size[0], (int)$size[1]);
        }
    }

    /**
     * Crop the image
     * @param int $theStartX
     * @param int $theStartY
     * @param int $theWidth
     * @param int $theHeight
     */
    public function crop($theStartX, $theStartY, $theWidth, $theHeight) {
        $crop = imagecreatetruecolor($theWidth, $theHeight);
        imagecopy ($crop, $this->imageResource, 0, 0, $theStartX, $theStartY, $theWidth, $theHeight);
        $this->imageResource = $crop;
    }

    /**
     * Automatic resize image in scale by width/height
     * @param int $theMaxWidth
     * @param int $theMaxHeight
     */
    public function automaticResizeInScale($theMaxWidth, $theMaxHeight) {
        if($this->height > $theMaxHeight || $this->width > $theMaxWidth) {
            if($this->width > $theMaxWidth && $this->width - $theMaxWidth > $this->height - $theMaxHeight) {
                $this->resizeToWidth($theMaxWidth);
            } else if($this->height > $theMaxHeight && $this->height - $theMaxHeight > $this->width - $theMaxWidth) {
                $this->resizeToHeight($theMaxHeight);
            } else {
                $this->resizeToCustom(array($theMaxWidth, $theMaxHeight));
            }
        }
    }

}
