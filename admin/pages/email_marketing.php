<h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-8 tracking-tight">Email Marketing (Tự Động)</h2>

<div class="bg-admin-panel backdrop-blur-xl border border-admin-border rounded-[2rem] p-8 mb-10 shadow-2xl relative overflow-hidden group">
    <div class="absolute -top-32 -right-32 w-64 h-64 bg-admin-primary/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-admin-primary/10 transition-colors duration-700"></div>
    
    <div class="relative z-10">
        <div class="mb-6 bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 flex items-start text-blue-400">
            <i data-lucide="info" class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0"></i>
            <div class="text-sm">
                <p class="font-bold mb-1">Gửi Email Hàng Loạt Tới Thành Viên</p>
                <p class="text-blue-300">Nhập tiêu đề, nội dung và các slug phim muốn giới thiệu (ngăn cách bằng dấu phẩy). Hệ thống sẽ tự động tạo email html giới thiệu phim đẹp mắt.</p>
                <p class="text-blue-300 mt-2 font-semibold">Lưu ý: Bạn cần cấu hình SMTP trong phần "Cấu Hình Chung" trước khi sử dụng tính năng này.</p>
            </div>
        </div>

        <!-- Mẫu Email -->
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Chọn Mẫu Email Có Sẵn</label>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="loadTemplate('weekend')" class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"><i data-lucide="coffee" class="w-4 h-4"></i> Cuối tuần cày phim</button>
                <button type="button" onclick="loadTemplate('newest')" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"><i data-lucide="sparkles" class="w-4 h-4"></i> Phim mới cập nhật</button>
                <button type="button" onclick="loadTemplate('comeback')" class="bg-orange-500/10 hover:bg-orange-500/20 text-orange-300 border border-orange-500/30 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"><i data-lucide="heart-handshake" class="w-4 h-4"></i> Mời quay lại (Miss you)</button>
                <button type="button" onclick="loadTemplate('event')" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5"><i data-lucide="gift" class="w-4 h-4"></i> Sự kiện & Thưởng xu</button>
            </div>
        </div>

        <div id="email-form" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Tiêu Đề Email</label>
                    <input type="text" id="email-subject" required placeholder="VD: Khám phá các siêu phẩm mới tuần này!" class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Nội Dung Mở Đầu</label>
                <textarea id="email-message" required rows="4" placeholder="Cảm ơn bạn đã luôn đồng hành cùng PhimTop1. Chúng tôi xin giới thiệu đến bạn những tựa phim hấp dẫn nhất..." class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all custom-scrollbar"></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-3 uppercase tracking-wide">Danh Sách Phim Đính Kèm</label>
                
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="radio" name="movie_source" value="manual" checked class="w-4 h-4 text-admin-primary bg-black/50 border-white/10 focus:ring-admin-primary focus:ring-1 transition-all" onchange="toggleMovieSource()">
                        <span class="text-sm text-gray-300 group-hover:text-white transition-colors">Nhập thủ công (Slug)</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="radio" name="movie_source" value="newest" class="w-4 h-4 text-admin-primary bg-black/50 border-white/10 focus:ring-admin-primary focus:ring-1 transition-all" onchange="toggleMovieSource()">
                        <span class="text-sm text-gray-300 group-hover:text-white transition-colors">Tự động lấy 6 phim mới nhất</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="radio" name="movie_source" value="trending" class="w-4 h-4 text-admin-primary bg-black/50 border-white/10 focus:ring-admin-primary focus:ring-1 transition-all" onchange="toggleMovieSource()">
                        <span class="text-sm text-gray-300 group-hover:text-white transition-colors">Tự động lấy 6 phim Top View</span>
                    </label>
                </div>

                <div id="manual-slugs-container">
                    <input type="text" id="email-movie-slugs" placeholder="VD: one-piece, naruto, avengers-endgame" class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all">
                    <p class="text-xs text-gray-400 mt-2"><i data-lucide="help-circle" class="w-3 h-3 inline-block -mt-0.5"></i> Ngăn cách bằng dấu phẩy. Hệ thống sẽ tự lấy ảnh và tạo link xem phim.</p>
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="button" id="btn-send-email" onclick="startEmailCampaign()" class="bg-gradient-to-r from-admin-primary to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-[0_0_20px_rgba(244,63,94,0.3)] hover:shadow-[0_0_25px_rgba(244,63,94,0.5)] flex items-center gap-2">
                    <i data-lucide="mail" class="w-5 h-5"></i> Bắt Đầu Gửi Email
                </button>
            </div>
        </div>

        <!-- Progress Tracking -->
        <div id="progress-container" class="mt-8 hidden">
            <h3 class="text-lg font-bold text-white mb-2">Tiến Độ Gửi Email</h3>
            <div class="w-full bg-black/50 rounded-full h-4 mb-2 overflow-hidden border border-white/10">
                <div id="progress-bar" class="bg-gradient-to-r from-admin-primary to-rose-500 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-400">
                <span id="progress-text">Đang chuẩn bị...</span>
                <span id="progress-count">0 / 0</span>
            </div>
            <div id="error-log" class="mt-4 text-red-400 text-sm hidden"></div>
        </div>
    </div>
