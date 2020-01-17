<?php

if (!function_exists('is_login_page')) {
  function is_login_page()
  {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
  }
}