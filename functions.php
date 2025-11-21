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
 * Copy Font Awesome and Elementor icon fonts from plugins to theme
 * This runs once to set up the fonts directory
 */
function mlc_setup_font_files() {
    $theme_fonts_dir = get_stylesheet_directory() . '/fonts/';
    
    // Create fonts directory if it doesn't exist
    if ( ! file_exists( $theme_fonts_dir ) ) {
        wp_mkdir_p( $theme_fonts_dir );
    }
    
    // Possible font file locations (try multiple paths)
    $font_sources = array(
        // Font Awesome fonts - try different possible locations
        'fa-solid-900.woff2' => array(
            WP_PLUGIN_DIR . '/elementor/assets/lib/font-awesome/webfonts/fa-solid-900.woff2',
            WP_PLUGIN_DIR . '/elementor/assets/lib/font-awesome/css/../webfonts/fa-solid-900.woff2',
        ),
        'fa-brands-400.woff2' => array(
            WP_PLUGIN_DIR . '/elementor/assets/lib/font-awesome/webfonts/fa-brands-400.woff2',
            WP_PLUGIN_DIR . '/elementor/assets/lib/font-awesome/css/../webfonts/fa-brands-400.woff2',
        ),
        // Elementor icons
        'eicons.woff2' => array(
            WP_PLUGIN_DIR . '/elementor/assets/lib/eicons/fonts/eicons.woff2',
            WP_PLUGIN_DIR . '/elementor/assets/lib/eicons/css/../fonts/eicons.woff2',
        ),
    );
    
    // Copy fonts if source exists and destination doesn't
    foreach ( $font_sources as $filename => $possible_paths ) {
        $dest = $theme_fonts_dir . $filename;
        
        // Skip if already exists
        if ( file_exists( $dest ) ) {
            continue;
        }
        
        // Try each possible path
        foreach ( $possible_paths as $source ) {
            if ( file_exists( $source ) ) {
                copy( $source, $dest );
                break; // Successfully copied, move to next font
            }
        }
    }
}
// Run on theme activation, admin init, and wp_loaded (to ensure fonts are copied)
add_action( 'after_switch_theme', 'mlc_setup_font_files' );
add_action( 'admin_init', 'mlc_setup_font_files' );
add_action( 'wp_loaded', 'mlc_setup_font_files' );

/**
 * Preload Font Awesome fonts for better performance
 */
function mlc_preload_font_awesome_fonts() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    
    $fonts_dir = get_stylesheet_directory_uri() . '/fonts/';
    
    // Font Awesome 5 Free (Solid) - used by .fa and .fas
    if ( file_exists( get_stylesheet_directory() . '/fonts/fa-solid-900.woff2' ) ) {
        echo '<link rel="preload" href="' . esc_url( $fonts_dir . 'fa-solid-900.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
    
    // Font Awesome 5 Brands - used by .fab
    if ( file_exists( get_stylesheet_directory() . '/fonts/fa-brands-400.woff2' ) ) {
        echo '<link rel="preload" href="' . esc_url( $fonts_dir . 'fa-brands-400.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
    
    // Elementor Icons (eicons) - used by Elementor
    if ( file_exists( get_stylesheet_directory() . '/fonts/eicons.woff2' ) ) {
        echo '<link rel="preload" href="' . esc_url( $fonts_dir . 'eicons.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
}
add_action( 'wp_head', 'mlc_preload_font_awesome_fonts', 0 );

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
            // Replace relative font paths with absolute URLs
            $fonts_url = get_stylesheet_directory_uri() . '/fonts/';
            $critical_css = str_replace(
                array(
                    'url("../fonts/',
                    "url('../fonts/",
                ),
                'url("' . $fonts_url,
                $critical_css
            );
            
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
        '/\/themes\/astra-child\/style\.css/i',
        '/e-swiper\.min\.css/i',
        '/font-awesome[^\/]*\/css\/all\.min\.css/i',
        '/fontawesome\.min\.css/i',
        '/brands\.min\.css/i',
        '/solid\.min\.css/i',
        '/\/cache\/fonts\/.*\/google-fonts\/css\/.*\.css/i',
        '/astra-addon-[^\/]*\.css/i',
        '/custom-frontend\.min\.css/i',
        '/custom-pro-widget-call-to-action\.min\.css/i',
        '/custom-pro-widget-nav-menu\.min\.css/i',
        '/custom-pro-widget-slides\.min\.css/i',
        '/custom-widget-icon-list\.min\.css/i',
        '/elementor-icons\.min\.css/i',
        '/ep-font\.css/i',
        '/ep-helper\.css/i',
        '/ep-slider\.css/i',
        '/post-\d+\.css/i',
        '/swiper\.min\.css/i',
        '/transitions\.min\.css/i',
        '/fadeIn\.min\.css/i',
        '/fadeInUp\.min\.css/i',
        '/e-animation-grow\.min\.css/i',
        '/v4-shims\.min\.css/i',
        '/widget-heading\.min\.css/i',
        '/widget-image\.min\.css/i',
        '/widget-image-carousel\.min\.css/i',
        '/widget-posts\.min\.css/i',
        '/widget-search-form\.min\.css/i',
        '/widget-spacer\.min\.css/i',
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
