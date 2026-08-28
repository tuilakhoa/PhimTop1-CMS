<?php
$composerFile = __DIR__ . "/../../composer.lock";
$phpmailerVersion = "v7.1.1";
if (file_exists($composerFile)) {
    $composerData = json_decode(file_get_contents($composerFile), true);
    if (!empty($composerData['packages'])) {
        foreach ($composerData['packages'] as $pkg) {
            if ($pkg['name'] === 'phpmailer/phpmailer') {
                $phpmailerVersion = $pkg['version'];
                break;
            }
        }
    }
}
?>

<!-- Library Update Checker -->
<h3 class="text-xl font-bold text-white mb-6 flex items-center mt-12 gap-3 drop-shadow-md"><div class="p-2 bg-pink-500/10 rounded-lg border border-pink-500/20"><i data-lucide="package-search" class="w-5 h-5 text-pink-400"></i></div> Kiểm Tra Cập Nhật Thư Viện</h3>

<div class="bg-admin-panel backdrop-blur-2xl p-6 rounded-[2rem] border border-admin-border shadow-2xl relative overflow-hidden">
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-pink-500/10 rounded-full blur-[100px] pointer-events-none mix-blend-screen"></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10" id="library-status-list">
        <!-- JS will populate this -->
        <div class="col-span-1 md:col-span-2 text-center text-gray-500 py-8">
            <i data-lucide="loader" class="w-8 h-8 animate-spin mx-auto mb-3 text-pink-400"></i>
            Đang kiểm tra phiên bản các thư viện...
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const libraries = [
        { type: 'frontend', name: 'Plyr (Trình phát video)', package: 'plyr', current: '3.8.4', url: 'https://data.jsdelivr.com/v1/package/npm/plyr' },
        { type: 'frontend', name: 'Swiper (Trình chiếu slide)', package: 'swiper', current: '14.2.0', url: 'https://data.jsdelivr.com/v1/package/npm/swiper' },
        { type: 'php', name: 'PHPMailer (Gửi Email)', package: 'phpmailer/phpmailer', current: '<?= htmlspecialchars($phpmailerVersion) ?>', url: 'https://repo.packagist.org/p2/phpmailer/phpmailer.json' }
    ];

    const listEl = document.getElementById('library-status-list');
    
    Promise.all(libraries.map(lib => {
        return fetch(lib.url)
            .then(res => res.json())
            .then(data => {
                if (lib.type === 'frontend') {
                    lib.latest = data.tags && data.tags.latest ? data.tags.latest : lib.current;
                } else {
                    lib.latest = data.packages && data.packages[lib.package] && data.packages[lib.package][0] ? data.packages[lib.package][0].version : lib.current;
                }
                return lib;
            })
            .catch(() => {
                lib.latest = 'Lỗi mạng';
                return lib;
            });
    })).then(results => {
        listEl.innerHTML = '';
        results.forEach(lib => {
            const isUpToDate = lib.latest === lib.current || lib.latest.replace('v', '') === lib.current.replace('v', '');
            const statusIcon = isUpToDate 
                ? '<div class="w-12 h-12 bg-green-500/10 rounded-xl flex items-center justify-center text-green-400 shrink-0 border border-green-500/20"><i data-lucide="check-circle-2" class="w-6 h-6"></i></div>'
                : '<div class="w-12 h-12 bg-yellow-500/10 rounded-xl flex items-center justify-center text-yellow-400 shrink-0 border border-yellow-500/20"><i data-lucide="alert-triangle" class="w-6 h-6"></i></div>';
            
            const badge = isUpToDate
                ? '<span class="text-[10px] font-bold text-green-400 bg-green-500/10 border border-green-500/20 px-2 py-1 rounded-md">Mới Nhất</span>'
                : '<span class="text-[10px] font-bold text-yellow-400 bg-yellow-500/10 border border-yellow-500/20 px-2 py-1 rounded-md shadow-[0_0_10px_rgba(234,179,8,0.1)]">Có Bản Mới</span>';

            const card = `
                <div class="flex items-center gap-5 p-5 bg-gray-950/50 rounded-2xl border border-gray-800 transition-all hover:bg-gray-900/80">
                    ${statusIcon}
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-white font-bold text-sm leading-tight">${lib.name}</h4>
                            ${badge}
                        </div>
                        <p class="text-gray-500 text-xs mt-1">Đang dùng: <span class="text-gray-300 font-medium">${lib.current}</span> <span class="mx-2 text-gray-700">•</span> Mới nhất: <span class="text-gray-300 font-medium">${lib.latest}</span></p>
                    </div>
                </div>
            `;
            listEl.innerHTML += card;
        });
        
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
});
</script>
