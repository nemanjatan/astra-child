(function() {
    'use strict';

    let cssLoaded = false;
    let deferredCssUrls = [];

    // Version-agnostic patterns to match CSS files that should be deferred
    const cssPatterns = [
        /bdt-uikit\.css/i,
        /roboto\.css/i,
        /robotoslab\.css/i,
        /opensans\.css/i,
        /\/astra\/assets\/css\/minified\/style\.min\.css/i,
        /\/themes\/astra\/style\.css/i,
        /\/themes\/astra-child\/style\.css/i,
        /e-swiper\.min\.css/i,
        /font-awesome[^\/]*\/css\/all\.min\.css/i,
        /fontawesome\.min\.css/i,
        /brands\.min\.css/i,
        /solid\.min\.css/i,
        /\/cache\/fonts\/.*\/google-fonts\/css\/.*\.css/i,
        /\/cache\/[^\/]+\/[^\/]+\/.*\.css/i, // Catch cached CSS files (like a/a0a33f5….css)
        /\/cache\/.*\/css\/.*\.css/i, // Catch any CSS in cache directories
        /astra-addon-[^\/]*\.css/i,
        /custom-frontend\.min\.css/i,
        /custom-pro-widget-call-to-action\.min\.css/i,
        /custom-pro-widget-nav-menu\.min\.css/i,
        /custom-pro-widget-slides\.min\.css/i,
        /custom-widget-icon-list\.min\.css/i,
        /elementor-icons\.min\.css/i,
        /ep-font\.css/i,
        /ep-helper\.css/i,
        /ep-slider\.css/i,
        /post-\d+\.css/i,
        /swiper\.min\.css/i,
        /transitions\.min\.css/i,
        /fadeIn\.min\.css/i,
        /fadeInUp\.min\.css/i,
        /e-animation-grow\.min\.css/i,
        /v4-shims\.min\.css/i,
        /widget-heading\.min\.css/i,
        /widget-image\.min\.css/i,
        /widget-image-carousel\.min\.css/i,
        /widget-posts\.min\.css/i,
        /widget-search-form\.min\.css/i,
        /widget-spacer\.min\.css/i
    ];

    /**
     * Discover and collect CSS URLs that match our patterns
     * This is version-agnostic - it finds CSS files regardless of version numbers
     */
    function discoverDeferredCSS() {
        const linkTags = document.querySelectorAll('link[rel="stylesheet"]');
        const discoveredUrls = [];

        linkTags.forEach(function(link) {
            const href = link.getAttribute('href') || '';
            
            cssPatterns.forEach(function(pattern) {
                if (pattern.test(href)) {
                    // Extract the full URL (including query strings/versions)
                    const fullUrl = href.startsWith('http') ? href : new URL(href, window.location.origin).href;
                    
                    // Only add if not already in our list
                    if (discoveredUrls.indexOf(fullUrl) === -1) {
                        discoveredUrls.push(fullUrl);
                    }
                    
                    // Remove the link from DOM
                    link.remove();
                }
            });
        });

        return discoveredUrls;
    }

    /**
     * Remove any matching CSS links from the DOM
     * This runs immediately and also periodically to catch CSS added later
     */
    function removeDeferredCSSFromDOM() {
        const linkTags = document.querySelectorAll('link[rel="stylesheet"]');

        linkTags.forEach(function(link) {
            const href = link.getAttribute('href') || '';
            
            cssPatterns.forEach(function(pattern) {
                if (pattern.test(href)) {
                    const fullUrl = href.startsWith('http') ? href : new URL(href, window.location.origin).href;
                    
                    // Store the URL for later loading
                    if (deferredCssUrls.indexOf(fullUrl) === -1) {
                        deferredCssUrls.push(fullUrl);
                    }
                    
                    // Remove the link from DOM
                    link.remove();
                }
            });
        });
    }

    /**
     * Load deferred CSS files after user interaction
     */
    function loadDeferredCSS() {
        if (cssLoaded) {
            return;
        }
        cssLoaded = true;

        // Final discovery pass to catch any CSS we might have missed
        const additionalUrls = discoverDeferredCSS();
        additionalUrls.forEach(function(url) {
            if (deferredCssUrls.indexOf(url) === -1) {
                deferredCssUrls.push(url);
            }
        });

        // Load each deferred CSS file
        deferredCssUrls.forEach(function(url) {
            if (!url) {
                return;
            }

            // Check if this CSS is already loaded
            const existingLink = document.querySelector('link[href="' + url + '"]');
            if (existingLink) {
                return;
            }

            // Create and append link element
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            link.media = 'print';
            
            // Use onload to change media to 'all' once loaded
            link.onload = function() {
                this.media = 'all';
            };

            // Fallback for browsers that don't support onload on link elements
            setTimeout(function() {
                if (link.media === 'print') {
                    link.media = 'all';
                }
            }, 100);

            document.head.appendChild(link);
        });
    }

    // Initial discovery and removal of deferred CSS
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            removeDeferredCSSFromDOM();
            // Check again after a short delay to catch CSS added by other scripts
            setTimeout(removeDeferredCSSFromDOM, 100);
            setTimeout(removeDeferredCSSFromDOM, 500);
        });
    } else {
        removeDeferredCSSFromDOM();
        // Check again after a short delay to catch CSS added by other scripts
        setTimeout(removeDeferredCSSFromDOM, 100);
        setTimeout(removeDeferredCSSFromDOM, 500);
    }

    // Load CSS ONLY on user interaction events (no automatic fallback)
    const events = ['scroll', 'click', 'mousemove', 'touchstart', 'keydown'];
    
    events.forEach(function(eventType) {
        window.addEventListener(eventType, loadDeferredCSS, {
            once: true,
            passive: true
        });
    });

})();

