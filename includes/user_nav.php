<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$settings = getSettings();
$coins = 0;
if (isset($_SESSION['user'])) {
    $pdo = getPDO();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT m.coins, f.image_url as frame_image FROM members m LEFT JOIN avatar_frames f ON m.active_frame_id = f.id WHERE m.email = ?");
            $stmt->execute([$_SESSION['user']['email']]);
            $u = $stmt->fetch();
            if ($u) {
                $coins = (int)$u['coins'];
                $navFrameUrl = $u['frame_image'] ?? null;
            }
        } catch (PDOException $e) {}
    }
}
?>
<div class="flex items-center space-x-4">
    <?php if (isset($_SESSION['user'])): ?>
        <!-- Notification Bell -->
        <div class="relative group z-50">
            <button class="relative p-2 text-gray-300 hover:text-white transition-colors focus:outline-none" id="btn-notifications">
                <i data-lucide="bell" class="w-6 h-6"></i>
                <span id="notif-badge" class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">0</span>
            </button>
            <div class="absolute right-0 mt-2 w-80 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all origin-top-right transform scale-95 group-hover:scale-100 z-50">
                <div class="p-3 border-b border-gray-800 flex justify-between items-center">
                    <h4 class="text-white font-bold">Thông Báo</h4>
                    <button id="btn-mark-all-read" class="text-xs text-blue-400 hover:text-blue-300">Đọc tất cả</button>
                </div>
                <div id="notif-list" class="max-h-80 overflow-y-auto custom-scrollbar p-2">
                    <div class="text-center text-gray-500 text-sm py-4">Đang tải...</div>
                </div>
            </div>
        </div>
        
        <!-- User Profile Dropdown -->
        <div class="relative group z-50">
            <button class="flex items-center focus:outline-none bg-gray-800/50 hover:bg-gray-700/50 px-2 py-1.5 md:px-3 md:py-2 rounded-full transition-all border border-gray-700/50">
                <div class="relative w-6 h-6 md:w-8 md:h-8 mr-1 md:mr-2">
                    <img src="<?= htmlspecialchars($_SESSION['user']['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user']['name']) . '&background=random') ?>" class="w-full h-full rounded-full shadow-sm" alt="Avatar">
                    <?php if (!empty($navFrameUrl)): ?>
                        <img src="<?= htmlspecialchars($navFrameUrl) ?>" class="absolute inset-0 w-full h-full scale-[1.3] object-contain z-10 pointer-events-none" alt="Frame">
                    <?php endif; ?>
                </div>
                <span class="hidden md:inline text-sm font-medium text-gray-200"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                <i data-lucide="chevron-down" class="w-3 h-3 md:w-4 md:h-4 text-gray-400 ml-1"></i>
            </button>
            <div class="absolute right-0 mt-2 w-56 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all origin-top-right transform scale-95 group-hover:scale-100 z-50">
                <div class="p-2">
                    <div class="px-4 py-3 border-b border-gray-800 mb-2 flex justify-between items-center">
                        <div class="overflow-hidden flex-1 pr-2">
                            <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                            <p class="text-xs text-gray-400 truncate mt-0.5"><?= htmlspecialchars($_SESSION['user']['email']) ?></p>
                        </div>
                        <div class="text-yellow-400 text-xs font-bold bg-yellow-400/10 px-2 py-1 rounded-lg border border-yellow-400/20 whitespace-nowrap" title="Điểm thưởng khi xem phim">
                            <span id="nav-coins"><?= $coins ?></span> <i data-lucide="coins" class="w-3 h-3 inline-block -mt-0.5"></i>
                        </div>
                    </div>
                    <a href="/profiles.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors mb-1">
                        <i data-lucide="users" class="w-4 h-4 mr-2"></i> Hồ sơ người xem
                    </a>
                    <a href="/history.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors mb-1">
                        <i data-lucide="history" class="w-4 h-4 mr-2"></i> Lịch sử xem phim
                    </a>
                    <a href="/shop.php" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors mb-1">
                        <i data-lucide="store" class="w-4 h-4 mr-2 text-red-400"></i> Cửa hàng vật phẩm
                    </a>
                    <a href="/api/auth.php?action=logout" class="flex items-center px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <a href="/member.php" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-medium transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
            <i data-lucide="user-circle" class="w-4 h-4 md:w-5 md:h-5 text-gray-200"></i>
            <span class="hidden md:inline">Đăng nhập</span>
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['user'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifBadge = document.getElementById('notif-badge');
    const notifList = document.getElementById('notif-list');
    const btnMarkAll = document.getElementById('btn-mark-all-read');
    
    function fetchNotifications() {
        fetch('/api/notifications.php?action=list')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if (res.unread_count > 0) {
                        notifBadge.textContent = res.unread_count;
                        notifBadge.classList.remove('hidden');
                    } else {
                        notifBadge.classList.add('hidden');
                    }
                    
                    if (res.data.length === 0) {
                        notifList.innerHTML = '<div class="text-center text-gray-500 text-sm py-4">Không có thông báo.</div>';
                        return;
                    }
                    
                    let html = '';
                    res.data.forEach(item => {
                        const bgClass = item.is_read == 0 ? 'bg-gray-800/80' : '';
                        const titleClass = item.is_read == 0 ? 'font-bold text-white' : 'text-gray-300';
                        html += `
                            <div class="p-3 mb-1 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer ${bgClass}" onclick="markRead(${item.id})">
                                <p class="text-sm ${titleClass} mb-1">${item.title}</p>
                                <p class="text-xs text-gray-400 mb-1">${item.message}</p>
                                <p class="text-[10px] text-gray-500">${item.created_at}</p>
                            </div>
                        `;
                    });
                    notifList.innerHTML = html;
                }
            });
    }
    
    window.markRead = function(id) {
        fetch('/api/notifications.php?action=mark_read', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({notification_id: id})
        }).then(() => fetchNotifications());
    };
    
    if (btnMarkAll) {
        btnMarkAll.addEventListener('click', () => markRead(0));
    }
    
    fetchNotifications();
    // Refresh every 2 minutes
    setInterval(fetchNotifications, 120000);
});
</script>
<?php endif; ?>
