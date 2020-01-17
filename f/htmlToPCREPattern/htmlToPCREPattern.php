<?php
if (!function_exists('htmlToPCREPattern')) {
    /**
     * @function htmlToPCREPattern
     * @param $pattern {String} html regular expression pattern
     * @return pcre compliant pattern
     **/
    function htmlToPCREPattern($pattern)
    {
        //replace \u1278 with \x{1278}
        $unicodePattern = "/\\\u([0-9A-Fa-f]{4})/";
        $pattern = preg_replace($unicodePattern, "\\\x{\\1}", $pattern);
        return $pattern;
    }
}
