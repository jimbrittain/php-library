<?php
function getGlobalField($name)
{
    globalfield_array();
    if (count(globalfield_array()) > 0) {
        foreach (globalfield_array() as $gcf) {
            if ($gcf->name == $name) {
                return $gcf->getValue();
            }
        }
    }
    return false;
}
function setGlobalField($name, $value)
{
    globalfield_array();
    if (count(globalfield_array()) > 0) {
      foreach (globalfield_array() as $gcf) {
        if ($gcf->name == $name) {
          return $gcf->setValue($value);
        }
      }
    }
    return false;
}
function createGlobalField($name, $type = '', $default = '')
{
    globalfield_array();
    $a = new GlobalCustomField($name, $type, $default);
    if ($a->valid) {
      array_push(globalfield_array(), $a);
      return true;
    } else {
      return false;
    }
}
function destroyGlobalField($name)
{
    globalfield_array();
    for ($i = 0, $imax = count(globalfield_array()); $i < $imax; $i += 1) {
        $a = globalfield_array()[$i];
        if ($a->name == $name) {
            $a->removeValue();
            return true;
        }
    }
    return false;
}
