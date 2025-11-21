# Astra Child Theme - Performance Optimization

This child theme is specifically designed for technical SEO and page speed optimization, implementing critical CSS inlining and deferred CSS loading strategies.

## Features

### 1. Critical CSS Inlining
- Inlines critical CSS from `css/mlc-cad-critical.css` directly in the `<head>` section
- Only applies to home page and landing pages (configurable)
- CSS is automatically minified before inlining
- Ensures above-the-fold content renders immediately without waiting for external stylesheets

### 2. Deferred CSS Loading
Prevents non-critical CSS files from loading during initial page render. These files are loaded only after user interaction (scroll, click, mousemove, touchstart, or keydown).

**Deferred CSS Files:**
- Element Pack UIkit CSS (`bdt-uikit.css`)
- Google Fonts (Roboto, Roboto Slab)
- Astra theme CSS (`style.min.css`)
- Font Awesome CSS files (`all.min.css`, `brands.min.css`, `solid.min.css`, `fontawesome.min.css`, `v4-shims.min.css`)
- Elementor CSS files (`custom-frontend.min.css`, `elementor-icons.min.css`, `swiper.min.css`, etc.)
- Elementor Pro widget CSS files
- Element Pack CSS files (`ep-helper.css`, `ep-font.css`, `ep-slider.css`)
- Astra Addon CSS files
- Post-specific Elementor CSS files (`post-{ID}.css`)
- Elementor animation CSS files (`fadeIn.min.css`, `fadeInUp.min.css`, `e-animation-grow.min.css`)
- And more...

### 3. Font Optimization
- **Font Awesome & Elementor Icons**: Automatically copies font files from Elementor plugin to theme's `/fonts/` directory
- Fonts are preloaded in the `<head>` for faster rendering
- `@font-face` declarations are included in critical CSS with absolute URLs
- Uses `font-display: swap` to prevent invisible text during font load

### 4. Lazy Video Loading
- Lazy loads video elements on the home page
- Videos load only after user interaction (scroll, click, mousemove, touchstart)
- Implemented in `js/lazy-video.js`

## Configuration

### Landing Pages
By default, optimizations apply to the home page (`is_front_page()`). To add additional landing pages, modify the `mlc_is_landing_page()` function in `functions.php`:

```php
function mlc_is_landing_page() {
    if ( is_front_page() ) {
        return true;
    }
    
    // Add your custom checks here
    // Example: if ( is_page_template( 'landing-page.php' ) ) {
    //     return true;
    // }
    
    return false;
}
```

### Adding New CSS Files to Defer
To add additional CSS files to the deferred loading list, add patterns to both:
1. `functions.php` - `mlc_remove_css_by_url()` function
2. `js/deferred-css-loader.js` - `cssPatterns` array

Patterns are version-agnostic and use regex matching.

## File Structure

```
astra-child/
├── css/
│   └── mlc-cad-critical.css    # Critical CSS (inlined in head)
├── fonts/                       # Font files (auto-created)
│   ├── fa-solid-900.woff2
│   ├── fa-brands-400.woff2
│   └── eicons.woff2
├── js/
│   ├── deferred-css-loader.js  # Loads deferred CSS after interaction
│   └── lazy-video.js           # Lazy video loading
├── functions.php                # Main theme functions
├── style.css                    # Child theme stylesheet
└── README.md                    # This file
```

## How It Works

1. **Initial Page Load:**
   - Critical CSS is inlined in `<head>`
   - Font files are preloaded
   - Non-critical CSS files are prevented from loading

2. **User Interaction:**
   - JavaScript detects user interaction (scroll, click, etc.)
   - Discovers deferred CSS files by pattern matching
   - Dynamically loads CSS files with their full URLs (version-agnostic)
   - Uses "print" media trick for non-blocking loading

3. **Font Loading:**
   - Font files are automatically copied from Elementor plugin on first run
   - Fonts are preloaded for immediate availability
   - `@font-face` declarations use absolute URLs for reliability

## Performance Benefits

- **Faster First Contentful Paint (FCP)**: Critical CSS inlined, no render-blocking CSS
- **Faster Largest Contentful Paint (LCP)**: Reduced initial CSS payload
- **Better Core Web Vitals**: Optimized loading strategy improves LCP, FID, and CLS scores
- **Reduced Initial Page Weight**: Non-critical CSS loads only when needed
- **Improved Time to Interactive (TTI)**: Less JavaScript and CSS to parse initially

## Technical Details

- **Version-Agnostic**: CSS file detection uses pattern matching, not hardcoded URLs
- **No Automatic Fallback**: CSS files load ONLY on user interaction (no timeout)
- **Cross-Browser Compatible**: Works in all modern browsers
- **WordPress Best Practices**: Uses proper WordPress hooks and filters

## Maintenance

### Updating Critical CSS
When you update `css/mlc-cad-critical.css`, changes will automatically be reflected on the next page load. The CSS is minified on-the-fly when inlined.

### Font Files
Font files are automatically copied from the Elementor plugin. If fonts are missing:
1. Visit WordPress admin (triggers automatic copy)
2. Or manually copy from `/wp-content/plugins/elementor/assets/lib/font-awesome/webfonts/` to `/wp-content/themes/astra-child/fonts/`

## Notes

- This optimization only applies to home page and landing pages (as defined in `mlc_is_landing_page()`)
- Other pages load CSS normally for full functionality
- All deferred CSS files are loaded after the first user interaction
- Font Awesome icons will display correctly even before deferred CSS loads (thanks to critical CSS)

