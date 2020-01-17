<?php
    function getCurrentUrl()
    {
      $curl = "";
      $curl = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
      $curl = $curl.$_SERVER['SERVER_NAME'];
      $curl = (empty($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] != 80) ? $curl.":".$_SERVER['SERVER_PORT'] : $curl;
      $curl = $curl.$_SERVER['REQUEST_URI'];
      return $curl;
    }