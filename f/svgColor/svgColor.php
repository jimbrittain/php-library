<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
class CSSColorString
{
    static $default = '#000000';
    static private $_htmlKeywords = array(
        'white',
        'silver', 
        'gray',
        'black',
        'red',
        'maroon',
        'yellow',
        'olive', 
        'lime', 
        'green',
        'aqua',
        'teal',
        'blue',
        'navy',
        'fuchsia',
        'purple',
    );
    static private $_x11Keywords = array(
        'aliceblue',
        'antiquewhite',
        'aqua',
        'aquamarine',
        'azure',
        'beige',
        'bisque',
        'black',
        'blanchedalmond',
        'blue',
        'blueviolet',
        'brown',
        'burlywood',
        'cadetblue',
        'chartreuse',
        'chocolate',
        'coral',
        'cornflowerblue',
        'cornsilk',
        'crimson',
        'cyan',
        'darkblue',
        'darkcyan',
        'darkgoldenrod',
        'darkgray',
        'darkgreen',
        'darkgrey',
        'darkkhaki',
        'darkmagenta',
        'darkolivegreen',
        'darkorange',
        'darkorchid',
        'darkred',
        'darksalmon',
        'darkseagreen',
        'darkslateblue',
        'darkslategray',
        'darkslategrey',
        'darkturquoise',
        'darkviolet',
        'deeppink',
        'deepskyblue',
        'dimgray',
        'dimgrey',
        'dodgerblue',
        'firebrick',
        'floralwhite',
        'forestgreen',
        'fuchsia',
        'gainsboro',
        'ghostwhite',
        'gold',
        'goldenrod',
        'gray',
        'grey',
        'green',
        'greenyellow',
        'honeydew',
        'hotpink',
        'indianred',
        'indigo',
        'ivory',
        'khaki',
        'lavender',
        'lavenderblush',
        'lawngreen',
        'lemonchiffon',
        'lightblue',
        'lightcoral',
        'lightcyan',
        'lightgoldenrodyellow',
        'lightgray',
        'lightgreen',
        'lightgrey',
        'lightpink',
        'lightsalmon',
        'lightseagreen',
        'lightskyblue',
        'lightslategray',
        'lightslategrey',
        'lightsteelblue',
        'lightyellow',
        'lime',
        'limegreen',
        'linen',
        'magenta',
        'maroon',
        'mediumaquamarine',
        'mediumblue',
        'mediumorchid',
        'mediumpurple',
        'mediumseagreen',
        'mediumslateblue',
        'mediumspringgreen',
        'mediumturquoise',
        'mediumvioletred',
        'midnightblue',
        'mintcream',
        'mistyrose',
        'moccasin',
        'navajowhite',
        'navy',
        'oldlace',
        'olive',
        'olivedrab',
        'orange',
        'orangered',
        'orchid',
        'palegoldenrod',
        'palegreen',
        'paleturquoise',
        'palevioletred',
        'papayawhip',
        'peachpuff',
        'peru',
        'pink',
        'plum',
        'powderblue',
        'purple',
        'red',
        'rosybrown',
        'royalblue',
        'saddlebrown',
        'salmon',
        'sandybrown',
        'seagreen',
        'seashell',
        'sienna',
        'silver',
        'skyblue',
        'slateblue',
        'slategray',
        'slategrey',
        'snow',
        'springgreen',
        'steelblue',
        'tan',
        'teal',
        'thistle',
        'tomato',
        'turquoise',
        'violet',
        'wheat',
        'white',
        'whitesmoke',
        'yellow',
        'yellowgreen',
    );
    static private $_css2SystemKeywords = array(
        'ActiveBorder',
        'ActiveCaption',
        'AppWorkspace',
        'Background',
        'ButtonFace',
        'ButtonHighlight',
        'ButtonShadow',
        'ButtonText',
        'CaptionText',
        'GrayText',
        'Highlight',
        'HighlightText',
        'InactiveBorder',
        'InactiveCaption',
        'InactiveCaptionText',
        'InfoBackground',
        'InfoText',
        'Menu',
        'MenuText',
        'Scrollbar',
        'ThreeDDarkShadow',
        'ThreeDFace',
        'ThreeDHighlight',
        'ThreeDLightShadow',
        'ThreeDShadow',
        'Window',
        'WindowFrame',
        'WindowText',
    );
    static function inIntergerRange($i, $min, $max)
    {
        return (is_numeric($i) && (int) $i == $i && $i >= $min && $i <= $max) ? true : false;
    }
    static function inFloatRange($i, $min, $max)
    {
        return (is_numeric($i) && $i >= $min && $i <= $max) ? true : false;
    }
    static function isRGB($s)
    {
        $reg = '/^rgb\(([0-9]{1,3}),(?: ){0,1}([0-9]{1,3}),(?: ){0,1}([0-9]{1,3})\)$/';
        $matches = array();
        if (preg_match($reg, $s, $matches)) {
            $r1 = (self::inIntergerRange($matches[1], 0, 255)) ? true : false;
            $r2 = (self::inIntergerRange($matches[2], 0, 255)) ? true : false;
            $r3 = (self::inIntergerRange($matches[3], 0, 255)) ? true : false;
            return ($r1 && $r2 && $r3) ? true : false;
        } else {
            return false;
        }
    }
    static function isRGBA($s)
    {
        //@codingStandardsIgnoreStart
        $reg = '/^rgba\(([0-9]{1,3}),(?: ){0,1}([0-9]{1,3}),(?: ){0,1}([0-9]{1,3}),(?: ){0,1}([0-9]{1}(?:\.[0-9]+){0,1}){1}\)$/';
        //@codingStandardsIgnoreEnd
        $matches = array();
        if (preg_match($reg, $s, $matches)) {
            $r1 = (self::inIntergerRange($matches[1], 0, 255)) ? true : false;
            $r2 = (self::inIntergerRange($matches[2], 0, 255)) ? true : false;
            $r3 = (self::inIntergerRange($matches[3], 0, 255)) ? true : false;
            $r4 = (self::inFloatRange($matches[4], 0, 1)) ? true : false;
            return ($r1 && $r2 && $r3 && $r4) ? true : false;
        } else {
            return false;
        }
    }
    static function isHexadecimal($s)
    {
        $reg = '/^(\#)?([A-Fa-f0-9]){3}(([A-Fa-f0-9]){3})?$/';
        return (preg_match($reg, $s)) ? true : false;
    }
    static function sanitizeHexadecimal($s)
    {
        if (self::isHexadecimal($s)) {
            $reg2 = '/^\#/';
            $s = (preg_match($reg2, $s)) ? $s : '#'.$s;
            return strtoupper($s);
        } else {
            return '';
        }
    }
    static function isHSL($s)
    {
        $reg = '/^hsl\(([0-9]{1,3}),(?: ){0,1}([0-9]{1,3})\%,(?: ){0,1}([0-9]{1,3})\%\)$/';
        $matches = array();
        if (preg_match($reg, $s, $matches)) {
            $r1 = (self::inIntergerRange($matches[1], 0, 360)) ? true : false;
            $r2 = (self::inIntergerRange(substr($matches[2], 0, -1), 0, 100)) ? true : false;
            $r3 = (self::inIntergerRange(substr($matches[3], 0, -1), 0, 100)) ? true : false;
            return ($r1 && $r2 && $r3) ? true : false;
        } else {
            return false;
        }
    }
    static function isHSLA($s)
    {
        //@codingStandardsIgnoreStart
        $reg = '/^hsla\(([0-9]{1,3}),(?: ){0,1}([0-9]{1,3})\%,(?: ){0,1}([0-9]{1,3})\%,(?: ){0,1}([0-9]{1}(?:\.[0-9]+){0,1}){1}\)$/';
        //@codingStandardsIgnoreEnd
        $matches = array();
        if (preg_match($reg, $s, $matches)) {
            $r1 = (self::inIntergerRange($matches[1], 0, 360)) ? true : false;
            $r2 = (self::inIntergerRange(substr($matches[2], 0, -1), 0, 100)) ? true : false;
            $r3 = (self::inIntergerRange(substr($matches[3], 0, -1), 0, 100)) ? true : false;
            $r4 = (self::inFloatRange($matches[4], 0, 1)) ? true : false;
            return ($r1 && $r2 && $r3 && $r4) ? true : false;
        } else {
            return false;
        }
    }
    static function isKeyword($s)
    {
        if (is_string($s) && preg_match('/^[a-zA-Z]*$/', $s)) {
            $r1 = (in_array($s, self::$_htmlKeywords)) ? true : false;
            $r2 = (in_array($s, self::$_x11Keywords)) ? true : false;
            $r3 = (in_array($s, self::$_css2SystemKeywords)) ? true : false;
            return ($r1 || $r2 || $r3) ? true : false;
        } else {
            return false;
        }
    }
    static function isKeywordLoose($s)
    {
        return (is_string($s) && preg_match('/^[a-zA-Z0-9\_\-]*$', $s)) ? true : false;
    }
    static function determineColor($s)
    {
        if (self::isHexadecimal($s)) {
            return 'Hex';
        } else if (self::isRGB($s)) {
            return 'RGB';
        } else if (self::isRGBA($s)) {
            return 'RGBA';
        } else if (self::isHSL($s)) {
            return 'HSL';
        } else if (self::isHSLA($s)) {
            return 'HSLA';
        } else if (self::isKeyword($s)) {
            return 'Keyword';
        }
        return false;
    }
    static function sanitize($s, $strict = true)
    {
        $type = self::determineColor($s);
        if (is_string($s) && $type !== false) {
            switch ($type) {
                case '':
                    break;
                case 'Hex':
                    return self::sanitizeHexadecimal($s);
                default:
                    return $s;
            }
        }
        if ($strict === false || strtoupper($strict) === 'LOOSE') {
            return (self::isKeywordLoose($s)) ? $s : self::$default;
        } else {
            return self::$default;
        }
    }
}
/*
echo CSSColorString::sanitize('000000').' for 000000';
echo CSSColorString::sanitize('#000000').' for #000000';
echo CSSColorString::sanitize('rgb(0,0,0)').' for rgb(0,0,0)';
echo CSSColorString::sanitize('rgb(255, 250, 250)').' for rgb(255, 250, 250)';
echo CSSColorString::sanitize('rgba(255,255,255,1)').' for rgba(255,255,255,1)';
echo CSSColorString::sanitize('rgba(255,255,255,1.0)').' for rgba(255,255,255,1.0)';
echo CSSColorString::sanitize('hsl(255,80%,80%)').' for hsl(255,80%,80%)';
echo CSSColorString::sanitize('hsla(255,80%,80%,1.0)').' for hsla(255,80%,80%,1.0)';
echo CSSColorString::sanitize('lime').' for lime';
 */
/*
function resolveGetColors($arr)
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
}
*/
//resolveGetColors();
