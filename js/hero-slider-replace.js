(function() {
    'use strict';

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        const staticSection = document.querySelector('.hero-static');
        const sliderSection = document.querySelector('.hero-slider');        

        if (!staticSection || !sliderSection) {
            return;
        }

        sliderSection.style.display = 'none';

        let replaced = false;

        function replaceWithSlider() {
            if (replaced) {
                return;
            }

            replaced = true;

            document.body.classList.add('hero-slider-ready');
            staticSection.style.display = 'none';
            sliderSection.style.display = '';

            if (window.Swiper) {
                const sliderContainer = sliderSection.querySelector('.swiper');
                if (sliderContainer && !sliderContainer.swiper) {
                    window.dispatchEvent(new Event('resize'));
                }
            }

            window.removeEventListener('scroll', handleScroll, { passive: true });
            document.removeEventListener('scroll', handleScroll, { passive: true });
        }

        function handleScroll() {
            replaceWithSlider();
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        document.addEventListener('scroll', handleScroll, { passive: true });

        const interactionEvents = ['click', 'touchstart', 'mousemove', 'keydown'];
        const handleInteraction = function() {
            replaceWithSlider();
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
