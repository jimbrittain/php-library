<?php
if (!function_exists('convertLatitudeLongtitudeDMS')) {
    /**
        @method convertLatitudeLongtitudeDMS
        @param {Number} $lat Latitude
        @param {Number} $long Longtitude
        @return {Object}
            => @property {String} latitude 
            => @property {String} longtitude
        @description converts decimal Latitude/Longtitude to human-readable Degrees, Minutes and Seconds
     */
    function convertLatitudeLongtitudeDMS($lat, $long)
    {
        function llconvert($num, $upperPolar, $lowerPolar)
        {
            $polar = ($num < 0) ? $lowerPolar : $upperPolar;
            $num = abs($num);
            $degs = (int) $num;
            $minsandsecs = ($num - $degs) * 60;
            $mins = (int) $minsandsecs;
            $minsRemainder = ($minsandsecs - $mins) * 60;
            $secs = round($minsRemainder);
            return $degs.'° '.$mins.'′ '.$secs.'″ '.$polar;
        }
        $long = (float) $long;
        $lat = (float) $lat;
        $obj = new stdClass();
        $obj->latitude = llconvert($lat, 'N', 'S');
        $obj->longtitude = llconvert($long, 'E', 'W');
        return $obj;
    }
}
