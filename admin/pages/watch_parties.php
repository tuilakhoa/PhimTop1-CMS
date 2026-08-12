<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden shadow-sm">
    <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-gray-900/50">
        <div>
            <h2 class="text-lg font-bold text-white flex items-center">
                <i data-lucide="users" class="w-5 h-5 mr-2 text-indigo-400"></i> Quản Lý Phòng Xem Chung
            </h2>
            <p class="text-sm text-gray-400 mt-1">Quản lý các phòng xem chung đang hoạt động, có thể vô hiệu hóa nếu vi phạm.</p>
        </div>
        <button onclick="loadWatchParties()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i> Làm Mới
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-800/50 text-gray-400 text-xs uppercase tracking-wider">
                    <th class="p-4 font-semibold">Mã Phòng</th>
                    <th class="p-4 font-semibold">Phim / Tập</th>
                    <th class="p-4 font-semibold">Người Tạo</th>
                    <th class="p-4 font-semibold">Loại Phòng</th>
                    <th class="p-4 font-semibold">Tiến Độ</th>
                    <th class="p-4 font-semibold">Trạng Thái</th>
                    <th class="p-4 font-semibold">Cập Nhật</th>
                    <th class="p-4 font-semibold text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="watch-parties-body" class="divide-y divide-gray-800 text-sm">
                <!-- Nội dung được tải qua AJAX -->
            </tbody>
        </table>
    </div>
</div>

<script>
function loadWatchParties() {
    const tbody = document.getElementById('watch-parties-body');
    tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-gray-500"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i> Đang tải dữ liệu...</td></tr>';
    lucide.createIcons();
    
    fetch('/api/v1/watch_party.php?action=list')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                renderTable(data.data);
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-red-500">Lỗi: ' + data.message + '</td></tr>';
            }
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-red-500">Lỗi kết nối.</td></tr>';
        });
}

function renderTable(rooms) {
    const tbody = document.getElementById('watch-parties-body');
    if (!rooms || rooms.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-gray-500">Không có phòng xem chung nào.</td></tr>';
        return;
    }
    
    let html = '';
    rooms.forEach(room => {
        let isPlayingStr = room.is_playing == 1 ? '<span class="text-green-500">Đang phát</span>' : '<span class="text-yellow-500">Tạm dừng</span>';
        
        // Format time
        let totalSeconds = parseInt(room.current_time);
        let hours = Math.floor(totalSeconds / 3600);
        let minutes = Math.floor((totalSeconds % 3600) / 60);
        let seconds = totalSeconds % 60;
        let timeStr = (hours > 0 ? hours + ':' : '') + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
        
        let statusStr = room.status === 'active' 
            ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">Hoạt Động</span>'
            : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-500/10 text-red-500 border border-red-500/20">Vô Hiệu Hóa</span>';
            
        let typeStr = room.is_public == 1 
            ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20"><i data-lucide="globe" class="w-3 h-3 mr-1"></i>Công Khai</span>'
            : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600"><i data-lucide="lock" class="w-3 h-3 mr-1"></i>Riêng Tư</span>';
            
        let actionBtn = room.status === 'active'
            ? `<button onclick="toggleRoomStatus('${room.room_code}', 'disabled')" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors" title="Khóa Phòng"><i data-lucide="lock" class="w-4 h-4"></i></button>`
            : `<button onclick="toggleRoomStatus('${room.room_code}', 'active')" class="p-1.5 text-gray-400 hover:text-green-500 hover:bg-green-500/10 rounded-lg transition-colors" title="Mở Phòng"><i data-lucide="unlock" class="w-4 h-4"></i></button>`;

        html += `
            <tr class="hover:bg-gray-800/30 transition-colors">
                <td class="p-4 font-mono font-bold text-indigo-400">${room.room_code}</td>
                <td class="p-4">
                    <div class="font-medium text-gray-200">${room.movie_slug}</div>
                    <div class="text-xs text-gray-500">Tập ${room.episode_name}</div>
                </td>
                <td class="p-4 text-gray-300">${room.creator_name}</td>
                <td class="p-4">${typeStr}</td>
                <td class="p-4">
                    <div class="text-gray-300">${timeStr}</div>
                    <div class="text-xs">${isPlayingStr}</div>
                </td>
                <td class="p-4">${statusStr}</td>
                <td class="p-4 text-xs text-gray-500">${room.last_updated}</td>
                <td class="p-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        ${actionBtn}
                        <a href="/xem-phim/${room.movie_slug}/${room.episode_name}?party=${room.room_code}" target="_blank" class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors" title="Xem Thử">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    lucide.createIcons();
}

function toggleRoomStatus(roomCode, newStatus) {
    if (confirm('Bạn có chắc chắn muốn ' + (newStatus === 'disabled' ? 'khóa' : 'mở khóa') + ' phòng ' + roomCode + '?')) {
        fetch('/api/v1/watch_party.php?action=toggle_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ room_code: roomCode, status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                loadWatchParties();
            } else {
                alert('Lỗi: ' + data.message);
            }
        });
    }
}

// Load on init
document.addEventListener('DOMContentLoaded', loadWatchParties);
</script>
