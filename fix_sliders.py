import re

file_path = 'themes/dark/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the AI recommend block entirely
# I will find the AI Recommend container and replace its JS template string.

def replace_between(text, start_str, end_str, new_content):
    start = text.find(start_str)
    if start == -1: return text
    end = text.find(end_str, start)
    if end == -1: return text
    return text[:start + len(start_str)] + new_content + text[end:]

new_ai_html = """
                            let html = '';
                            res.data.forEach(item => {
                                let thumb = item.poster_url || item.thumb_url;
                                if (thumb && !thumb.startsWith('http')) {
                                    thumb = 'https://phimimg.com/' + thumb;
                                }
                                html += `
                                    <a href="/phim/${item.slug}" class="swiper-slide group block w-32 sm:w-40 md:w-48 lg:w-56 shrink-0 relative transition-transform duration-500 hover:scale-105 hover:z-50">
                                        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-gray-900 border border-white/5 shadow-lg group-hover:shadow-2xl group-hover:shadow-red-500/20 transition-all duration-500">
                                            <img src="${thumb}" alt="${item.name}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                                            
                                            <!-- Badge Gợi ý -->
                                            <div class="absolute top-2 left-2 bg-red-600/90 backdrop-blur text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-lg">Gợi ý</div>
                                            
                                            <!-- Play Button -->
                                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                                                <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/40 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                                                    <i data-lucide="play" class="w-5 h-5 text-white fill-white ml-1"></i>
                                                </div>
                                            </div>
                                            
                                            <!-- Info -->
                                            <div class="absolute bottom-0 left-0 p-3 w-full transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                                                <h3 class="text-white font-bold text-sm truncate drop-shadow-md">${item.name}</h3>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="text-green-400 font-bold text-[10px] bg-green-400/10 px-1.5 py-0.5 rounded border border-green-400/20">${item.year || '2024'}</span>
                                                    <span class="text-gray-300 text-[10px] uppercase">${item.quality || 'FHD'}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                `;
                            });
"""

# Find AI recommend block and replace
content = replace_between(content, "const container = document.getElementById('ai-recommend-container');", "container.classList.remove('hidden');", new_ai_html + "\n                            ")

# Let's fix Top Ranking HTML as well (around line containing ranking layout)
old_ranking_php_start = """<div class="swiper-slide group shrink-0 w-32 sm:w-40 md:w-48 block">"""
old_ranking_php_end = """<!-- Info -->"""

# Wait, regex is safer. I'll just rewrite the whole top ranking PHP loop
# Let's see what the original is.
