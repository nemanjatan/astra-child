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

/**
 * Check if current page is a landing page
 * Modify this function to match your landing page criteria
 */
function mlc_is_landing_page() {
    // Check if it's the front page
    if ( is_front_page() ) {
        return true;
    }
    
    // Add additional checks for landing pages here
    // For example: specific page templates, page IDs, or slugs
    // if ( is_page_template( 'landing-page.php' ) ) {
    //     return true;
    // }
    
    return false;
}

/**
 * Inline critical CSS in the head section
 */
function mlc_inline_critical_css() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    
    $critical_css_path = get_stylesheet_directory() . '/css/mlc-cad-critical.css';
    
    if ( file_exists( $critical_css_path ) ) {
        $critical_css = file_get_contents( $critical_css_path );
        if ( ! empty( $critical_css ) ) {
            // Minify the CSS (remove comments and extra whitespace)
            $critical_css = preg_replace( '/\s+/', ' ', $critical_css );
            $critical_css = preg_replace( '/\/\*.*?\*\//', '', $critical_css );
            $critical_css = trim( $critical_css );
            
            echo '<style id="mlc-critical-css">' . $critical_css . '</style>' . "\n";
        }
    }
}
add_action( 'wp_head', 'mlc_inline_critical_css', 1 );

/**
 * Remove CSS links by URL pattern using style_loader_tag filter
 * This removes the CSS from initial page load
 */
function mlc_remove_css_by_url( $tag, $handle, $href ) {
    if ( ! mlc_is_landing_page() ) {
        return $tag;
    }
    
    // Patterns to match the CSS files we want to defer (version-agnostic)
    $patterns = array(
        '/bdt-uikit\.css/i',
        '/roboto\.css/i',
        '/robotoslab\.css/i',
        '/\/astra\/assets\/css\/minified\/style\.min\.css/i',
        '/\/themes\/astra\/style\.css/i',
        '/font-awesome[^\/]*\/css\/all\.min\.css/i',
        '/fontawesome\.min\.css/i',
        '/\/cache\/fonts\/.*\/google-fonts\/css\/.*\.css/i',
        '/astra-addon-[^\/]*\.css/i',
        '/custom-frontend\.min\.css/i',
        '/custom-pro-widget-call-to-action\.min\.css/i',
        '/custom-pro-widget-nav-menu\.min\.css/i',
        '/custom-widget-icon-list\.min\.css/i',
        '/elementor-icons\.min\.css/i',
        '/ep-font\.css/i',
        '/ep-helper\.css/i',
        '/post-\d+\.css/i',
        '/swiper\.min\.css/i',
        '/transitions\.min\.css/i',
        '/v4-shims\.min\.css/i',
        '/widget-posts\.min\.css/i',
    );
    
    foreach ( $patterns as $pattern ) {
        if ( preg_match( $pattern, $href ) ) {
            return ''; // Return empty string to remove the link tag
        }
    }
    
    return $tag;
}
add_filter( 'style_loader_tag', 'mlc_remove_css_by_url', 10, 3 );

/**
 * Enqueue script to load deferred CSS after user interaction
 */
function mlc_enqueue_deferred_css_loader() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    
    wp_enqueue_script(
        'mlc-deferred-css-loader',
        get_stylesheet_directory_uri() . '/js/deferred-css-loader.js',
        array(),
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'mlc_enqueue_deferred_css_loader' );
