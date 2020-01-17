<?php
    function arrayFlattenSimple($pieces)
    {
        if (!is_array($pieces)) {
          return array($pieces);
        }
        $result = array();
        foreach ($pieces as $v) {
          if (is_array($v)) {
            $result = array_merge($result, array_flatten_simple($v));
          } else {
            $result[] = $v;
          }
        }
      return $result;
    }