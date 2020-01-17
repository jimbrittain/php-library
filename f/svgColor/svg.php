<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once('domainFunctions.php');
include_once('svgColor.php');

function resolveGetColors($arr = array())
{
     $arr = (is_array($arr)) ? $arr : array();
     if (isset($_GET['col'])) {
         $t = $_GET['col'];
         $t = (!is_array($t)) ? array($t) : $t;
         foreach ($t as $c) {
             $c = urldecode($c);
             $c = strip_tags($c);
             $arr[] = CSSColorString::sanitize($c);
         }
     }
     return $arr;
}
function resolveImageSource($default = '')
{
    $default = (is_string($default)) ? $default : '';
    if (isset($_GET['src'])) {
        if (extendedFileExists($_GET['src'], true, array(), 'image/svg+xml')) {
            return $_GET['src'];
        }
    }
    return '';
}
function getSVG($src)
{
    echo "SHITBALLS".$src;
    if (!empty($src)) {
        $handle = fopen($src, 'r');
        if ($handle !== false) {
            $contents = fread($handle, filesize($src));
            fclose($handle);
            return (!empty($contents)) ? $contents : '';
        }
    }
    return '';
}
$imageSource = resolveImageSource('i/border/thread.svg');
$cols = resolveGetColors();
if (!headers_sent()) {
    header('Content-type: image/svg+xml');
}
echo '<'.'?xml version="1.0" encoding="utf-8"?'.'>';
/* @codingStandardsIgnoreStart */
?>
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="fill" viewBox="0 0 160 160" width="160" height="160">
    <?php
        /* @codingStandardsIgnoreEnd */
        if (isset($cols) && is_array($cols)) {
            echo '<style type="text/css">';
                echo "\n/* <![CDATA[ */\n";
                for ($i = 0, $imax = count($cols); $i < $imax; $i += 1) {
                    echo "\n.fill { fill: ".$cols[$i]."; }";
                }
                echo "\n/* ]]> */\n";
            echo '</style>';
        }
    ?>
    <?php
        echo getSVG($imageSource);
    ?>
</svg>
