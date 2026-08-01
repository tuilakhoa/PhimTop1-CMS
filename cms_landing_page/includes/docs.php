    <!-- Documentation Section -->
    <section id="docs" class="section" style="background: #070707; border-top: 1px solid var(--color-border);">
        <div class="container">
            <div class="text-center" style="margin-bottom: 64px;">
                <h2 class="title-lg">Hướng Dẫn Cấu Hình Cloudflare API</h2>
                <p class="subtitle">Kết nối dữ liệu thực tế từ Cloudflare Turnstile & Web Analytics thẳng vào Dashboard của bạn.</p>
            </div>
            
            <div class="grid-3 docs-grid">
                <!-- Step 1 -->
                <div class="doc-card step-1">
                    <div class="doc-card-glow"></div>
                    <div class="doc-number">1</div>
                    <h3 class="title-md">Tạo API Token</h3>
                    <ul class="doc-list">
                        <li>Đăng nhập vào trang quản trị Cloudflare.</li>
                        <li>Ở góc phải trên cùng, nhấn vào biểu tượng Avatar > chọn <strong>My Profile</strong>.</li>
                        <li>Chuyển sang tab <strong>API Tokens</strong> ở menu bên trái.</li>
                        <li>Nhấn <strong>Create Token</strong> > <strong>Create Custom Token</strong>.</li>
                        <li>Cấp quyền: <br><strong>Account > Account Analytics > Read</strong><br><strong>Zone > Analytics > Read</strong>.</li>
                        <li>Lưu lại chuỗi <span class="code-tag text-red">API Token</span> vừa tạo.</li>
                    </ul>
                </div>
                
                <!-- Step 2 -->
                <div class="doc-card step-2">
                    <div class="doc-card-glow"></div>
                    <div class="doc-number">2</div>
                    <h3 class="title-md">Lấy ID Hệ Thống</h3>
                    <ul class="doc-list">
                        <li>Quay lại màn hình chính của Cloudflare, chọn tên miền (Domain) website của bạn.</li>
                        <li>Cuộn xuống góc dưới cùng bên phải của trang <strong>Overview</strong>.</li>
                        <li>Bạn sẽ thấy phần <strong>API</strong> chứa hai chuỗi ký tự quan trọng.</li>
                        <li>Copy chuỗi <span class="code-tag text-blue">Account ID</span>.</li>
                        <li>Copy chuỗi <span class="code-tag text-blue">Zone ID</span>.</li>
                    </ul>
                </div>

                <!-- Step 3 -->
                <div class="doc-card step-3">
                    <div class="doc-card-glow"></div>
                    <div class="doc-number">3</div>
                    <h3 class="title-md">Nhập vào CMS</h3>
                    <ul class="doc-list">
                        <li>Đăng nhập vào trang quản trị Admin của PhimTop1.</li>
                        <li>Chuyển đến menu <strong>Cài Đặt > Bảo Mật</strong>.</li>
                        <li>Tìm đến mục <strong>Tích hợp Cloudflare API</strong>.</li>
                        <li>Dán lần lượt <span class="code-tag text-white-code">API Token</span>, <span class="code-tag text-white-code">Account ID</span> và <span class="code-tag text-white-code">Zone ID</span> vào các ô tương ứng.</li>
                        <li>Lưu cấu hình và trở về trang <strong>Tổng Quan</strong> để xem số liệu thống kê realtime.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
