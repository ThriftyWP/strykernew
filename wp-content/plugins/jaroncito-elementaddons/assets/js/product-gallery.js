document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
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
            slidesPerView: parseInt(document.querySelector('.swiper-container').dataset.imagesPerView, 10) || 3,
            slidesPerGroup: parseInt(document.querySelector('.swiper-container').dataset.slidesToScroll, 10) || 1,
            spaceBetween: 10,
            breakpoints: {
                768: {
                    slidesPerView: parseInt(document.querySelector('.swiper-container').dataset.imagesPerView, 10) || 3,
                }
            }
        });

        // Update the main image when clicking on a gallery image
        document.querySelectorAll('.jaroncito-gallery-item img').forEach(function(thumbnail) {
            thumbnail.addEventListener('click', function() {
                var mainImage = document.querySelector('.jaroncito-main-product-image');
                if (mainImage) {
                    mainImage.src = this.src;
                }
            });
        });
    } else {
        console.error('Swiper library is not loaded.');
    }
});
