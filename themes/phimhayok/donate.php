<?php include __DIR__ . '/header.php'; ?>

<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1200px] py-10 mt-20">
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-5xl font-bold text-white mb-4"><span class="text-[#fcc526]">VIP</span> & Ủng hộ (Donate)</h1>
        <p class="text-gray-400 max-w-2xl mx-auto">Đóng góp của bạn giúp chúng mình duy trì máy chủ, mua thêm băng thông và không ngừng cải thiện trải nghiệm xem phim hoàn toàn miễn phí.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <!-- Đóng góp tuỳ tâm -->
        <div class="bg-[#141414] rounded-2xl border border-gray-800 p-8 shadow-xl hover:border-gray-600 transition-colors">
            <div class="w-16 h-16 bg-gray-800 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                <i data-lucide="coffee" class="w-8 h-8 text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-white mb-3">Mời Admin 1 ly Cà phê</h2>
            <p class="text-gray-400 text-sm mb-6 leading-relaxed">Bạn có thể donate tuỳ tâm. Bất kỳ khoản đóng góp nào cũng đều là nguồn động lực to lớn với team phát triển.</p>
            
            <ul class="space-y-3 mb-8">
                <li class="flex items-start text-sm text-gray-300"><i data-lucide="check" class="w-4 h-4 text-green-500 mr-2 mt-0.5"></i> Nhận huy hiệu [Fan cứng] trên phần bình luận</li>
                <li class="flex items-start text-sm text-gray-300"><i data-lucide="check" class="w-4 h-4 text-green-500 mr-2 mt-0.5"></i> Cảm nhận sự đóng góp vào cộng đồng</li>
            </ul>

            <button onclick="showBankQr(20000, 'Donate')" class="w-full py-3 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-xl transition-colors">
                Ủng hộ 20.000 VNĐ
            </button>
        </div>

        <!-- Mua gói VIP -->
        <div class="bg-[#1a1a1a] rounded-2xl border-2 border-[#fcc526] p-8 shadow-[0_0_20px_rgba(252,197,38,0.15)] relative overflow-hidden">
            <div class="absolute top-0 right-0 bg-[#fcc526] text-black text-xs font-bold px-3 py-1 rounded-bl-lg">Khuyên dùng</div>
            
            <div class="w-16 h-16 bg-[#fcc526]/20 rounded-2xl flex items-center justify-center mb-6 border border-[#fcc526]/30">
                <i data-lucide="crown" class="w-8 h-8 text-[#fcc526]"></i>
            </div>
            <h2 class="text-2xl font-bold text-white mb-3">Nâng cấp VIP (1 Tháng)</h2>
            <p class="text-gray-400 text-sm mb-6 leading-relaxed">Mở khóa toàn bộ trải nghiệm cao cấp nhất trên cả Website và Ứng dụng điện thoại.</p>
            
            <div class="text-3xl font-bold text-[#fcc526] mb-6">49.000 <span class="text-sm font-normal text-gray-500 line-through ml-1">99.000đ</span><span class="text-sm text-gray-400 font-normal"> / tháng</span></div>

            <ul class="space-y-3 mb-8">
                <li class="flex items-start text-sm text-white font-medium"><i data-lucide="check" class="w-4 h-4 text-[#fcc526] mr-2 mt-0.5"></i> Tắt 100% quảng cáo banner/popup</li>
                <li class="flex items-start text-sm text-white font-medium"><i data-lucide="check" class="w-4 h-4 text-[#fcc526] mr-2 mt-0.5"></i> Mở khóa tính năng Tải phim Offline (trên App)</li>
                <li class="flex items-start text-sm text-white font-medium"><i data-lucide="check" class="w-4 h-4 text-[#fcc526] mr-2 mt-0.5"></i> Phát video trong nền (Picture-in-Picture)</li>
                <li class="flex items-start text-sm text-white font-medium"><i data-lucide="check" class="w-4 h-4 text-[#fcc526] mr-2 mt-0.5"></i> Được quyền "Tạo phòng" Xem Chung (Watch Party)</li>
            </ul>

            <button onclick="showBankQr(49000, 'VIP1M')" class="w-full py-3 bg-[#fcc526] hover:bg-yellow-500 text-black font-bold rounded-xl transition-colors shadow-lg">
                Nâng cấp VIP ngay
            </button>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div id="qr-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center">
    <div class="bg-[#141414] border border-gray-800 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl scale-95 transition-transform duration-300" id="qr-modal-content">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white flex items-center"><i data-lucide="qr-code" class="w-5 h-5 mr-2 text-[#fcc526]"></i> Quét mã thanh toán</h3>
            <button onclick="closeQrModal()" class="text-gray-500 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-6 text-center">
            <p class="text-sm text-gray-400 mb-4">Mở app Ngân hàng hoặc MoMo để quét mã</p>
            <div class="bg-white p-2 rounded-xl inline-block mb-4">
                <img id="vietqr-img" src="" alt="QR Code" class="w-48 h-48">
            </div>
            <div class="bg-[#1a1a1a] p-3 rounded-lg border border-gray-800 text-sm mb-4">
                <div class="flex justify-between mb-1"><span class="text-gray-500">Số tiền:</span> <span class="text-[#fcc526] font-bold" id="qr-amount">0đ</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Nội dung:</span> <span class="text-white font-mono" id="qr-content">...</span></div>
            </div>
            <p class="text-[11px] text-gray-500">Sau khi chuyển khoản thành công, tài khoản của bạn sẽ được nâng cấp tự động trong 1-5 phút.</p>
        </div>
        <div class="p-4 border-t border-gray-800 text-center">
            <button onclick="closeQrModal()" class="w-full py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors">Đóng</button>
        </div>
    </div>
</div>

<script>
    <?php 
        $userEmail = $_SESSION['user']['email'] ?? 'guest';
        $userPrefix = substr(md5($userEmail), 0, 6);
    ?>
    function showBankQr(amount, type) {
        <?php if(!isset($_SESSION['user'])): ?>
            alert('Vui lòng đăng nhập để nâng cấp VIP / Donate!');
            window.location.href = '/login.php';
            return;
        <?php endif; ?>
        
        const txCode = type + ' <?= $userPrefix ?>';
        // Sử dụng VietQR API (Thay stk và bank_id bằng thông tin thật của admin)
        const bankId = '970436'; // Vietcombank
        const accountNo = '0123456789';
        const accountName = 'ADMIN_PHIMTOP1';
        
        const qrUrl = `https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.jpg?amount=${amount}&addInfo=${encodeURIComponent(txCode)}&accountName=${accountName}`;
        
        document.getElementById('vietqr-img').src = qrUrl;
        document.getElementById('qr-amount').innerText = amount.toLocaleString('vi-VN') + 'đ';
        document.getElementById('qr-content').innerText = txCode;
        
        const modal = document.getElementById('qr-modal');
        const content = document.getElementById('qr-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
        
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    
    function closeQrModal() {
        const modal = document.getElementById('qr-modal');
        const content = document.getElementById('qr-modal-content');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }
</script>

<?php include __DIR__ . '/footer.php'; ?>
