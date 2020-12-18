<?php
namespace Sentia\Utils;

class WebUtil {
    /**
     * rozbali ZIP subor do urceneho adresara
     * @param $zipFile - cesta k zip suboru
     * @param $dir - adresar (bez koncoveho lomitka), kde sa ma zip odzipovat
     * @param bool $overwrite
     * @return bool|null
     */
    public function getOriginFromReferer(string $urlReferer):string{
        $origin = '';
        $urlArr = parse_url($urlReferer);
        $origin .= $urlArr['scheme'].'://';
        $origin .= $urlArr['host'];
        $origin .= isset($urlArr['port']) ? ':'.$urlArr['port'] : '';
        return $origin;
    }
}
