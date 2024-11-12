jQuery(document).ready(function($) {
    var $slider = $('.video-slider');

    // Initialize Slick Slider
    $slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: true,
        arrows: true,
        autoplay: false,
        autoplaySpeed: 5000,
        adaptiveHeight: true
    });

    // Function to stop the video in the current slide
    function stopCurrentVideo() {
        var $currentSlide = $slider.find('.slick-current');
        var $iframe = $currentSlide.find('iframe');
        var iframeSrc = $iframe.attr('src');

        if ($iframe.length) {
            // Reset the iframe src to stop the video
            $iframe.attr('src', iframeSrc);
        }
    }

    // Listen for the 'beforeChange' event, which triggers before the slide changes
    $slider.on('beforeChange', function(event, slick, currentSlide, nextSlide) {
        stopCurrentVideo(); // Stop video in the current slide before transitioning
    });
});
