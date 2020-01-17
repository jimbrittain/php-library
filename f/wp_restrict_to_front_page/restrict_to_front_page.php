<?php
function restrict_to_front_page()
{
  if (!is_front_page() && !is_admin() && !is_login_page()) {
    wp_redirect(home_url('/index.php'));
    exit;
  }
}
add_action('template_redirect', 'restrict_to_front_page');