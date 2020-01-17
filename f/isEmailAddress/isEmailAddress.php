<?php
if (!function_exists('isEmailAddress')) {
    function isEmailAddress($e)
    {
       //name part
       $reg = "/^([A-Za-z0-9\-\_]{1,63}\.)*[A-Za-z0-9\-\_]{1,63}@";
       //domain part
       $reg .= "((([0-9]{1,3}\.){3,5}[0-9]{1,3})|(([A-Za-z0-9\_\-]{1,63}\.)+([A-Za-z0-9\-\_]{1,63})))$/";
       return (preg_match($reg, $e) !== false);
    }
}
