<?php
namespace Sentia\Utils;

class WebUtil {

    public function getOriginFromReferer(?string $urlReferer):string{
        $origin = '';
        if($urlReferer !== null){
            $urlArr = parse_url($urlReferer);
            $origin .= $urlArr['scheme'].'://';
            $origin .= $urlArr['host'];
            $origin .= isset($urlArr['port']) ? ':'.$urlArr['port'] : '';
        }
        return $origin;
    }
}
