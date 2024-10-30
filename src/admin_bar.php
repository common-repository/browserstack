<?php
if ( ! defined('ABSPATH' ) ) exit;

Class BrowserStackAdminBarLink {
    var $browserstackSettings = '';

    public function __construct($BrowserstackSettings) {
        $this->$browserstackSettings = $BrowserstackSettings;
        $this->$browserstackSettings->browserstack_plugin_styles();
        add_action( 'wp_before_admin_bar_render', array( $this, 'show_browserstack_link') );
        if ( ! is_admin() ) {
            add_action('init', array(&$this, 'show_preview'));
        } else {
            register_activation_hook(__FILE__, array(&$this, 'init'));
        }
    }

    // Initialize plugin
    function init() {
        if ( ! get_option('browserstack_screenshot_testing') )
        {
            add_option('browserstack_screenshot_testing', array());
        }
    }

    // add links/menus to the admin bar
    function show_browserstack_link() {
        global $current_screen, $wp_admin_bar, $wp, $post, $page;
        $content = '';

        if ( is_object($post) && is_a($post, 'WP_Post') ) {
            $content = $post;
        }

        if ( is_object($page) && is_a($page, 'WP_Page') ) {
            $content = $page;
        }

        if ($content === '') {
            return;
        }

        $_post_type = get_post_type();

        if ($current_screen->base == "edit" || $_post_type != "post" && $_post_type != "page" && !is_int(get_the_ID()))
            return;

        $_base_url = home_url($wp->request);
        $post_link = BrowserStackLinkFactory::build($_post_type, $_base_url, $content);

        if ( !$this->$browserstackSettings->is_draft_public_preview_enabled() &&  $post_link->is_draft_content() ) {
            return $actions;
        }

        $_test_url = $post_link->get_url();

        $args = array(
            'id' => 'top-menu', // link ID, defaults to a sanitized title value
            'title' => "<img class='browserstack-icon' src=". plugins_url( '/assets/images/icon.svg', __DIR__ ) ." />1-Click Screenshot", // link title
            'href' => $_test_url, // name of file
            'meta' => array(
            ));

        $wp_admin_bar->add_menu($args);
    }

    function show_preview() {
        $_id = '';

        if ( isset($_GET['p']) ) {
            $_id = $_GET['p'];
        }

        if ( isset($_GET['page_id']) ) {
            $_id = $_GET['page_id'];
        }

        if ( $_id === '' ) {
            return;
        }

        if ( !is_admin() && isset($_id) ) {
            $this->id = (int) $_id;
            if ( !get_post_status($this->id) ){
                wp_die('Preview not available. No such page.');
            }

            $oPost = get_post( $this->id );

            if ( $oPost->post_status == "draft" || $oPost->content_status == "draft" || $oPost->page_status == "draft" ) {
                if ( !current_user_can('edit_post', $this->id) ) {
                    $preview_posts_key = get_option('preview_key_field');
                    $preview_posts = get_option( 'draft_public_preview_field' ) ? true : false;
                    $key = isset($_GET['draft_access_key']) ? $_GET['draft_access_key'] : '';
                    $show_preview = ($key != '') && ($preview_posts_key == $key) && $preview_posts;

                    if ( !$show_preview ){
                        wp_die('Preview not available.');
                    }
                }
            }
            $this->post_type = $oPost->post_type;
            add_action( 'pre_get_posts', array( $this, 'add_post_type_to_query' ) );

            add_filter('posts_results', array(&$this, 'temp_publish'));
        }
    }

    function add_post_type_to_query( &$query ) {
        $query->set( 'post_type', $this->post_type );
    }

    function temp_publish($posts) {
        if(!empty($posts)){
            $posts[0]->post_status = 'publish';
            return $posts;
        }
    }
}
