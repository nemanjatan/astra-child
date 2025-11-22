(function() {
    'use strict';

    let cssLoaded = false;
    let deferredCssUrls = [];

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
        /\/cache\/[^\/]+\/[^\/]+\/.*\.css/i,
        /\/cache\/.*\/css\/.*\.css/i,
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

    function discoverDeferredCSS() {
        const linkTags = document.querySelectorAll('link[rel="stylesheet"]');
        const discoveredUrls = [];

        linkTags.forEach(function(link) {
            const href = link.getAttribute('href') || '';
            cssPatterns.forEach(function(pattern) {
                if (pattern.test(href)) {
                    const fullUrl = href.startsWith('http') ? href : new URL(href, window.location.origin).href;
                    if (discoveredUrls.indexOf(fullUrl) === -1) {
                        discoveredUrls.push(fullUrl);
                    }
                    link.remove();
                }
            });
        });

        return discoveredUrls;
    }

    function removeDeferredCSSFromDOM() {
        const linkTags = document.querySelectorAll('link[rel="stylesheet"]');
        linkTags.forEach(function(link) {
            const href = link.getAttribute('href') || '';
            cssPatterns.forEach(function(pattern) {
                if (pattern.test(href)) {
                    const fullUrl = href.startsWith('http') ? href : new URL(href, window.location.origin).href;
                    if (deferredCssUrls.indexOf(fullUrl) === -1) {
                        deferredCssUrls.push(fullUrl);
                    }
                    link.remove();
                }
            });
        });
    }

    function loadDeferredCSS() {
        if (cssLoaded) {
            return;
        }
        cssLoaded = true;

        const additionalUrls = discoverDeferredCSS();
        additionalUrls.forEach(function(url) {
            if (deferredCssUrls.indexOf(url) === -1) {
                deferredCssUrls.push(url);
            }
        });

        deferredCssUrls.forEach(function(url) {
            if (!url) {
                return;
            }

            const existingLink = document.querySelector('link[href="' + url + '"]');
            if (existingLink) {
                return;
            }

            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
            setTimeout(function() {
                if (link.media === 'print') {
                    link.media = 'all';
                }
            }, 100);

            document.head.appendChild(link);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            removeDeferredCSSFromDOM();
            setTimeout(removeDeferredCSSFromDOM, 100);
            setTimeout(removeDeferredCSSFromDOM, 500);
        });
    } else {
        removeDeferredCSSFromDOM();
        setTimeout(removeDeferredCSSFromDOM, 100);
        setTimeout(removeDeferredCSSFromDOM, 500);
    }

    const events = ['scroll', 'click', 'mousemove', 'touchstart', 'keydown'];
    events.forEach(function(eventType) {
        window.addEventListener(eventType, loadDeferredCSS, {
            once: true,
            passive: true
        });
    });

})();
