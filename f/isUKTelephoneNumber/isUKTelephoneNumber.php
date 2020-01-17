<?php
if (!function_exists('isUKTelephoneNumber')) {
    /**
        @method isUKTelephoneNumber
        @param $n
        @return {Boolean} true || false
     */
    function isUKTelephoneNumber($n)
    {
        $reg = "/^((\()?\+44(\))?([\ \.\-])?)?((\()?[0-9]+[\.\-\ ]?((\))[\.\-\ ]?)?){10,11}$/";
        return (preg_match($reg, $n) === true);
    }
}
