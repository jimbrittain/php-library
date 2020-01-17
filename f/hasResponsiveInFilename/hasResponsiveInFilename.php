<?php
    
    /*
     * Matching patterns
     * ***_2x, ***-2x, ***@2x, ***2x
     * ***-100w, ***_100w, ***@100w, ***100w
     * ***-100x100, ***_100x100, ***@100x100, ***100x100
     */
if (!function_exists('hasResponsiveInFilename')) {
    function hasResponsiveInFilename($s)
    {
        if (function_exists('getFilenameWithoutResponsiveParts')) {
            return (strlen(getFilenameWithoutResponsiveParts($s)) === strlen($s)) ? false : true;
        }
    }
}
if (!function_exists('getFilenameWithoutResponsiveParts')) {
    function getFilenameWithoutResponsiveParts($s)
    {
        if (is_string($s) && !empty($s)) {
            $pathParts = pathinfo($s);
            $reg = '/([\-_@]{0,1}[0-9]+(x|X|w|W|(x|X)+[0-9]+))$/';
            $matches = false;
            if (preg_match($reg, $pathParts['filename'], $matches)) {
                $r = substr($pathParts['filename'], 0, (0 - strlen($matches[0])));
            $pathParts['filename'] = $r;
            $r = reassemblePathInfo($pathParts);
                return (!empty($r)) ? $r : $s;
            }
        }
        return $s;
    }
}
if (!function_exists('reassemblePathInfo')) {
    function reassemblePathInfo($p)
    {
        if (is_array($p) 
            && isset($p['dirname']) 
            && isset($p['filename']) 
            && isset($p['extension'])) {
            $s = $p['dirname'];
            $s = (!empty($s)) ? $s.'/' : $s;
            $s .= $p['filename'];
            $s .= (!empty($p['extension'])) ? '.'.$p['extension'] : '';
            return $s;
        } else {
            return '';
        }
    }
}
