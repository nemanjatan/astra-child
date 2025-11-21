(function() {

    let loaded = false;

    function loadVideos() {
        if (loaded) return;
        loaded = true;

        document.querySelectorAll('.lazy-video-poster').forEach(img => {
            const mp4 = img.dataset.video;
            if (!mp4) return;

            const video = document.createElement('video');
            video.muted = true;
            video.loop = true;
            video.autoplay = true;
            video.playsInline = true;
            video.preload = 'none';
            video.src = mp4;
            video.style.width = '100%';
            video.style.height = '300px';
            video.style.objectFit = 'cover';
            video.className = 'lazy-loaded-video';

            img.replaceWith(video);
        });
    }

    ['scroll', 'click', 'mousemove', 'touchstart'].forEach(ev => {
        window.addEventListener(ev, loadVideos, { once: true, passive: true });
    });

})();