</div>

<script>
let totalMembers = 0;
let membersProcessed = 0;
let isSending = false;
let currentBatchPage = 1;
const batchLimit = 20;

function toggleMovieSource() {
    const source = document.querySelector('input[name="movie_source"]:checked').value;
    const container = document.getElementById('manual-slugs-container');
    if (source === 'manual') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function loadTemplate(type) {
    const subjectEl = document.getElementById('email-subject');
    const messageEl = document.getElementById('email-message');
    
    let sourceRadio = 'manual';
    
    switch (type) {
        case 'weekend':
            subjectEl.value = '🎬 Cuối tuần rồi, cày phim thôi!';
            messageEl.value = 'Chúc bạn một cuối tuần vui vẻ và thư giãn! Đừng quên rủ bạn bè, người thân hoặc người ấy cùng xem những bộ phim siêu cuốn mà PhimTop1 vừa cập nhật nhé.\n\nDưới đây là danh sách phim thịnh hành đáng xem nhất hiện tại:';
            sourceRadio = 'trending';
            break;
        case 'newest':
            subjectEl.value = '🔥 Phim mới vừa cập bến PhimTop1, xem ngay!';
            messageEl.value = 'Hàng loạt siêu phẩm mới nóng hổi đã được cập nhật trên hệ thống. Chuẩn bị bắp rang bơ và cày phim xuyên đêm cùng chúng tôi nào!\n\nĐiểm qua những phim vừa lên sóng:';
            sourceRadio = 'newest';
            break;
        case 'comeback':
            subjectEl.value = '❤️ Lâu rồi không gặp! Chúng tôi có vài siêu phẩm cho bạn...';
            messageEl.value = 'Dạo này bạn có bận rộn quá không? PhimTop1 rất nhớ bạn đấy! Rất nhiều tựa phim khủng đã được ra mắt kể từ lần cuối bạn ghé thăm.\n\nHãy dành chút thời gian giải trí và xem qua những gợi ý này nhé:';
            sourceRadio = 'trending';
            break;
        case 'event':
            subjectEl.value = '🎁 Quà Tặng Sự Kiện: Nhận thưởng xu xem phim thả ga!';
            messageEl.value = 'Sự kiện đặc biệt đang diễn ra! Đăng nhập ngay hôm nay để nhận thêm Xu Thưởng và mở khóa các tính năng VIP cùng nhiều phim độc quyền.\n\nVào xem phim và tham gia điểm danh nhận quà ngay:';
            sourceRadio = 'newest';
            break;
    }
    
    document.querySelector(`input[name="movie_source"][value="${sourceRadio}"]`).checked = true;
    toggleMovieSource();
    
    lucide.createIcons();
}

async function startEmailCampaign() {
    if (isSending) return;
    
    const subject = document.getElementById('email-subject').value.trim();
    const message = document.getElementById('email-message').value.trim();
    const movieSlugs = document.getElementById('email-movie-slugs').value.trim();
    const movieSource = document.querySelector('input[name="movie_source"]:checked').value;
    
    if (!subject || !message) {
        alert("Vui lòng nhập tiêu đề và nội dung.");
        return;
    }
    
    if (!confirm("Bạn có chắc chắn muốn bắt đầu gửi email cho toàn bộ thành viên? Quá trình này có thể mất một thời gian.")) {
        return;
    }
    
    document.getElementById('email-form').style.opacity = '0.5';
    document.getElementById('email-form').style.pointerEvents = 'none';
    document.getElementById('progress-container').classList.remove('hidden');
    document.getElementById('error-log').classList.add('hidden');
    document.getElementById('error-log').innerHTML = '';
    
    isSending = true;
    currentBatchPage = 1;
    membersProcessed = 0;
    
    try {
        const res = await fetch('ajax_email.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'get_total'})
        });
        const data = await res.json();
        
        if (data.error) throw new Error(data.error);
        
        totalMembers = data.total;
        document.getElementById('progress-count').innerText = `0 / ${totalMembers}`;
        
        if (totalMembers === 0) {
            alert("Không có thành viên nào trong hệ thống có email.");
            resetUI();
            return;
        }
        
        sendNextBatch();
    } catch (e) {
        showError("Lỗi khởi tạo: " + e.message);
        resetUI();
    }
}

