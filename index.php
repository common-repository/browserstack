<?php
if ( ! defined('ABSPATH' ) ) exit;
/**
 * @package BrowserStack
 * @version 1.2.0
 */
/*
Plugin Name: BrowserStack
Plugin URI: https://wordpress.org/plugins/browserstack
Description: This plugin enables the integrations of BrowserStack products for testing your content on real devices.
Author: BrowserStack
Version: 1.2.0
Author URI: https://browserstack.com
*/

include_once 'src/admin_bar.php';
include_once 'src/link.php';
include_once 'src/settings.php';

class BrowserStackPostRowActions {
    public function __construct() {
        add_filter( 'post_row_actions', array($this, 'post_action_row'), 10, 2 );
    }

    function post_action_row($actions, $post) {
        $post_link = new BrowserStackPostLink(get_site_url(), $post);
        global $BrowserstackSettings;
        if ( !$BrowserstackSettings->is_draft_public_preview_enabled() &&  $post_link->is_draft_content() ) {
            return $actions;
        }
        $actions['browserstack_test'] = "<a target='_blank' rel='noopener' class='browserstack-integration-action' href='". $post_link->get_url() ."'><img class='browserstack-icon' src=". plugins_url( '/assets/images/icon.svg', __FILE__ ) ." />1-Click Screenshot</a>";
        return $actions;
    }
}

class BrowserStackPageRowActions {
    public function __construct() {
        add_filter( 'page_row_actions', array($this, 'page_action_row'), 10, 2 );
    }

    function page_action_row($actions, $page) {
        $page_link = new BrowserStackPageLink(get_site_url(), $page);
        global $BrowserstackSettings;
        if ( !$BrowserstackSettings->is_draft_public_preview_enabled() &&  $page_link->is_draft_content() ) {
            return $actions;
        }
        $actions['browserstack_test'] = "<a target='_blank' rel='noopener' class='browserstack-integration-action' href='". $page_link->get_url() ."'><img class='browserstack-icon' src=". plugins_url( '/assets/images/icon.svg', __FILE__ ) ." />1-Click Screenshot</a>";
        return $actions;
    }
}

$BrowserstackSettings = new BrowserstackSettings();
$BrowserstackAdminBarLink = new BrowserstackAdminBarLink($BrowserstackSettings);

add_action( 'current_screen', 'browserstack_determine_screen' );
add_action( 'admin_enqueue_scripts', 'browserstack_plugin_styles' );

function browserstack_plugin_styles() {
    global $BrowserstackSettings;
    $BrowserstackSettings->browserstack_plugin_styles();
}

function browserstack_determine_screen() {
    global $current_screen;

    if ( $current_screen->post_type == "post" ) {
        $browserStackPostRowActions = new BrowserStackPostRowActions();
    } elseif ( $current_screen->post_type == "page" ) {
        $browserStackPageRowActions = new BrowserStackPageRowActions();
    }
}
