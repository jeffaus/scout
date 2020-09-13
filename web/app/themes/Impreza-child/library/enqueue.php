<?php

add_action(
    'wp_enqueue_scripts',
    function () {
        // Custom fonts
        wp_enqueue_style('fancybox-css', get_stylesheet_directory_uri() . '/dist/jquery.fancybox.min.css');

        wp_enqueue_script( 'fancybox-js', get_stylesheet_directory_uri() . '/dist/jquery.fancybox.min.js', array('jquery'), '3.5.7', true );

        // CSS Stylesheets.
        spr_enqueue( 'spr-styles', '/dist/css/app.css', 'style', array() );

        // Javascript enqueue
        spr_enqueue( 'app-js', '/dist/js/app.js', 'script', array( 'jquery' ), true );

        wp_localize_script( 'app-js', 'ajax_day', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ));
    }
);


if ( ! function_exists( 'spr_enqueue' ) ) {
    /**
     * Function to make enqueueing scripts and stylesheets easier
     * Adds the last modified time as the version number
     *
     * @param string $handle - Name of the script. Should be unique.
     * @param string $relpath - Full URL of the script, or path of the script relative to the WordPress root directory.
     * @param string $type - style or script.
     * @param array  $deps - Optional. An array of registered script handles this script depends on. Default empty array.
     * @param bool   $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
     */
    function spr_enqueue( $handle, $relpath, $type = 'script', $deps = array(), $in_footer = false ) {
        $uri = get_theme_file_uri( $relpath );
        $vsn = filemtime( get_theme_file_path( $relpath ) );

        if ( 'script' === $type ) {
            wp_enqueue_script( $handle, $uri, $deps, $vsn, $in_footer );

        } elseif ( 'style' === $type ) {
            wp_enqueue_style( $handle, $uri, $deps, $vsn );
        }
    }
}
