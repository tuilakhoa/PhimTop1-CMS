const fs = require('fs');
let code = fs.readFileSync('new_header.php', 'utf8');

// Replace the right side of header to include a hamburger button
const rightSide = `<div class="flex items-center gap-4">
                <form id="live-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative hidden sm:block">`;

const newRightSide = `<div class="flex items-center gap-4">
                <form id="live-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative hidden sm:block">`;

const headerEnd = `</header>`;
const newHeaderEnd = `    <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-white focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        
        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu" class="hidden md:hidden bg-[#181a20] border-t border-gray-800 absolute w-full left-0 top-full shadow-2xl flex flex-col">
            <div class="p-4 border-b border-gray-800">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative w-full">
                    <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="Tìm kiếm phim..." class="bg-gray-800/80 text-sm text-white rounded-full pl-10 pr-4 py-2 w-full focus:outline-none focus:ring-1 focus:ring-kk-red border border-gray-700">
                    <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-white transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <a href="<?php echo home_url(); ?>" class="px-6 py-3 border-b border-gray-800/50 text-gray-300 hover:text-white hover:bg-white/5">TRANG CHỦ</a>
            <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="px-6 py-3 border-b border-gray-800/50 text-gray-300 hover:text-white hover:bg-white/5">PHIM MỚI</a>
            
            <div class="px-6 py-3 border-b border-gray-800/50">
                <div class="text-gray-300 font-bold mb-2 text-sm text-kk-red">THỂ LOẠI</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach(array_slice($genres, 0, 10) as $g): ?>
                        <a href="<?php echo home_url('/the-loai/' . $g['slug']); ?>" class="text-xs text-gray-400 hover:text-white bg-gray-800/50 px-2 py-1 rounded"><?php echo esc_html($g['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="px-6 py-3 pb-6">
                <div class="text-gray-300 font-bold mb-2 text-sm text-kk-red">QUỐC GIA</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach(array_slice($countries, 0, 10) as $c): ?>
                        <a href="<?php echo home_url('/quoc-gia/' . $c['slug']); ?>" class="text-xs text-gray-400 hover:text-white bg-gray-800/50 px-2 py-1 rounded"><?php echo esc_html($c['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </header>`;

code = code.replace(headerEnd, newHeaderEnd);

const scriptEnd = `</script>`;
const newScriptEnd = `    
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
</script>`;

code = code.replace(scriptEnd, newScriptEnd);
fs.writeFileSync('new_header.php', code);
console.log("Patched mobile menu");
