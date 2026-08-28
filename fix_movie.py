import re

file_path = 'themes/dark/movie.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the layout from <div class="bg-[#0a0a0a] pt-[60px] md:pt-[70px]"> to <!-- Movie Info -->
# I will use a precise regex or string find.
def replace_between(text, start_str, end_str, new_content):
    start = text.find(start_str)
    if start == -1: return text
    end = text.find(end_str, start)
    if end == -1: return text
    return text[:start] + new_content + text[end:]

start_str = """<div class="bg-[#0a0a0a] pt-[60px] md:pt-[70px]">"""
end_str = """<div class="bg-[#111319] min-h-screen text-gray-200 font-sans pb-20 pt-8">"""

new_movie_layout = """<?php if (isset($currentEp) && $currentEp): ?>
<div class="bg-black pt-[60px] md:pt-[70px]">
    <!-- THEATER MODE WATCH PAGE -->
    <div class="w-full px-0 md:px-8 lg:px-12 2xl:px-20 mx-auto pt-0 md:pt-6">
        <!-- Video Player -->
        <div class="w-full bg-black md:rounded-xl overflow-hidden shadow-2xl border-0 md:border border-[#2d2f36]">
                    <div class="aspect-video w-full relative flex items-center justify-center group" id="player-container">
                        <?php if ($isM3U8): ?>
                            <link rel="stylesheet" href="https://cdn.plyr.io/3.8.4/plyr.css" />
                            <style>
                                :root { --plyr-color-main: #dc2626; } /* Red theme */
                                .plyr { overflow: hidden; height: 100%; width: 100%; }
                            </style>
                            <video id="video-player" class="w-full h-full outline-none bg-black" playsinline poster="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : ($movie['thumb_url'] ?? '')) ?>"></video>
                            <script defer src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
                            <script defer src="https://cdn.plyr.io/3.8.4/plyr.polyfilled.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const video = document.getElementById('video-player');
                                    const source = "<?= addslashes($videoUrl) ?>";
                                    let startTime = <?= $startTime ?>;
                                    
                                    if (Hls.isSupported()) {
                                        const hls = new Hls();
                                        hls.loadSource(source);
                                        hls.on(Hls.Events.MANIFEST_PARSED, function () {
                                            const player = new Plyr(video, {
                                                i18n: { quality: 'Chất lượng', speed: 'Tốc độ', normal: 'Bình thường' }
                                            });
                                            if (startTime > 0) player.once('canplay', () => { player.currentTime = startTime; });
                                        });
                                        hls.attachMedia(video);
                                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                        video.src = source;
                                        video.addEventListener('loadedmetadata', () => {
                                            if (startTime > 0) video.currentTime = startTime;
                                        });
                                        new Plyr(video);
                                    }
                                });
                            </script>
                        <?php elseif ($isMp4): ?>
                            <video controls class="w-full h-full bg-black" poster="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : ($movie['thumb_url'] ?? '')) ?>">
                                <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
                            </video>
                        <?php elseif ($videoUrl): ?>
                            <iframe src="<?= htmlspecialchars($currentEp['link_embed']) ?>" class="absolute inset-0 w-full h-full bg-black" allowfullscreen frameborder="0"></iframe>
                        <?php else: ?>
                            <div class="text-white text-center p-8">
                                <i data-lucide="alert-triangle" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
                                <p>Không tìm thấy nguồn phát phù hợp cho tập phim này.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Player Control Bar -->
                    <div class="p-4 bg-[#111] flex flex-wrap justify-between items-center gap-3">
                        <div class="flex items-center text-gray-400 text-sm">
                            <i data-lucide="server" class="w-4 h-4 mr-1.5 text-red-500"></i> 
                            Server: <?= htmlspecialchars($episodes[0]['server_name'] ?? 'HLS/Embed') ?>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="toggleTheatreMode()" class="px-4 py-2 bg-gray-800 hover:bg-red-600 text-white rounded-md text-sm font-medium transition-colors flex items-center shadow-lg">
                                <i data-lucide="monitor" class="w-4 h-4 mr-1.5"></i> Rạp Hát
                            </button>
                        </div>
                    </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- CINEMATIC MOVIE DETAIL BACKDROP -->
<div class="relative w-full min-h-[70vh] lg:min-h-[85vh] bg-black flex items-end pb-12 pt-[80px]">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($movie['poster_url']) ? $movie['poster_url'] : ($movie['thumb_url'] ?? ''))) ?>" alt="Backdrop" class="w-full h-full object-cover opacity-50 blur-[2px] md:blur-none">
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/60 to-transparent hidden md:block"></div>
    </div>
    
    <div class="relative z-10 w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto">
        <div class="flex flex-col md:flex-row gap-8 items-end md:items-stretch">
            <!-- Poster Desktop -->
            <div class="hidden md:block w-48 lg:w-64 shrink-0 shadow-2xl rounded-xl overflow-hidden border border-white/10 self-end">
                <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($movie['thumb_url']) ? $movie['thumb_url'] : ($movie['poster_url'] ?? ''))) ?>" class="w-full object-cover aspect-[2/3]">
            </div>
            
            <!-- Info -->
            <div class="flex-1 pb-4">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <?php if(!empty($movie['quality'])): ?>
                    <span class="bg-white/20 backdrop-blur text-white px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider"><?= htmlspecialchars($movie['quality']) ?></span>
                    <?php endif; ?>
                    <span class="bg-red-600 text-white px-2 py-0.5 rounded text-xs font-bold"><?= htmlspecialchars($movie['year'] ?? date('Y')) ?></span>
                    <span class="text-gray-300 text-sm flex items-center gap-1"><i data-lucide="clock" class="w-4 h-4 text-gray-400"></i> <?= htmlspecialchars($movie['time'] ?? 'N/A') ?></span>
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-black text-white leading-tight tracking-tight drop-shadow-2xl mb-2"><?= htmlspecialchars($movie['name']) ?></h1>
                <?php if(!empty($movie['origin_name'])): ?>
                <h2 class="text-lg md:text-xl text-gray-400 font-medium mb-6 italic"><?= htmlspecialchars($movie['origin_name']) ?></h2>
                <?php endif; ?>
                
                <div class="flex flex-wrap gap-4 items-center">
                    <?php if ($first_ep_link !== '#'): ?>
                    <a href="<?= $first_ep_link ?>" class="flex items-center bg-white text-black hover:bg-gray-200 px-8 py-3.5 rounded-lg font-bold transition-all hover:scale-105 shadow-xl shadow-white/10">
                        <i data-lucide="play" class="w-6 h-6 mr-2 fill-current"></i> Xem Phim
                    </a>
                    <?php else: ?>
                    <button disabled class="flex items-center bg-gray-700 text-gray-400 px-8 py-3.5 rounded-lg font-bold cursor-not-allowed">
                        Đang Cập Nhật
                    </button>
                    <?php endif; ?>
                    <button onclick="document.getElementById('download-app-modal').classList.remove('hidden'); document.getElementById('download-app-modal').classList.add('flex');" class="flex items-center bg-white/10 hover:bg-white/20 backdrop-blur-md text-white px-6 py-3.5 rounded-lg font-medium transition-all hover:scale-105 border border-white/10">
                        <i data-lucide="download" class="w-5 h-5 mr-2"></i> Tải App
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

"""

content = replace_between(content, start_str, end_str, new_movie_layout)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Movie backdrop updated")
