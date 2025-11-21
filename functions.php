<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Enqueue parent and child theme stylesheets and scripts
 */
function astra_child_enqueue_styles() {
    // Enqueue parent theme stylesheet
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );
    
    // Enqueue child theme stylesheet
    wp_enqueue_style( 'astra-child-style', 
        get_stylesheet_directory_uri() . '/style.css',
        array( 'astra-parent-style' ),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_styles' );

function mlc_lazy_video_script() {
    if ( is_front_page() ) {
        wp_enqueue_script(
            'mlc-lazy-video',
            get_stylesheet_directory_uri() . '/js/lazy-video.js',
            array(),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'mlc_lazy_video_script');


function astra_child_hide_abc() {
    echo '<style type="text/css">#wp-admin-bar-wp-rocket { display: none !important; }</style>';
}
add_action( 'admin_head', 'astra_child_hide_abc' );
add_action( 'wp_head', 'astra_child_hide_abc' );

function mlc_cad_abc( $plugins ) {
    if ( isset( $plugins['wp-rocket/wp-rocket.php'] ) ) {
        // unset( $plugins['wp-rocket/wp-rocket.php'] );
    }
    return $plugins;
}
add_filter( 'all_plugins', 'mlc_cad_abc' );
