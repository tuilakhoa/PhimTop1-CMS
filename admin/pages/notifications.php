<h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-8 tracking-tight">Push Notifications (FCM)</h2>

<div class="bg-admin-panel backdrop-blur-xl border border-admin-border rounded-[2rem] p-8 mb-10 shadow-2xl relative overflow-hidden group">
    <div class="absolute -top-32 -right-32 w-64 h-64 bg-rose-500/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-rose-500/10 transition-colors duration-700"></div>
    
    <div class="relative z-10">
        <div class="mb-6 bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 flex items-start text-blue-400">
            <i data-lucide="info" class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0"></i>
            <div class="text-sm">
                <p class="font-bold mb-1">Gửi thông báo tới Ứng dụng Di động & Trình duyệt Web</p>
                <p class="text-blue-300">Nhập tiêu đề và nội dung để bắn thông báo ngay lập tức. Tính năng này yêu cầu đã kết nối Firebase Server Key.</p>
            </div>
        </div>

        <form action="" method="POST" class="space-y-6">
            <input type="hidden" name="action" value="send_push">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Tiêu Đề Thông Báo</label>
                    <input type="text" name="title" required placeholder="VD: Tập mới: One Piece - Tập 1071" class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Đường Dẫn (URL)</label>
                    <input type="text" name="url" placeholder="VD: https://phimtop1.com/phim/one-piece" class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Nội Dung Chi Tiết</label>
                <textarea name="message" required rows="3" placeholder="Luffy đã thức tỉnh Gear 5, vào xem ngay thôi!" class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all custom-scrollbar"></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Link Ảnh Banner (Tùy chọn)</label>
                <input type="text" name="image" placeholder="https://..." class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all">
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-gradient-to-r from-admin-primary to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-[0_0_20px_rgba(244,63,94,0.3)] hover:shadow-[0_0_25px_rgba(244,63,94,0.5)] flex items-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i> Gửi Thông Báo Bằng FCM
                </button>
            </div>
        </form>
    </div>
</div>

<h3 class="text-xl font-bold text-white mb-6 flex items-center mt-12 gap-3 drop-shadow-md"><div class="p-2 bg-purple-500/10 rounded-lg border border-purple-500/20"><i data-lucide="history" class="w-5 h-5 text-purple-400"></i></div> Lịch Sử Đã Gửi</h3>

<div class="bg-admin-panel backdrop-blur-2xl border border-admin-border rounded-[2rem] overflow-hidden shadow-2xl relative">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-400 relative z-10">
            <thead class="text-[11px] text-gray-400 uppercase tracking-widest bg-black/40 backdrop-blur-md border-b border-white/5">
                <tr>
                    <th scope="col" class="px-6 py-5 font-bold">Thời gian</th>
                    <th scope="col" class="px-6 py-5 font-bold">Tiêu đề</th>
                    <th scope="col" class="px-6 py-5 font-bold">Nội dung</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <tr>
                    <td class="px-6 py-4" colspan="4">
                        <div class="text-center text-gray-500 py-8">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>Chưa có lịch sử gửi thông báo.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
