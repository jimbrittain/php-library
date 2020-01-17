<?php
if (!function_exists('isFullName')) {
    function isFullName($n)
    {
        //check if has correct characters, no numbers, little punctuation, allow -, ',
        $reg = "/^[\\p{L}][ \\p{L}'-.]*[\\p{L}]$/";
        return (preg_match($reg, $n) === true);
    }
}