async function sendNextBatch() {
    if (!isSending) return;
    
    document.getElementById('progress-text').innerText = `Đang gửi batch thứ ${currentBatchPage}...`;
    
    const subject = document.getElementById('email-subject').value.trim();
    const message = document.getElementById('email-message').value.trim();
    const movieSlugs = document.getElementById('email-movie-slugs').value.trim();
    
    const movieSource = document.querySelector('input[name="movie_source"]:checked').value;
    
    try {
        const res = await fetch('ajax_email.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'send_batch',
                page: currentBatchPage,
                limit: batchLimit,
                subject: subject,
                message: message,
                movieSource: movieSource,
                movieSlugs: movieSlugs
            })
        });
        
        const data = await res.json();
        
        if (data.error) {
            showError("Lỗi batch " + currentBatchPage + ": " + data.error);
            resetUI();
            return;
        }
        
        if (data.finished || data.count === 0 && currentBatchPage > 1) {
            // Done
            document.getElementById('progress-text').innerText = "Đã hoàn thành gửi email!";
            let pct = 100;
            document.getElementById('progress-bar').style.width = pct + '%';
            setTimeout(() => {
                alert("Đã gửi email thành công!");
                resetUI();
            }, 500);
            return;
        }
        
        membersProcessed += data.count;
        // In case count is less than limit, we might need to adjust or just wait for next batch to return finished
        
        // Update UI
        let displayCount = currentBatchPage * batchLimit;
        if (displayCount > totalMembers) displayCount = totalMembers;
        
        let pct = (displayCount / totalMembers) * 100;
        if (pct > 100) pct = 100;
        
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-count').innerText = `${displayCount} / ${totalMembers} (Thành công: ${membersProcessed})`;
        
        currentBatchPage++;
        
        // Delay between batches to prevent SMTP limits
        setTimeout(sendNextBatch, 1000);
        
    } catch (e) {
        showError("Lỗi ngoại lệ khi gửi batch " + currentBatchPage + ": " + e.message);
        resetUI();
    }
}

function showError(msg) {
    const el = document.getElementById('error-log');
    el.classList.remove('hidden');
    el.innerHTML += `<div>${msg}</div>`;
}

function resetUI() {
    isSending = false;
    document.getElementById('email-form').style.opacity = '1';
    document.getElementById('email-form').style.pointerEvents = 'auto';
}
</script>
