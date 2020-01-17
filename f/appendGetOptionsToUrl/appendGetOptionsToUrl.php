<?php
  //though not defined due to php's limitations, or my abilities, any string, or array following the third
  //argument will be treated as exclusion keys. 
  //$priority is defined as the new one receiving priority, unless, the third argument is a string "existing"
    function returnHashed($str)
    {
      return ((strpos($str, '#') == true) ? substr($str, strpos($str, '#')) : "");
    };
    function removeHashed($str)
    {
      return ((strpos($str, '#') == true) ? substr($str, 0, strpos($str, '#')) : $str);
    }
    function returnGet($str)
    {
      return ((strpos($str, '?') == true) ? substr($str, (strpos($str, '?') + 1)) : "");
    }
    function removeGet($str)
    {
      return ((strpos($str, '?') == true) ? substr($str, 0, strpos($str, '?')) : $str);
    }

    function returnPriority($existing, $new, $priority="in")
    {
      switch ($priority){
        case "existing":
            return $existing;
            break;
        default:
            return $new;
            break;
      }
    }
    function returnGetKeyArray($str)
    {
      $arrayStart = explode("&amp;", $str);
      $objectArray = array();
      $getArray = array();
      foreach ($arrayStart as $temp) {
        $objectParts = explode("=", $temp);
        if (isset($objectParts[0])) {
          $arrayIdentifier = urldecode($GLOBALS['arrayIdentifier']);
          $keyName = urldecode($objectParts[0]);
          if (strrpos($keyName, $arrayIdentifier) !== false) {
            if (strrpos($keyName, $arrayIdentifier) == strlen($keyName) - strlen($arrayIdentifier)) {
              $keyName = substr($keyName, 0, (strlen($keyName) - strlen($arrayIdentifier)));
            }
          }
          $keyName = urlencode($keyName);
          $getArray[$keyName] = (!key_exists($keyName, $getArray)) ? array() : $getArray[$keyName];
          $getArray[$keyName][] = (isset($objectParts[1])) ? $objectParts[1] : "";
        }
      }
      return $getArray;
    }


  function appendGetOptionsToUrl($existingUrl="/", $append="", $priority = "in")
  {
    $url = $existingUrl;
    $anchor = "";
    if (!empty($append)) {
      $anchor = returnHashed($url);
      $url = removeHashed($url);
      if (returnHashed($append) != "") {
        $anchor = returnPriority($anchor, returnHashed($append), $priority);
        $append = removeHashed($append);
      }
      if (strlen($append) > 0) {
        $preget = returnGet($url);
        $url = removeGet($url);
        if (strpos($append, '&amp;') !== false && strpos($append, '&amp;') == 0) {
          $append = substr($append, 5);
        }
        if (strpos($append, '&amp;') === false && strpos($append, '&')) {
          $append = substr($append, 1);
        }
        $preArr = returnGetKeyArray($preget);
        $newArr = returnGetKeyArray($append);
        $append = "";
        $resolvedArr = array();
        foreach ($newArr as $k => $v) {
          if (key_exists($k, $preArr)) {
            $resolvedArr[$k] = returnPriority($preArr[$k], $newArr[$k], $priority);
          } else {
            $resolvedArr[$k] = $newArr[$k];
          }
        }
        foreach ($preArr as $k => $v) {
          if (!key_exists($k, $newArr)) {
            $resolvedArr[$k] = $preArr[$k];
          }
        }
        if (func_num_args() > 3) { // has exclude list;
          $args = func_get_args();
          $args = array_slice($args, 3);
          print_r($args);
          $args = array_flatten_simple($args);
          foreach ($args as $key) {
            if (key_exists($key, $resolvedArr)) {
              unset($resolvedArr[$key]);
            }
          }
        }
        foreach ($resolvedArr as $k => $v) {
          if (strlen($k) > 0) {
            $append = $append."&amp;".$k.$GLOBALS['arrayIdentifier']."=";
            $append = $append.implode(("&amp;".$k.$GLOBALS['arrayIdentifier']."="), $v);
          }
        }
        if (strpos($append, '&amp;') !== false && strpos($append, "&amp;") == 0) {
          $append = substr($append, 5);
        }
        if (((strpos($append, '&amp;') !== false && strpos($append, '&amp;') != 0) || 
         (strpos($append, '&amp;') === false))
         && (strpos($append, '&') !== false) && strpos($append, '&') == 0) {
          $append = substr($append, 1);
        }
      }
    }
    return $url.((!empty($append)) ? "?".$append : "").$anchor;
  }