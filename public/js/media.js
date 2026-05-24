jQuery(function($) {
    var audio = new Audio();
    var playlist = [];
    var currentIndex = 0;
    var bar = $('.damaris-player-bar');
    var isPlaying = false;
    
    // Click on play button
    $(document).on('click', '.damaris-play-btn', function() {
        var track = $(this).closest('.damaris-track');
        var src = track.data('src');
        if (!src) return;
        
        var allTracks = $('.damaris-track').map(function() { return $(this).data('src'); }).get();
        playlist = allTracks.filter(function(s) { return s; });
        currentIndex = playlist.indexOf(src);
        
        if (audio.src !== src) {
            audio.src = src;
            audio.play();
            updateBar(track);
        } else if (audio.paused) {
            audio.play();
        } else {
            audio.pause();
        }
        
        bar.css('display', 'flex').hide().fadeIn(300);
    });
    
    // Update bar
    function updateBar(track) {
        var title = track.find('.damaris-track-title').text() || 'Audio';
        bar.find('.damaris-player-title').text(title);
        bar.find('.damaris-playpause').text('⏸');
    }
    
    // Play/Pause
    bar.find('.damaris-playpause').on('click', function() {
        if (audio.paused) { audio.play(); $(this).text('⏸'); }
        else { audio.pause(); $(this).text('▶'); }
    });
    
    // Next/Prev
    bar.find('.damaris-player-next').on('click', function() {
        if (currentIndex < playlist.length - 1) {
            currentIndex++;
            audio.src = playlist[currentIndex];
            audio.play();
            var t = $('.damaris-track[data-src="' + playlist[currentIndex] + '"]');
            updateBar(t);
        }
    });
    bar.find('.damaris-player-prev').on('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            audio.src = playlist[currentIndex];
            audio.play();
            var t = $('.damaris-track[data-src="' + playlist[currentIndex] + '"]');
            updateBar(t);
        }
    });
    
    // Progress
    audio.addEventListener('timeupdate', function() {
        if (audio.duration) {
            var pct = (audio.currentTime / audio.duration) * 100;
            bar.find('.damaris-player-progress-bar').css('width', pct + '%');
            bar.find('.damaris-player-current').text(formatTime(audio.currentTime));
            bar.find('.damaris-player-total').text(formatTime(audio.duration));
        }
    });
    
    audio.addEventListener('ended', function() {
        bar.find('.damaris-player-next').click();
    });
    
    // Click on progress bar
    bar.find('.damaris-player-progress').on('click', function(e) {
        var rect = this.getBoundingClientRect();
        var pct = (e.clientX - rect.left) / rect.width;
        audio.currentTime = pct * audio.duration;
    });
    
    // Close
    bar.find('.damaris-player-close').on('click', function() {
        audio.pause();
        audio.src = '';
        bar.fadeOut(300);
    });
    
    function formatTime(s) {
        var m = Math.floor(s / 60);
        var sec = Math.floor(s % 60);
        return m + ':' + (sec < 10 ? '0' : '') + sec;
    }
    
    // Update play buttons state
    audio.addEventListener('play', function() { $('.damaris-play-btn').text('▶').removeClass('playing'); });
    audio.addEventListener('pause', function() { bar.find('.damaris-playpause').text('▶'); });
});