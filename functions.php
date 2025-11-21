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

/**
 * Enqueue hero slider replacement script
 * Replaces static hero section with slider on scroll
 */
function mlc_hero_slider_replace_script() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    
    wp_enqueue_script(
        'mlc-hero-slider-replace',
        get_stylesheet_directory_uri() . '/js/hero-slider-replace.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'mlc_hero_slider_replace_script');


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
    // Don't apply optimizations in Elementor edit mode
    if ( mlc_is_elementor_edit_mode() ) {
        return false;
    }
    
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
 * Check if we're in Elementor edit mode
 */
function mlc_is_elementor_edit_mode() {
    // Check if we're in admin and editing with Elementor
    if ( is_admin() && isset( $_GET['action'] ) && $_GET['action'] === 'elementor' ) {
        return true;
    }
    
    // Check if Elementor preview mode is active
    if ( isset( $_GET['elementor-preview'] ) ) {
        return true;
    }
    
    // Check if Elementor editor is active (using Elementor's API if available)
    if ( class_exists( '\Elementor\Plugin' ) ) {
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            return true;
        }
        if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
            return true;
        }
    }
    
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
        // Element Pack icons
        'element-pack.woff2' => array(
            WP_PLUGIN_DIR . '/bdthemes-element-pack/assets/fonts/element-pack.woff2',
        ),
        'element-pack.woff' => array(
            WP_PLUGIN_DIR . '/bdthemes-element-pack/assets/fonts/element-pack.woff',
        ),
        'element-pack.ttf' => array(
            WP_PLUGIN_DIR . '/bdthemes-element-pack/assets/fonts/element-pack.ttf',
        ),
        'element-pack.svg' => array(
            WP_PLUGIN_DIR . '/bdthemes-element-pack/assets/fonts/element-pack.svg',
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
 * Preload critical fonts FIRST to break dependency chain
 * These must load before CSS that references them
 */
function mlc_preload_critical_fonts_early() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    
    $fonts_dir = get_stylesheet_directory_uri() . '/fonts/';
    
    // Preload critical fonts FIRST (before CSS loads)
    // Font Awesome 5 Free (Solid) - used by .fa and .fas
    if ( file_exists( get_stylesheet_directory() . '/fonts/fa-solid-900.woff2' ) ) {
        echo '<link rel="preload" href="' . esc_url( $fonts_dir . 'fa-solid-900.woff2' ) . '" as="font" type="font/woff2" crossorigin fetchpriority="high">' . "\n";
    }
    
    // Elementor Icons (eicons) - used by Elementor
    if ( file_exists( get_stylesheet_directory() . '/fonts/eicons.woff2' ) ) {
        echo '<link rel="preload" href="' . esc_url( $fonts_dir . 'eicons.woff2' ) . '" as="font" type="font/woff2" crossorigin fetchpriority="high">' . "\n";
    }
    
    // Font Awesome 5 Brands - used by .fab
    if ( file_exists( get_stylesheet_directory() . '/fonts/fa-brands-400.woff2' ) ) {
        echo '<link rel="preload" href="' . esc_url( $fonts_dir . 'fa-brands-400.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
    
    // Element Pack Icons - used by Element Pack plugin
    if ( file_exists( get_stylesheet_directory() . '/fonts/element-pack.woff2' ) ) {
        echo '<link rel="preload" href="' . esc_url( $fonts_dir . 'element-pack.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
}
// Use negative priority to ensure this runs FIRST, before other head actions
add_action( 'wp_head', 'mlc_preload_critical_fonts_early', -10 );

/**
 * Add preconnect hints for critical third-party domains
 */
function mlc_add_preconnect_hints() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    echo '<link rel="preconnect" href="https://mlccadsystems.us-6.evergage.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    // Uncomment if needed: echo '<link rel="preconnect" href="https://i.simpli.fi" crossorigin>' . "\n";
}
add_action( 'wp_head', 'mlc_add_preconnect_hints', 0 );

/**
 * Preload LCP image for faster loading
 * Includes imagesrcset and imagesizes for responsive image preloading
 * Uses WebP format for better compression and faster loading
 */
function mlc_preload_lcp_image() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    
    // Preload the LCP image (static hero section image)
    // This is the SOLIDWORKS promo image that appears in the static hero section
    // Use WebP format for better compression and faster loading
    // Include srcset and sizes to preload the correct responsive variant
    echo '<link rel="preload" as="image"
        href="https://www.mlc-cad.com/wp-content/uploads/2025/11/MLC-CAD-Systems-SOLIDWORKS-PROMO-30th-Anniversary-Discount-5-desktop.webp"
        imagesrcset="
            https://www.mlc-cad.com/wp-content/uploads/2025/11/MLC-CAD-Systems-SOLIDWORKS-PROMO-30th-Anniversary-Discount-5-desktop.webp 1920w,
            https://www.mlc-cad.com/wp-content/uploads/2025/11/MLC-CAD-Systems-SOLIDWORKS-PROMO-30th-Anniversary-Discount-5-desktop.webp 1536w
        "
        imagesizes="(max-width: 1920px) 100vw, 1920px"
        fetchpriority="high"
    >' . "\n";
}
add_action( 'wp_head', 'mlc_preload_lcp_image', 0 );


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
    
    // Add CSS to hide slider initially and show static section
    // This provides a fallback if JavaScript hasn't loaded yet
    echo '<style id="mlc-hero-slider-css">
        /* Hide slider section initially */
        .hero-slider,
        .elementor-element-2696e17 {
            display: none !important;
        }
        /* Show static section initially */
        .hero-static,
        .elementor-element-5b93419 {
            display: block !important;
        }
        /* When slider is ready, show it and hide static */
        .hero-slider-ready .hero-slider,
        .hero-slider-ready .elementor-element-2696e17 {
            display: block !important;
        }
        .hero-slider-ready .hero-static,
        .hero-slider-ready .elementor-element-5b93419 {
            display: none !important;
        }
        /* Ensure LCP image in static hero section loads with high priority */
        .hero-static img,
        .elementor-element-5b93419 img {
            content-visibility: auto;
        }
    </style>' . "\n";
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
        '/opensans\.css/i',
        '/\/astra\/assets\/css\/minified\/style\.min\.css/i',
        '/\/themes\/astra\/style\.css/i',
        '/\/themes\/astra-child\/style\.css/i',
        '/e-swiper\.min\.css/i',
        '/font-awesome[^\/]*\/css\/all\.min\.css/i',
        '/fontawesome\.min\.css/i',
        '/brands\.min\.css/i',
        '/solid\.min\.css/i',
        '/\/cache\/fonts\/.*\/google-fonts\/css\/.*\.css/i',
        '/\/cache\/[^\/]+\/[^\/]+\/.*\.css/i', // Catch cached CSS files (like a/a0a33f5….css)
        '/\/cache\/.*\/css\/.*\.css/i', // Catch any CSS in cache directories
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

/**
 * Remove render-blocking CSS from HTML output using output buffering
 * This catches CSS files that might bypass the style_loader_tag filter
 */
function mlc_remove_render_blocking_css_output( $buffer ) {
    if ( ! mlc_is_landing_page() ) {
        return $buffer;
    }
    
    // Remove opensans.css
    $buffer = preg_replace(
        '/<link[^>]*href=["\'][^"\']*opensans\.css[^"\']*["\'][^>]*>/i',
        '',
        $buffer
    );
    
    // Remove cached CSS files with hash-like names (like /cache/a/a0a33f5….css)
    // Pattern: /cache/[single-char]/[hash].css
    $buffer = preg_replace(
        '/<link[^>]*href=["\'][^"\']*\/cache\/[a-z0-9]+\/[a-f0-9]+[^"\']*\.css[^"\']*["\'][^>]*>/i',
        '',
        $buffer
    );
    
    return $buffer;
}

/**
 * Start output buffering to catch and remove render-blocking CSS
 */
function mlc_start_output_buffer() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    ob_start( 'mlc_remove_render_blocking_css_output' );
}
add_action( 'template_redirect', 'mlc_start_output_buffer', 1 );

/**
 * End output buffering
 */
function mlc_end_output_buffer() {
    if ( ! mlc_is_landing_page() ) {
        return;
    }
    if ( ob_get_level() > 0 ) {
        ob_end_flush();
    }
}
add_action( 'wp_footer', 'mlc_end_output_buffer', 999 );

/**
 * Add fetchpriority="high" to LCP image in static hero section
 */
function mlc_add_fetchpriority_to_lcp_image( $html, $context, $attachment_id ) {
    if ( ! mlc_is_landing_page() ) {
        return $html;
    }
    
    // Add fetchpriority="high" to the LCP image (attachment ID 86690)
    if ( $attachment_id == 86690 ) {
        // Check if fetchpriority is not already present
        if ( strpos( $html, 'fetchpriority' ) === false ) {
            $html = str_replace( '<img ', '<img fetchpriority="high" ', $html );
        }
    }
    
    return $html;
}
add_filter( 'wp_content_img_tag', 'mlc_add_fetchpriority_to_lcp_image', 10, 3 );

/**
 * Add fetchpriority="high" to images in static hero section via output buffering
 * This catches images that might not go through wp_content_img_tag filter
 */
function mlc_add_fetchpriority_to_hero_images( $content ) {
    if ( ! mlc_is_landing_page() || is_admin() ) {
        return $content;
    }
    
    // Add fetchpriority="high" to images in the static hero section
    // Match images within the hero-static section or elementor-element-5b93419
    $content = preg_replace_callback(
        '/(<section[^>]*class="[^"]*(?:hero-static|elementor-element-5b93419)[^"]*"[^>]*>.*?)(<img\s+)([^>]*>)/is',
        function( $matches ) {
            $img_tag = $matches[2] . $matches[3];
            // Check if fetchpriority is not already present
            if ( strpos( $img_tag, 'fetchpriority' ) === false ) {
                $img_tag = str_replace( '<img ', '<img fetchpriority="high" ', $img_tag );
            }
            return $matches[1] . $img_tag;
        },
        $content,
        1 // Limit to first match (the static hero section)
    );
    
    return $content;
}
add_filter( 'the_content', 'mlc_add_fetchpriority_to_hero_images', 999 );
