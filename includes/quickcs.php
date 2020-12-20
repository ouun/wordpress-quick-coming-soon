<?php
/**
 * Config for Landing Page
 *
 */

/**
 * Landing Page Redirect
 */
function quickcs_redirect_requirements()
{
    // Check conditions first
    if (!is_user_logged_in() && !wp_doing_ajax() && !defined('WP_CLI')) {
        if ('/coming-soon/' != $_SERVER['REQUEST_URI'] &&
            '/xmlrpc.php' != $_SERVER['REQUEST_URI'] &&
            !in_array($GLOBALS['pagenow'], apply_filters('quickcs_whitelabel_slugs', array( 'wp-login.php', 'wp-register.php' )), true)
        ) {
            wp_safe_redirect(trailingslashit('/coming-soon'));
            exit;
        }
    }
}
add_action('init', 'quickcs_redirect_requirements');

/**
 * Create New Coming Soon Page on plugin activation (if one does not exist)
 * From https://clicknathan.com/web-design/automatically-create-pages-wordpress/
 */
function quickcs_slug_exists($post_name)
{
    global $wpdb;

    if ($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A')) {
        return true;
    } else {
        return false;
    }
}

function quickcs_create_landing_page()
{

    $quickcs_page_created = get_option('quickcs_page_created');

    /**
     * Checking that we have an options value set, this means the plugin has created a page
     */
    if (!empty($quickcs_page_created)) {
        $post_check = get_post($quickcs_page_created);

        /**
         * We have manually deleted the page, and we will remove the
         * option here
         */
        if (false === $post_check) {
            delete_option('quickcs_page_created');
        } else {

            /**
             * The plugin has already created this post but it's not published
             * Let's publish it again
             */
            if ('publish' != get_post_status($quickcs_page_created)) {
                $quickcs_update = array(
                    'ID'           => $quickcs_page_created,
                    'post_status'   => 'publish',
                );

                wp_update_post($quickcs_update);
            }
        }
    }

    $quickcs_page = array(
        'post_type' => 'page',
        'post_title' => __('Coming Soon', 'quick-cs'),
        'post_status' => 'publish',
        'post_author' => 1,
        'post_name' => 'coming-soon',
        'post_content' => 'This website is coming soon!'
    );

    /**
     * No page slug or set option exists
     * Let's make a new page and publish it!
     */
    if (!quickcs_slug_exists('coming-soon') && !$quickcs_page_created) {
        $quickcs_page_id = wp_insert_post($quickcs_page);
        update_option('quickcs_page_created', $quickcs_page_id);
    }
    /**
     * Page slug exists but option does not, probably user-created page, still working on this
     */
    // elseif( quickcs_slug_exists( 'coming-soon' ) && !$quickcs_page_created ) {

    //  $quickcs_page_id = get_page_by_path( 'coming-soon' )->ID;
    //  update_option( 'quickcs_page_created', $quickcs_page_id );
    // }
}

/**
 * Force plugin-created post to Draft on plugin deactivation
 */
function quickcs_unpublish_page_on_deactivation()
{

    $quickcs_page_id = get_option('quickcs_page_created');

    if ($quickcs_page_id) {
        $quickcs_update = array(
            'ID'           => $quickcs_page_id,
            'post_status'   => 'trash',
        );

        wp_update_post($quickcs_update);
    }
}

/**
 * Prevent people from changing the slug on page view
 * From http://wordpress.stackexchange.com/questions/31627/removing-edit-permalink-view-custom-post-type-areas
 *
 * @param string $return
 * @param int $post_id
 *
 * @return string|string[]|null
 */
function quickcs_hide_edit_permalink(string $return, int $post_id)
{

    if (get_option('quickcs_page_created') == $post_id) {
        $ret2 = preg_replace('/<span id="edit-slug-buttons">.*<\/span>|<span id=\'view-post-btn\'>.*<\/span>/i', '', $return);
    }

    return $ret2;
}

add_filter('get_sample_permalink_html', 'quickcs_hide_edit_permalink', '', 4);
