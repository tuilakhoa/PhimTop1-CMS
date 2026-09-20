<div class="bg-gray-800/50 backdrop-blur-xl border border-white/10 rounded-xl overflow-hidden shadow-2xl p-6">
    <h2 class="text-2xl font-bold text-white flex items-center gap-2 mb-6">
        <i data-lucide="scan" class="w-6 h-6 text-indigo-400"></i> Quét Nghệ Sĩ Vi Phạm (Weibo Bot)
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Bảng điều khiển -->
        <div class="bg-black/20 rounded-lg p-5 border border-white/5">
            <h3 class="text-lg font-semibold text-white mb-4">Trạng thái Bot</h3>
            
            <div class="mb-4">
                <p class="text-sm text-gray-400 mb-1">Tình trạng hiện tại:</p>
                <div id="botState" class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-500/20 text-gray-300">
                    Đang kiểm tra...
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-400 mb-1">Tiến độ:</p>
                <div class="w-full bg-gray-700 rounded-full h-2.5 mb-1">
                    <div id="botProgress" class="bg-indigo-500 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <p id="botProgressText" class="text-xs text-gray-400">0% (0/0)</p>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-400 mb-1">Thông điệp từ Bot:</p>
                <div class="bg-gray-900/50 rounded p-3 text-sm font-mono text-green-400 min-h-[60px]" id="botMessage">
                    Đang kết nối...
                </div>
            </div>

            <button id="startBotBtn" onclick="startBot()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex justify-center items-center gap-2">
                <i data-lucide="play" class="w-4 h-4"></i> Bắt đầu quét Weibo
            </button>
        </div>

        <!-- Kết quả -->
        <div class="bg-black/20 rounded-lg p-5 border border-white/5 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">Kết Quả Mới Nhất</h3>
                <button onclick="loadResults()" class="text-xs bg-gray-700 hover:bg-gray-600 text-white px-2 py-1 rounded">Làm mới</button>
            </div>
            
            <div class="overflow-y-auto max-h-[300px] custom-scrollbar flex-1 mb-6">
                <table class="w-full text-sm text-left text-gray-300">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-800/50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 rounded-tl-lg">Nghệ Sĩ</th>
                            <th class="px-3 py-2">Từ khóa vi phạm</th>
                            <th class="px-3 py-2 rounded-tr-lg">Link</th>
                        </tr>
                    </thead>
                    <tbody id="resultsTableBody">
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-gray-500">Chưa có kết quả hoặc đang tải...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr class="border-gray-800 mb-4">
            
            <!-- Điều tra nhanh -->
            <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                <i data-lucide="search" class="w-5 h-5 text-yellow-500"></i> Điều Tra Nhanh Lịch Sử
            </h3>
            <p class="text-xs text-gray-400 mb-4">Kiểm tra xem một diễn viên cụ thể có từng chia sẻ các bài viết vi phạm chủ quyền trong quá khứ hay không.</p>
            <div class="flex gap-2">
                <input type="text" id="investigateName" placeholder="Nhập tên diễn viên (Tiếng Trung, VD: 赵丽颖)" class="flex-1 bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                <button onclick="investigateActor()" id="investigateBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm whitespace-nowrap">
                    Điều Tra
                </button>
            </div>
            <div id="investigateResult" class="mt-3 text-sm hidden p-3 rounded bg-gray-900/50 border border-gray-800"></div>
        </div>
    </div>
</div>

<script>
let monitorInterval;

function startBot() {
    if(!confirm("Bạn có chắc chắn muốn chạy Bot rà soát ngay bây giờ? Quá trình này có thể mất vài phút.")) return;
    
    const btn = document.getElementById('startBotBtn');
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    btn.innerHTML = '<i class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent text-white rounded-full mr-2"></i> Đang khởi động...';
    
    fetch('/api/weibo_bot_api.php?action=start_bot')
        .then(res => res.json())
        .then(data => {
            // Không dùng alert nữa vì gây khó chịu trên mobile
            btn.innerHTML = '<i data-lucide="check" class="w-4 h-4 mr-2"></i> Đã chạy nền';
            lucide.createIcons();
            startMonitoring();
        })
        .catch(err => {
            alert("Có lỗi xảy ra khi gọi Bot!");
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btn.innerHTML = '<i data-lucide="play" class="w-4 h-4 mr-2"></i> Bắt đầu quét Weibo';
            lucide.createIcons();
        });
}

