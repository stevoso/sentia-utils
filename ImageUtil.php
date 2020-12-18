<?php
namespace Sentia\Utils;

class ImageUtil {

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

}
