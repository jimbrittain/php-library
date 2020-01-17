<?php
if (!class_exists('IMGlobalCustomFieldSettings')) {
    class IMGlobalCustomFieldSettings
    {
        public $groupname = '';
        public $pagename = '';
        public function __construct()
        {
            $this->groupname = IMGlobalCustomFieldPlugin::$groupname;
            $this->pagename = IMGlobalCustomFieldPlugin::$pagename;
            add_action('admin_init', array(&$this, 'adminInit'));
            add_action('admin_menu', array(&$this, 'adminMenu'));
        }
        public function viewHelpText()
        {
            $str = '';
            $str .= '<p>These allow you to set global options for your website in the same place, 
                      you can include these settings in your content using [globalcf field="{name}"].</p>';
            echo $str;
        }
        public function adminInit()
        {
            add_settings_section(
                $this->pagename.'-template-section',
                'IM Global Custom Fields', 
                array(&$this, 'viewHelpText'),
                $this->pagename
            );
            foreach (globalfield_array() as $gcf) {
                add_settings_field(
                    $gcf->prefixedName(),
                    $gcf->prefixedName(),
                    array($gcf, 'view_editHTML'),
                    $this->pagename,
                    $this->pagename.'-template-section',
                    array(
                        'field' => $gcf->name,
                    )
                );
                register_setting($this->groupname, $gcf->prefixedName(), array($gcf, 'sanitizeValue'));
            }
        }
        public function adminMenu()
        {
            if (is_admin()) {
            //if (count(globalfield_array()) > 0 && is_admin()) {
                add_options_page(
                    'Global Custom Fields',
                    'IM Global Custom Fields', 
                    'manage_options', 
                    $this->pagename, 
                    array(&$this, 'pluginSettingsPage')
                );
            }
        }
        public function pluginSettingsPage()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            } else {
                echo '<div class="wrap">
                    <h2>IM Global Custom Fields </h2>
                        <form action="options.php" method="post">';
                settings_fields($this->groupname);
                do_settings_fields($this->pagename, $this->groupname);
                do_settings_sections($this->pagename);
                if (count(globalfield_array()) > 0) {
                    submit_button();
                } else {
                    //@codingStandardsIgnoreStart
                    echo "<p>You currently have <strong>no defined Global Custom Fields</strong> to alter</p>";
                    echo "<p>You can define these in your themes function.php using the code createGlobalField(\$name).</p>";
                    //@codingStandardsIgnoreEnd
                }
                echo '</form></div>';
            }
        }
    }
}
