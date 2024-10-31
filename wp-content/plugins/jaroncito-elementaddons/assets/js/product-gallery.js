document.addEventListener('DOMContentLoaded', function() {
    // Ensure Swiper is loaded
    if (typeof Swiper === 'undefined') {
        console.error('Swiper library is not loaded.');
        return;
    }

    var swiperContainer = document.querySelector('.swiper-container');
    if (!swiperContainer) return;

    // Get configuration from data attributes
    var imagesPerView = parseInt(swiperContainer.dataset.imagesPerView, 10) || 3;
    var slidesToScroll = parseInt(swiperContainer.dataset.slidesToScroll, 10) || 1;

    // Initialize Swiper
    var gallerySwiper = new Swiper('.swiper-container', {
        loop: false,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        slidesPerView: imagesPerView,
        slidesPerGroup: slidesToScroll,
        spaceBetween: 10,
        breakpoints: {
            768: {
                slidesPerView: imagesPerView,
            }
        }
    });

    // Update the main image when clicking on a gallery image
    document.querySelectorAll('.jaroncito-gallery-item img').forEach(function (thumbnail) {
        thumbnail.addEventListener('click', function () {
            var mainImage = document.querySelector('.jaroncito-main-product-image');
            if (mainImage) {
                mainImage.src = this.src;
            }
        });
    });
});
