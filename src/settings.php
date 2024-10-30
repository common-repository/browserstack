<?php
if ( ! defined('ABSPATH' ) ) exit;
Class BrowserstackSettings {
    public function create_plugin_settings_page() {
        // Add the menu item and page
        $page_title = 'BrowserStack Settings Page';
        $menu_title = 'BrowserStack';
        $capability = 'manage_options';
        $slug = 'browserstack_settings';
        $callback = array( $this, 'plugin_settings_page_content' );
        $icon = plugins_url( '/assets/images/icon.svg', __DIR__ );
        $position = 100;

        // add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
        add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
    }

    public function plugin_settings_page_content() { ?>
        <div class="wrap browserstack-wrap">
            <div class="bs-logo">
              <img src="<?php echo plugin_dir_url( __DIR__ ) . '/assets/images/logo.svg'; ?>" alt="BrowserStack">
            </div>
            <h2 class="browserstack-title">Settings</h2>
            <form method="POST" action="options.php">
                <?php
                    settings_fields( 'browserstack_settings' );
                    do_settings_sections( 'browserstack_settings' );
                    submit_button();
                ?>
            </form>
        </div> <?php
    }

    public function load_plugin_scripts($hook) {
      //Load stylesheet to only browserstack_settings page
      if ($hook === 'toplevel_page_browserstack_settings') {
        wp_register_style( 'browserstack-setting-style', plugins_url( '/assets/css/settings.css', __DIR__ ), false,   '1.0' );
        wp_enqueue_style ( 'browserstack-setting-style' );
      }
    }

    public function setup_sections() {
      add_settings_section( 'preview_key_section', '', array( $this, 'section_callback' ), 'browserstack_settings' );
    }

    public function section_callback( $arguments ) {
        switch( $arguments['id'] ){
            case 'preview_key_section':
                ?> <div class='sub-text'>1-Click Screenshot Settings. You can change your access key and allow screenshot testing on draft pages.</div> <?php
                break;
        }
    }

    public function setup_fields() {
        add_settings_field( 'draft_public_preview_field', 'Perform 1-Click responsive testing of draft pages ', array( $this, 'draft_public_preview_field_callback' ), 'browserstack_settings', 'preview_key_section', array( 'class' => 'one-click-checkbox' ) );
        register_setting( 'browserstack_settings', 'draft_public_preview_field' );

        add_settings_field( 'preview_key_field', 'Draft page access key', array( $this, 'preview_key_field_callback' ), 'browserstack_settings', 'preview_key_section', array( 'class' => 'access-key' ) );
        if ( get_option( 'preview_key_field' ) === false ) {
            update_option( 'preview_key_field', bin2hex(openssl_random_pseudo_bytes(8)) );
        }
        if ( $this->is_draft_public_preview_enabled() === false ) {
            update_option( 'draft_public_preview_field', 1 );
        }
        register_setting( 'browserstack_settings', 'preview_key_field' );
    }

    public function draft_public_preview_field_callback( $arguments ) {
        $checked = $this->is_draft_public_preview_enabled() ? 'checked' : '';
        echo '<input type="checkbox" id="draft_public_preview_field" name="draft_public_preview_field" value="draft_public_preview_field" '. $checked .'>';
    }

    public function is_draft_public_preview_enabled(){
        return get_option( 'draft_public_preview_field' );
    }

    public function preview_key_field_callback( $arguments ) {
        echo '<input name="preview_key_field" id="preview_key_field" type="text" value="' . get_option( 'preview_key_field' ) . '" />';
    }

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
        add_action( 'admin_init', array( $this, 'setup_sections' ) );
        add_action( 'admin_init', array( $this, 'setup_fields' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'load_plugin_scripts' ) );

        if ( ! is_admin() ) {
            add_action('init', array(&$this, 'show_preview'));
        } else {
            register_activation_hook(__FILE__, array(&$this, 'init'));
        }
    }

    function browserstack_plugin_styles() {
        wp_register_style( 'browserstack-main-style', plugins_url('../assets/css/main.css', __FILE__ ), false,   '1.0' );
        wp_enqueue_style ( 'browserstack-main-style' );
    }

    // Initialize plugin
    function init() {
        if ( ! get_option('browserstack_screenshot_testing') )
        {
            add_option('browserstack_screenshot_testing', array());
        }
    }

}
