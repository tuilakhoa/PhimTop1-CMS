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
            effect: 'creative',
            creativeEffect: {
                prev: {
                    shadow: true,
                    translate: ['-20%', 0, -1],
                },
                next: {
                    translate: ['100%', 0, 0],
                },
            },
            pagination: {
                el: '.swiper-hero .swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-hero .swiper-button-next',
                prevEl: '.swiper-hero .swiper-button-prev',
            },
            on: {
                autoplayTimeLeft(s, time, progress) {
                    const progressLine = document.querySelector('.hero-progress-line');
                    if(progressLine) {
                        progressLine.style.width = ((1 - progress) * 100) + '%';
                    }
                }
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

    // Load LocalStorage Watch History
    try {
        let history = JSON.parse(localStorage.getItem('phimhayok_watch_history')) || [];
        if (history.length > 0) {
            const section = document.getElementById('continue-watching-section');
            const list = document.getElementById('continue-watching-list');
            if (section && list) {
                let html = '';
                history.forEach(item => {
                    const linkUrl = item.url ? item.url : `/phim/${item.slug}`;
                    html += `
                        <div class="swiper-slide w-[280px] md:w-[320px]">
                            <a href="${linkUrl}" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-2">
                                <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                                    <img src="${item.thumb}" alt="${item.name}" loading="lazy" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                                    <div class="absolute inset-0 bg-black/40 group-hover:bg-transparent transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <i data-lucide="play-circle" class="w-12 h-12 text-phim-yellow"></i>
                                    </div>
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm">
                                            Tập ${item.episode}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h3 class="text-white font-medium text-sm md:text-base truncate group-hover:text-phim-yellow transition-colors">${item.name}</h3>
                                </div>
                            </a>
                        </div>
                    `;
                });
                list.innerHTML = html;
                section.classList.remove('hidden');
                
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Init Swiper for History
                new Swiper('.swiper-history', {
                    slidesPerView: 'auto',
                    spaceBetween: 16,
                    navigation: {
                        nextEl: '.swiper-history .swiper-button-next',
                        prevEl: '.swiper-history .swiper-button-prev',
                    },
                    breakpoints: {
                        320: { slidesPerView: 1.2 },
                        640: { slidesPerView: 2.2 },
                        768: { slidesPerView: 3.2 },
                        1024: { slidesPerView: 4.2 },
                        1280: { slidesPerView: 5.2 },
                    }
                });
            }
        }
    } catch(e) {
        console.error('Error loading history:', e);
    }
});

    // Load AI Recommendations
    try {
        fetch('/api/v1/recommend.php?action=personal&limit=12')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    const section = document.getElementById('ai-recommend-section');
                    const list = document.getElementById('ai-recommend-list');
                    if (section && list) {
                        let html = '';
                        res.data.forEach(item => {
                            let thumbUrl = item.thumb_url || item.poster_url;
                            if (thumbUrl && !thumbUrl.startsWith('http')) {
                                // Assume phimimg if missing
                                thumbUrl = 'https://phimimg.com/' + thumbUrl;
                            }
                            html += `
                                <div class="swiper-slide w-[180px] md:w-[200px]">
                                    <a href="/phim/${item.slug}" class="block group relative cursor-pointer">
                                        <div class="aspect-[2/3] relative overflow-hidden rounded-lg">
                                            <img src="${thumbUrl}" alt="${item.name}" loading="lazy" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                            <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors"></div>
                                        </div>
                                        <div class="mt-3">
                                            <h3 class="text-white font-bold text-sm truncate group-hover:text-phim-yellow transition-colors">${item.name}</h3>
                                            <p class="text-gray-500 text-xs truncate mt-0.5">${item.origin_name || ''}</p>
                                        </div>
                                    </a>
                                </div>
                            `;
                        });
                        list.innerHTML = html;
                        section.classList.remove('hidden');
                        
                        if (typeof lucide !== 'undefined') lucide.createIcons();

                        new Swiper('.swiper-recommend', {
                            slidesPerView: 'auto',
                            spaceBetween: 16,
                            navigation: {
                                nextEl: '.swiper-recommend .swiper-button-next',
                                prevEl: '.swiper-recommend .swiper-button-prev',
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
                }
            })
            .catch(err => console.error('Error loading recommendations:', err));
    } catch(e) {}