function startMonitoring() {
    if(monitorInterval) clearInterval(monitorInterval);
    
    monitorInterval = setInterval(() => {
        fetch('/api/weibo_bot_api.php?action=status')
            .then(res => res.json())
            .then(data => {
                if(data.state) {
                    const stateEl = document.getElementById('botState');
                    stateEl.innerText = data.state.toUpperCase();
                    
                    if(data.state === "đang quét") {
                        stateEl.className = "inline-block px-3 py-1 rounded-full text-sm font-medium bg-blue-500/20 text-blue-400";
                    } else if(data.state === "hoàn thành") {
                        stateEl.className = "inline-block px-3 py-1 rounded-full text-sm font-medium bg-green-500/20 text-green-400";
                        clearInterval(monitorInterval);
                        document.getElementById('startBotBtn').disabled = false;
                        document.getElementById('startBotBtn').classList.remove('opacity-50');
                        loadResults(); // Tải lại bảng ngay khi xong
                    } else {
                        stateEl.className = "inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-500/20 text-gray-300";
                    }
                    
                    document.getElementById('botProgress').style.width = (data.percentage || 0) + '%';
                    document.getElementById('botProgressText').innerText = (data.percentage || 0) + '% (' + (data.progress || '0/0') + ')';
                    document.getElementById('botMessage').innerText = data.message || "Đang xử lý...";
                }
            })
            .catch(err => console.error(err));
    }, 2000); // 2 seconds
}

function loadResults() {
    fetch('/api/weibo_bot_api.php?action=results')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('resultsTableBody');
            tbody.innerHTML = '';
            
            if(!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-3 py-4 text-center text-gray-500">Không tìm thấy vi phạm nào.</td></tr>';
                return;
            }
            
            data.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = "border-b border-gray-700/50 hover:bg-white/5";
                tr.innerHTML = `
                    <td class="px-3 py-3">
                        <div class="font-medium text-white">${item.name}</div>
                        <div class="text-xs text-gray-400">${item.user_type}</div>
                    </td>
                    <td class="px-3 py-3 text-red-400">${item.keyword_matched}</td>
                    <td class="px-3 py-3">
                        <a href="${item.link}" target="_blank" class="text-blue-400 hover:underline text-xs flex items-center gap-1">
                            <i data-lucide="external-link" class="w-3 h-3"></i> Xem bài
                        </a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            lucide.createIcons();
        })
        .catch(err => {
            document.getElementById('resultsTableBody').innerHTML = '<tr><td colspan="3" class="px-3 py-4 text-center text-red-500">Lỗi khi tải kết quả</td></tr>';
        });
}

// Chạy theo dõi tự động khi mở trang
startMonitoring();
loadResults();

function investigateActor() {
    const nameInput = document.getElementById('investigateName');
    const name = nameInput.value.trim();
    if (!name) {
        alert("Vui lòng nhập tên diễn viên (Tiếng Trung)!");
        return;
    }
    
    const btn = document.getElementById('investigateBtn');
    const resultBox = document.getElementById('investigateResult');
    
    btn.disabled = true;
    btn.innerText = "Đang quét...";
    resultBox.classList.remove('hidden');
    resultBox.innerHTML = '<span class="text-yellow-400"><i class="animate-spin inline-block w-3 h-3 border-2 border-current border-t-transparent text-yellow-400 rounded-full mr-1"></i> Đang lục tìm toàn bộ lịch sử bài đăng của ' + name + '... Xin chờ vài chục giây.</span>';
    
    fetch('/api/weibo_bot_api.php?action=investigate&name=' + encodeURIComponent(name))
        .then(res => res.text())
        .then(text => {
            btn.disabled = false;
            btn.innerText = "Điều Tra";
            
            if (text.startsWith("RAW_ERROR:")) {
                resultBox.innerHTML = '<span class="text-red-400 font-mono text-xs break-all">❌ Lỗi BOT thô (RAW):<br>' + text.substring(10).replace(/</g, "&lt;") + '</span>';
                return;
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                resultBox.innerHTML = '<span class="text-red-400 font-mono text-xs break-all">❌ Lỗi PHP thô (RAW):<br>' + text.replace(/</g, "&lt;") + '</span>';
                return;
            }
            
            if (data.error) {
                resultBox.innerHTML = '<span class="text-red-400">❌ ' + data.error + '</span>';
                return;
            }
            
            if (data.violations && data.violations.length > 0) {
                let html = '<span class="text-red-500 font-bold mb-2 block">⚠️ PHÁT HIỆN VI PHẠM TỪ TÀI KHOẢN: ' + data.actor + '</span>';
                data.violations.forEach(v => {
                    html += '<div class="text-gray-300 mb-1">- Bài đăng chứa: <strong class="text-red-400">' + v.keyword + '</strong></div>';
                    html += '<a href="' + v.link + '" target="_blank" class="text-blue-400 hover:underline inline-block mb-2 text-xs">Xem bằng chứng &rarr;</a>';
                });
                html += '<div class="text-green-400 mt-2 text-xs">Đã tự động đưa vào Danh Sách Đen. Vui lòng tải lại bảng kết quả!</div>';
                resultBox.innerHTML = html;
            } else {
                resultBox.innerHTML = '<span class="text-green-400">✅ <strong>' + data.actor + '</strong> trong sạch! Không tìm thấy bất kỳ bài đăng nào chứa các từ khóa cấm trong lịch sử.</span>';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = "Điều Tra";
            resultBox.innerHTML = '<span class="text-red-400">❌ Lỗi mạng hoặc máy chủ không phản hồi: ' + err + '</span>';
        });
}
</script>
