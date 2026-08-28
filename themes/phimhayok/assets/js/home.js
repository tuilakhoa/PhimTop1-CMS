document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        // Horizontal Swiper (16:9 Posters)
        new Swiper('.swiper-horizontal', {
            slidesPerView: 'auto',
            spaceBetween: 16,
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
                        res.data.forEach(item => {
                            let thumb = item.poster_url || item.thumb_url;
                            if (thumb && !thumb.startsWith('http')) {
                                thumb = 'https://phimimg.com/' + thumb;
                            }
                            html += `
                                <a href="/phim/${item.slug}" class="swiper-slide group block w-32 sm:w-40 md:w-48 lg:w-56 shrink-0 relative    hover:z-50 cursor-pointer">
                                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-gray-900 shadow-lg group-hover:shadow-2xl group-hover:shadow-phim-yellow/20  ">
                                        <img src="${thumb}" alt="${item.name}" loading="lazy" class="w-full h-full object-cover   ">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100  "></div>
                                        
                                        <!-- Badge Gợi ý -->
                                        <div class="absolute top-2 left-2 bg-phim-yellow/90 backdrop-blur text-black text-[10px] font-bold px-2 py-0.5 rounded-sm uppercase tracking-wider shadow-lg">Gợi ý</div>
                                        
                                        <!-- Play Button -->
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100     ">
                                            <div class="w-12 h-12 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center border border-phim-yellow/50 shadow-[0_0_20px_rgba(234,179,8,0.3)]">
                                                <i data-lucide="play" class="w-5 h-5 text-phim-yellow fill-phim-yellow ml-1"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Info -->
                                        <div class="absolute bottom-0 left-0 p-3 w-full  translate-y-2 group-hover:translate-y-0  ">
                                            <h3 class="text-white font-bold text-sm truncate drop-shadow-md group-hover:text-phim-yellow ">${item.name}</h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-gray-900 font-bold text-[10px] bg-phim-yellow px-1.5 py-0.5 rounded-sm">${item.year || '2024'}</span>
                                                <span class="text-gray-300 text-[10px] uppercase">${item.quality || 'FHD'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
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
                            let thumbUrl = item.poster_url || item.thumb_url;
                            if (thumbUrl && !thumbUrl.startsWith('http')) {
                                // Assume phimimg if missing
                                thumbUrl = 'https://phimimg.com/' + thumbUrl;
                            }
                            html += `
                                <div class="swiper-slide w-[180px] md:w-[200px]">
                                    <a href="/phim/${item.slug}" class="block group relative cursor-pointer">
                                        <div class="aspect-[2/3] relative overflow-hidden rounded-lg">
                                            <img src="${thumbUrl}" alt="${item.name}" loading="lazy" class="w-full h-full object-cover   ">
                                            <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent "></div>
                                        </div>
                                        <div class="mt-3">
                                            <h3 class="text-white font-bold text-sm truncate group-hover:text-phim-yellow ">${item.name}</h3>
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
