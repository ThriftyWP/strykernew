document.addEventListener('DOMContentLoaded', function() {
    // Ensure Swiper is loaded
    if (typeof Swiper === 'undefined') {
        console.error('Swiper library is not loaded.');
        return;
    }
    console.log('Swiper version:', Swiper.version);

    // Initialize main gallery slider
    const gallerySlider = new Swiper('.jaroncito-gallery-slider', {
        slidesPerView: 1,
        spaceBetween: 30,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        loop: true,
        effect: 'slide',
    });


    var swiperContainer = document.querySelector('.jaroncito-gallery-carousel');
    if (!swiperContainer) return;

    // Get configuration from data attributes
    var imagesPerView = parseInt(swiperContainer.dataset.imagesPerView, 10) || 3;
    var slidesToScroll = parseInt(swiperContainer.dataset.slidesToScroll, 10) || 1;

    // Initialize Swiper
    var gallerySwiper = new Swiper('.jaroncito-gallery-carousel.swiper-container', {
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

    // Add navigation button functionality for main image (MOVED OUTSIDE LIGHTBOX)
    const nextButton = document.querySelector('.main-image-next');
    const prevButton = document.querySelector('.main-image-prev');
    const mainImage = document.querySelector('.jaroncito-main-product-image');
    const thumbnails = Array.from(document.querySelectorAll('.jaroncito-gallery-item img'));
    let currentMainIndex = 0;

    // Add main image to the beginning of images array
    const allImages = [mainImage, ...thumbnails];

    if (nextButton && prevButton && mainImage) {
        nextButton.addEventListener('click', () => {
            currentMainIndex = (currentMainIndex + 1) % allImages.length;
            mainImage.src = allImages[currentMainIndex].src;
        });

        prevButton.addEventListener('click', () => {
            currentMainIndex = (currentMainIndex - 1 + allImages.length) % allImages.length;
            mainImage.src = allImages[currentMainIndex].src;
        });
    }

    // Lightbox functionality
    if (mainImage && mainImage.dataset.lightbox === 'yes') {
        mainImage.addEventListener('click', function() {
            const lightbox = document.createElement('div');
            lightbox.className = 'jaroncito-lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-content">
                    <img src="${this.src}" alt="${this.alt}">
                    <button class="lightbox-prev">&lt;</button>
                    <button class="lightbox-next">&gt;</button>
                    <button class="lightbox-close">&times;</button>
                </div>
            `;

            document.body.appendChild(lightbox);

            // Add keyboard navigation
            const handleKeyboard = (e) => {
                switch(e.key) {
                    case 'ArrowRight':
                        currentIndex = (currentIndex + 1) % images.length;
                        lightbox.querySelector('img').src = images[currentIndex];
                        break;
                    case 'ArrowLeft':
                        currentIndex = (currentIndex - 1 + images.length) % images.length;
                        lightbox.querySelector('img').src = images[currentIndex];
                        break;
                    case 'Escape':
                        lightbox.remove();
                        document.removeEventListener('keydown', handleKeyboard);
                        break;
                }
            };

            document.addEventListener('keydown', handleKeyboard);

            // Make sure to remove the event listener when closing the lightbox
            lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
                lightbox.remove();
                document.removeEventListener('keydown', handleKeyboard);
            });
            
            // Handle navigation
            const images = Array.from(document.querySelectorAll('.jaroncito-gallery-item img')).map(img => img.src);
            let currentIndex = parseInt(this.dataset.index);

            images.unshift(mainImage.src);
            
            lightbox.querySelector('.lightbox-next').addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % images.length;
                lightbox.querySelector('img').src = images[currentIndex];
            });
            
            lightbox.querySelector('.lightbox-prev').addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                lightbox.querySelector('img').src = images[currentIndex];
            });
            
            lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
                lightbox.remove();
            });
        });
    }
});