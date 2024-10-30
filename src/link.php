<?php
if ( ! defined('ABSPATH' ) ) exit;
/**
 * BrowserStackLink
 * Create the direct link to browserstack for testing
 * Makes sure we create the link to the right content by
 * inforcing the creation in child classes of post and page
 */

abstract class BrowserStackLink{
    /**
    * Base URL
    * @since 5.1.1
    * @var string
    */
    public $content_url;

    /**
    * Page / Post, i.e. Content
    * @since 5.1.1
    * @var WP_Post
    */
    public $content;

    public function __construct($content_url, $content) {
        $this->content_url = $content_url;
        $this->content = $content;
        $this->content_id = $content->ID;
        $this->content_status = $content->post_status;
    }

    public function get_url(){
        $query_params = http_build_query( array( 'url' => $this->build_bs_url_params() ) );
        return $this->base_browserstack_url() . '?' . $query_params;
    }

    public function is_draft_content(){
        $preview = get_query_var('preview');
        return ( $this->content_status == 'draft' || $preview );
    }

    abstract protected function content_param_key();

    protected function base_browserstack_url() {
        return getenv("BROWSERSTACK_BASE_PATH") ?: "https://www.browserstack.com/screenshots/wordpress";
    }

    protected function build_bs_url_params() {
        $bs_params = array(
            $this->content_param_key() => $this->content_id // get the content key based off the class that inherits this
        );

        if ( $this->is_draft_content() ) {
            $bs_params['draft_access_key'] = get_option('preview_key_field'); // to match with the private key and verify request is only by admin/owner of the content
        }

        $url = trim( $this->content_url ) . '?' . http_build_query($bs_params);
        return rawurlencode( $url );
    }
}

final class BrowserStackPostLink extends BrowserStackLink {
    protected function content_param_key() {
        return "p";
    }
}

final class BrowserStackPageLink extends BrowserStackLink {
    protected function content_param_key() {
        return "page_id";
    }
}

/**
 * BrowserStackLinkFactory
 * implements the factory for building links object for content
 * possible options or type of content: page, post
 */

class BrowserStackLinkFactory {
    public static function build($type, $content_url, $content) {
        switch($type) {
            case 'post':
                $link_object = new BrowserStackPostLink($content_url, $content);
                break;
            case 'page':
                $link_object = new BrowserStackPageLink($content_url, $content);
                break;
            default:
                $link_object = new BrowserStackPostLink($content_url, $content);
                break;
        }
        return $link_object;
    }
}