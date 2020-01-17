<?php
if (!function_exists('isTelephoneNumber')) {
    /**
        @method isTelephoneNumber
        @param $n
        @return {Boolean} true || false
     */
    function isTelephoneNumber($n)
    {
        //$reg = "/^(\+[0-9]{1,4})?(\(?([0-9]+[\-\.\ \)]?)){8,15}$/";
        $reg = "/^(\+[0-9]{1,4}(\.|\-\ )*)?([0-9]+[\-\.\ \)]?){7,14}([0-9]+)$/";
        return (preg_match($reg, $n) !== false);
    }
}
