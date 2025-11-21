/**
 * Hero Slider Replacement
 * Replaces static hero section with actual slider on user scroll
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        // Find the static section and slider section
        const staticSection = document.querySelector('.hero-static');
        const sliderSection = document.querySelector('.hero-slider');        

        if (!staticSection || !sliderSection) {
            return; // Sections not found, exit
        }

        // Hide slider initially (CSS already handles this, but ensure it's hidden)
        sliderSection.style.display = 'none';

        // Track if replacement has happened
        let replaced = false;

        // Function to replace static with slider
        function replaceWithSlider() {
            if (replaced) {
                return; // Already replaced
            }

            replaced = true;

            // Add class to body to trigger CSS changes
            document.body.classList.add('hero-slider-ready');

            // Hide static section
            staticSection.style.display = 'none';

            // Show slider section
            sliderSection.style.display = '';

            // Initialize the slider if it exists and hasn't been initialized
            // The slider should auto-initialize when it becomes visible
            // But we can trigger a resize event to ensure it initializes properly
            if (window.Swiper) {
                // Check if slider container exists
                const sliderContainer = sliderSection.querySelector('.swiper');
                if (sliderContainer && !sliderContainer.swiper) {
                    // Slider not initialized yet, trigger initialization
                    // This will be handled by Element Pack's slider initialization
                    // We just need to ensure the container is visible
                    window.dispatchEvent(new Event('resize'));
                }
            }

            // Remove scroll listener after replacement
            window.removeEventListener('scroll', handleScroll, { passive: true });
            document.removeEventListener('scroll', handleScroll, { passive: true });
        }

        // Handle scroll event
        function handleScroll() {
            // Replace on any scroll
            replaceWithSlider();
        }

        // Add scroll listeners
        window.addEventListener('scroll', handleScroll, { passive: true });
        document.addEventListener('scroll', handleScroll, { passive: true });

        // Also replace on other user interactions (click, touch, etc.)
        const interactionEvents = ['click', 'touchstart', 'mousemove', 'keydown'];
        const handleInteraction = function() {
            replaceWithSlider();
            // Remove listeners after first interaction
            interactionEvents.forEach(function(event) {
                window.removeEventListener(event, handleInteraction);
                document.removeEventListener(event, handleInteraction);
            });
        };

        interactionEvents.forEach(function(event) {
            window.addEventListener(event, handleInteraction, { passive: true, once: true });
            document.addEventListener(event, handleInteraction, { passive: true, once: true });
        });
    }
})();

