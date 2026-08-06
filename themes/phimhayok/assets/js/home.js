document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        // Horizontal Swiper (16:9 Posters)
        new Swiper('.swiper-horizontal', {
            slidesPerView: 'auto',
            spaceBetween: 16,
            navigation: {
                nextEl: '.swiper-horizontal .swiper-button-next',
                prevEl: '.swiper-horizontal .swiper-button-prev',
            },
            breakpoints: {
                320: { slidesPerView: 1.2 },
                640: { slidesPerView: 2.2 },
                768: { slidesPerView: 3.2 },
                1024: { slidesPerView: 4.2 },
                1280: { slidesPerView: 5.2 },
            }
        });
        // Hero Swiper
        new Swiper('.swiper-hero', {
            slidesPerView: 1,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.swiper-hero .swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-hero .swiper-button-next',
                prevEl: '.swiper-hero .swiper-button-prev',
            }
        });

        // Top 10 Swiper
        new Swiper('.swiper-top10', {
            slidesPerView: 'auto',
            spaceBetween: 16,
            breakpoints: {
                320: { slidesPerView: 2.2 },
                640: { slidesPerView: 3.2 },
                768: { slidesPerView: 4.2 },
                1024: { slidesPerView: 5.2 },
                1280: { slidesPerView: 6.2 },
            }
        });

        // Vertical Posters Swiper
        new Swiper('.swiper-vertical-posters', {
            slidesPerView: 'auto',
            spaceBetween: 16,
            navigation: {
                nextEl: '.swiper-vertical-posters .swiper-button-next',
                prevEl: '.swiper-vertical-posters .swiper-button-prev',
            },
            breakpoints: {
                320: { slidesPerView: 2.2 },
                640: { slidesPerView: 3.2 },
                768: { slidesPerView: 4.2 },
                1024: { slidesPerView: 5.2 },
                1280: { slidesPerView: 7.2 },
            }
        });
    }
});
