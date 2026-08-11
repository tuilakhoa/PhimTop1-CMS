<?php
$settings = getSettings();
$isEnabled = isset($settings['enableWatchingSession']) ? (int)$settings['enableWatchingSession'] : 1;
?>
<h2 class="text-2xl font-bold text-white mb-6">Theo Dõi Người Xem (Realtime)</h2>

<?php if (!$isEnabled): ?>
<div class="bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 p-4 rounded-xl mb-6 flex items-center">
    <i data-lucide="alert-triangle" class="w-5 h-5 mr-3"></i>
    <div>
        <p class="font-medium">Tính năng Theo dõi Người Xem hiện đang bị tắt.</p>
        <p class="text-sm opacity-80">Bạn có thể bật lại trong <a href="?page=settings" class="underline">Cài đặt chung</a> để xem người dùng đang hoạt động.</p>
    </div>
</div>
<?php endif; ?>

<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden shadow-lg mb-8 <?= (!$isEnabled) ? 'opacity-50 pointer-events-none' : '' ?>">
    <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-800/50">
        <h3 class="font-bold text-lg text-white flex items-center">
            <i data-lucide="activity" class="w-5 h-5 mr-2 text-green-500"></i> Đang Online
            <span id="online-count" class="ml-2 bg-green-500/20 text-green-400 text-xs px-2 py-1 rounded-full">0</span>
        </h3>
        <button onclick="fetchSessions()" class="text-sm bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-lg transition-colors flex items-center">
            <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i> Làm mới
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-400">
            <thead class="text-xs text-gray-500 uppercase bg-gray-800/50">
                <tr>
                    <th scope="col" class="px-6 py-3">Thiết bị</th>
                    <th scope="col" class="px-6 py-3">Nền tảng</th>
                    <th scope="col" class="px-6 py-3">Tài khoản</th>
                    <th scope="col" class="px-6 py-3">Đang xem</th>
                    <th scope="col" class="px-6 py-3">Cập nhật</th>
                    <th scope="col" class="px-6 py-3 text-right">Điều khiển</th>
                </tr>
            </thead>
            <tbody id="sessions-tbody">
                <!-- Rows will be injected here -->
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Đang tải dữ liệu...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
let refreshInterval;

function fetchSessions() {
    fetch('/api/v1/watching_session.php?action=list')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                renderSessions(data.data);
            }
        })
        .catch(err => console.error(err));
}

function sendCommand(deviceId, cmd) {
    fetch('/api/v1/watching_session.php?action=command', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({device_id: deviceId, command: cmd})
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            alert('Đã gửi lệnh ' + cmd + ' tới thiết bị!');
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
}

function formatTime(seconds) {
    if (!seconds || seconds <= 0) return '00:00';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
}

function renderSessions(sessions) {
    const tbody = document.getElementById('sessions-tbody');
    const countBadge = document.getElementById('online-count');
    countBadge.textContent = sessions.length;

    if(sessions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Không có ai đang xem lúc này.</td></tr>';
        return;
    }

    let html = '';
    sessions.forEach(s => {
        const platformIcon = s.platform === 'web' ? 'globe' : (s.platform === 'tv' ? 'tv' : 'smartphone');
        const userBadge = s.is_logged_in == 1 
            ? `<span class="bg-blue-500/20 text-blue-400 text-xs px-2 py-1 rounded border border-blue-500/30">${s.user_name}</span>`
            : `<span class="bg-gray-500/20 text-gray-400 text-xs px-2 py-1 rounded border border-gray-500/30">Guest</span>`;
        
        html += `
        <tr class="border-b border-gray-800 hover:bg-gray-800/30 transition-colors">
            <td class="px-6 py-4 font-medium text-white truncate max-w-[150px]" title="${s.device_id}">
                ${s.device_name || 'Không xác định'}
                <div class="text-[10px] text-gray-500 truncate">${s.device_id}</div>
            </td>
            <td class="px-6 py-4">
                <i data-lucide="${platformIcon}" class="w-4 h-4 inline-block mr-1"></i> <span class="capitalize">${s.platform}</span>
            </td>
            <td class="px-6 py-4">${userBadge}</td>
            <td class="px-6 py-4">
                <div class="text-white font-medium truncate max-w-[200px]" title="${s.movie_name}">${s.movie_name}</div>
                <div class="text-xs text-blue-400">${s.episode_name} ${s.progress ? `<span class="text-green-400 ml-1">(Đang xem: ${formatTime(s.progress)})</span>` : ''}</div>
            </td>
            <td class="px-6 py-4 text-xs text-gray-400">${s.last_seen}</td>
            <td class="px-6 py-4 text-right">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button onclick="sendCommand('${s.device_id}', 'play')" title="Play" class="px-2 py-1 text-sm font-medium bg-gray-800 border border-gray-700 hover:bg-green-600/20 hover:text-green-500 text-gray-300 rounded-l-lg focus:z-10 focus:ring-2 focus:ring-gray-500">
                        <i data-lucide="play" class="w-4 h-4"></i>
                    </button>
                    <button onclick="sendCommand('${s.device_id}', 'pause')" title="Pause" class="px-2 py-1 text-sm font-medium bg-gray-800 border-t border-b border-gray-700 hover:bg-yellow-600/20 hover:text-yellow-500 text-gray-300 focus:z-10 focus:ring-2 focus:ring-gray-500">
                        <i data-lucide="pause" class="w-4 h-4"></i>
                    </button>
                    <button onclick="sendCommand('${s.device_id}', 'stop')" title="Stop" class="px-2 py-1 text-sm font-medium bg-gray-800 border border-gray-700 hover:bg-red-600/20 hover:text-red-500 text-gray-300 rounded-r-lg focus:z-10 focus:ring-2 focus:ring-gray-500">
                        <i data-lucide="square" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
    });
    tbody.innerHTML = html;
    lucide.createIcons();
}

document.addEventListener('DOMContentLoaded', () => {
    fetchSessions();
    refreshInterval = setInterval(fetchSessions, 5000); // Tự động làm mới mỗi 5 giây
});
</script>
