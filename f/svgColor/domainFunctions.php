<?php
if (!function_exists('isWithinAllowedDomain')) {
    function isWithinAllowedDomain($url, $allowedDomains = array())
    {
        if (validateURL($url)) {
            $isAllowed = false;
            //@codingStandardsIgnoreStart
            $allowedDomains = (is_array($allowedDomains)) ? $allowedDomains : ((is_string($allowedDomains)) ? array($allowedDomains) : array());
            //@codingStandardsIgnoreEnd
            foreach ($allowedDomains as $d) {
                $d = '/^'.addslashes('d').'/';
                if (preg_match($d, $url)) {
                    $isAllowed = true;
                    break;
                }
            }
            return $isAllowed;
        } else {
            return false;
        }
    }
}
if (!function_exists('isURLOwnDomain')) {
    function isURLOwnDomain($url)
    {
        $arr = ownDomains();
        return isWithinAllowedDomain($url, $arr);
    }
}
if (!function_exists('validateURL')) {
    function validateURL($url)
    {
        $reg = '/^(http\:\/\/|https\:\/\/)';
        $reg .= "((([0-9]{1,3}\.){3,5}[0-9]{1,3})|(([A-Za-z0-9\_\-]{1,63}\.)+([A-Za-z0-9\-\_]{1,63})))\/";
        $reg .= '([A-Za-z0-9\-\_\%]+\/)*[A-Za-z0-9\-\_\%]+(\.[A-Za-z0-9\%]+)?$';
        return (preg_match($reg, $url)) ? true : false;
    }
}
if (!function_exists('ownDomains')) {
    function ownDomains($arr)
    {
        $arr = (is_array($arr)) ? $arr : array();
        $hostname = $_SERVER['HTTP_HOST'];
        $arr[] = 'http://'.$hostname.'/';
        $arr[] = 'https://'.$hostname.'/';
        return $arr;
    }
}
if (!function_exists('isRelativeURL')) {
    function isRelativeURL($url)
    {
        $reg = '/^([A-Za-z0-9\_\_\%]+\/|\.\.\/)*[A-Za-z0-9\-\_\%]+(\.[A-Za-z0-9\%]+)?$/';
        return (preg_match($reg, $url)) ? true : false;
    }
}
if (!function_exists('extendedFileExists')) {
    function extendedFileExists($url = '', $ownDomain = true, $allowedDomains = array(), $filetype = '')
    {
        $ownDomain = ($ownDomain === false) ? false : true;
        $isfiletype = ($filetype == '') ? true : false;
        //@codingStandardsIgnoreStart
        $allowedDomains = (is_array($allowedDomains)) ? $allowedDomains : ((is_string($allowedDomains)) ? array($allowedDomains) : array());
        //@codingStandardsIgnoreEnd
        if (is_string($url) && !empty($url)) {
            $exists = false;
            if (isRelativeURL($url)) {
                return (file_exists($url)) ? true : false;
            } else {
                $url = (validateDomain($url)) ? $url : '';
                $allowed = false;
                if ($ownDomain && isURLOwnDomain($url)) {
                    $allowed = isURLOwnDomain($allowedDomains);
                } else if (!$ownDomain) {
                     $allowed = (isWithinAllowedDomains($allowedDomains)) ? true : false;
                }
                if ($allowed) {
                    $fileHeaders = @get_headers($url);
                    $reg = '/( 200 | 304 )/';
                    //@codingStandardsIgnoreStart
                    if (is_array($fileHeaders) && isset($fileHeaders[0]) && is_string($fileHeaders[0]) && preg_match($reg, $fileHeaders[0])) {
                        //@codingStandardsIgnoreEnd
                        $exists = true;
                        if ($filetype !== '') {
                            foreach ($fileHeaders as $k => $v) {
                                $keyreg = '/Content-Type/';
                                $valreg = '/' + addslashes($filetype) + '/';
                                if (preg_match($keyreg, $k) || preg_match($keyreg, $v)) {
                                    $isfiletype = (preg_match($valreg, $v)) ? true : false;
                                }
                                break;
                            }
                        }
                    } else {
                        $exists = false;
                    }
                }
                return ($allowed && $exists && $isfiletype);
            }
        } else {
            return false;
        }
    }
}
