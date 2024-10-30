<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// delete setttings options
delete_option('preview_key_field');
delete_option('draft_public_preview_field');
