<?php
requireAdmin();
$settings = getSettings();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_app_settings') {
    $appApiKey = trim($_POST['appApiKey'] ?? '');
    $appBannerEnabled = isset($_POST['appBannerEnabled']) ? 1 : 0;
    $appDownloadUrl = trim($_POST['appDownloadUrl'] ?? '');
    $appInAppUpdateUrl = trim($_POST['appInAppUpdateUrl'] ?? '');
    $appDownloadUrlTv = trim($_POST['appDownloadUrlTv'] ?? '');
    
    $appSchemaEnabled = isset($_POST['appSchemaEnabled']) ? 1 : 0;
    $appSchemaName = trim($_POST['appSchemaName'] ?? '');
    $appSchemaOs = trim($_POST['appSchemaOs'] ?? '');
    $appSchemaCategory = trim($_POST['appSchemaCategory'] ?? '');
    $appSchemaPrice = trim($_POST['appSchemaPrice'] ?? '');
    $appSchemaCurrency = trim($_POST['appSchemaCurrency'] ?? '');
    $appSchemaRatingValue = trim($_POST['appSchemaRatingValue'] ?? '');
    $appSchemaRatingCount = trim($_POST['appSchemaRatingCount'] ?? '');
    
    updateSettings([
        'appApiKey' => $appApiKey,
        'appBannerEnabled' => $appBannerEnabled,
        'appDownloadUrl' => $appDownloadUrl,
        'appInAppUpdateUrl' => $appInAppUpdateUrl,
        'appDownloadUrlTv' => $appDownloadUrlTv,
        'appSchemaEnabled' => $appSchemaEnabled,
        'appSchemaName' => $appSchemaName,
        'appSchemaOs' => $appSchemaOs,
        'appSchemaCategory' => $appSchemaCategory,
        'appSchemaPrice' => $appSchemaPrice,
        'appLatestVersion' => trim($_POST['appLatestVersion'] ?? '1.0.0'),
        'appBuildNumber' => !empty($_POST['appBuildNumber']) ? (int)$_POST['appBuildNumber'] : 0,
        'appForceUpdate' => isset($_POST['appForceUpdate']) ? 1 : 0,
        
        'appLatestVersionIos' => trim($_POST['appLatestVersionIos'] ?? '1.0.0'),
        'appBuildNumberIos' => !empty($_POST['appBuildNumberIos']) ? (int)$_POST['appBuildNumberIos'] : 0,
        'appForceUpdateIos' => isset($_POST['appForceUpdateIos']) ? 1 : 0,
        'appDownloadUrlIos' => trim($_POST['appDownloadUrlIos'] ?? ''),

        'appUpdateMessage' => trim($_POST['appUpdateMessage'] ?? '')
    ]);
    
    $success = "Cập nhật cấu hình App thành công!";
    
    // Lưu ý: Tính năng upload google-services.json đã bị loại bỏ 
    // vì hệ thống chuyển sang mô hình Private (Closed CMS), 
    // Firebase được quản lý trực tiếp qua codebase bằng CLI.
    
    $settings = getSettings(); // Refresh
}
?>

<h2 class="text-2xl font-bold text-white mb-6">Cấu Hình Kết Nối App (API)</h2>

<?php if ($success): ?>
    <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-lg flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="max-w-4xl">
    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-800 mb-6 overflow-x-auto">
        <button type="button" onclick="switchTab('general')" class="tab-btn whitespace-nowrap px-5 py-3 text-sm font-medium text-white border-b-2 border-red-500 transition-colors" data-target="general">
            <i data-lucide="settings" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> Cài Đặt Chung
        </button>
        <button type="button" onclick="switchTab('schema')" class="tab-btn whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-400 hover:text-gray-200 border-b-2 border-transparent hover:border-gray-700 transition-colors" data-target="schema">
            <i data-lucide="code" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> App Schema
        </button>
        <button type="button" onclick="switchTab('guide')" class="tab-btn whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-400 hover:text-gray-200 border-b-2 border-transparent hover:border-gray-700 transition-colors" data-target="guide">
            <i data-lucide="book-open" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> Hướng Dẫn & API
        </button>
    </div>

    <!-- Top Block: Form for Settings -->
    <form method="POST" enctype="multipart/form-data" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 relative shadow-sm mb-6">
        <input type="hidden" name="action" value="update_app_settings">

        <!-- Tab Content: General -->
        <div id="tab-general" class="tab-pane block space-y-6">
            <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2">Thông tin kết nối & Tải App</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">App API Key</label>
                <div class="flex">
                    <input type="text" id="appApiKey" name="appApiKey" value="<?= htmlspecialchars($settings['appApiKey'] ?? '') ?>" placeholder="Chưa có mã bảo mật" class="w-full bg-gray-800 border border-gray-700 rounded-l-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none transition-shadow" readonly>
                    <button type="button" onclick="generateApiKey()" class="bg-gray-700 hover:bg-gray-600 border border-gray-700 border-l-0 rounded-r-lg px-4 py-2.5 text-sm font-medium text-white transition-colors flex-shrink-0" title="Tạo mã ngẫu nhiên">
                        <i data-lucide="refresh-cw" class="w-4 h-4 inline-block mr-1"></i> Tạo Mã Mới
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Mã này được hệ thống tạo ngẫu nhiên nhằm bảo mật các kết nối từ Native App tới hệ thống API. Bạn không thể tự nhập để tránh lộ lọt.</p>
            </div>

            <div class="border-t border-gray-800 pt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Link Tải App Mobile (Trang Web / Play Store)</label>
                <input type="text" name="appDownloadUrl" value="<?= htmlspecialchars($settings['appDownloadUrl'] ?? '') ?>" placeholder="https://..." class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <p class="text-xs text-gray-500 mt-2">Đường dẫn tải ứng dụng Mobile để hiển thị trên trình duyệt Web (Nút tải app). Nếu để trống, nút sẽ không hiển thị.</p>
            </div>

            <div class="border-t border-gray-800 pt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Link Tải Cập Nhật (In-App Update APK)</label>
                <input type="text" name="appInAppUpdateUrl" value="<?= htmlspecialchars($settings['appInAppUpdateUrl'] ?? '') ?>" placeholder="https://..." class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <p class="text-xs text-gray-500 mt-2">Đường dẫn file APK trực tiếp dùng cho tính năng tự động cập nhật bên trong App (In-App Updater). Nếu để trống sẽ sử dụng link tải App Mobile ở trên.</p>
            </div>

            <div class="border-t border-gray-800 pt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Link Tải App Android TV (APK)</label>
                <input type="text" name="appDownloadUrlTv" value="<?= htmlspecialchars($settings['appDownloadUrlTv'] ?? '') ?>" placeholder="https://..." class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none transition-shadow">
                <p class="text-xs text-gray-500 mt-2">Đường dẫn tải ứng dụng dành cho Smart TV. Nếu để trống, nút tải app TV trên web sẽ không hiển thị.</p>
            </div>

            <div class="border-t border-gray-800 pt-6">
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="appBannerEnabled" value="1" class="sr-only" <?= (!empty($settings['appBannerEnabled']) ? 'checked' : '') ?>>
                        <div class="block bg-gray-700 w-10 h-6 rounded-full checkbox-bg"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition checkbox-dot"></div>
                    </div>
                    <div class="ml-3 text-sm font-medium text-gray-300">
                        Bật gợi ý tải App trên Web (Smart App Banner)
                        <p class="text-xs text-gray-500 mt-1 font-normal">Khi người dùng truy cập web bằng trình duyệt điện thoại, một nút "Mở trong App" sẽ hiện ra.</p>
                    </div>
                </label>
            </div>

            <div class="border-t border-gray-800 pt-6 mt-6">
                <h4 class="text-md font-semibold text-white mb-4">Cơ Chế Cập Nhật Tự Động (Auto-Update App)</h4>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Thông Báo Cập Nhật Chung</label>
                    <textarea name="appUpdateMessage" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none" placeholder="Đã có phiên bản mới với nhiều tính năng hấp dẫn..."><?= htmlspecialchars($settings['appUpdateMessage'] ?? 'Đã có phiên bản mới, vui lòng cập nhật!') ?></textarea>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- ANDROID SECTION -->
                    <div class="bg-gray-800/30 border border-gray-700 p-4 rounded-xl">
                        <h5 class="text-green-500 font-semibold mb-3 flex items-center">
                            <i data-lucide="smartphone" class="w-4 h-4 mr-1.5"></i> Android
                        </h5>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1">Phiên Bản Mới (VD: 1.1.0)</label>
                                <input type="text" name="appLatestVersion" value="<?= htmlspecialchars($settings['appLatestVersion'] ?? '1.0.0') ?>" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:ring-1 focus:ring-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1">Build Number Mới (VD: 2)</label>
                                <input type="number" name="appBuildNumber" value="<?= htmlspecialchars($settings['appBuildNumber'] ?? '') ?>" placeholder="Tùy chọn" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:ring-1 focus:ring-green-500 outline-none">
                            </div>
                        </div>
                        <label class="flex items-center cursor-pointer mb-2">
                            <div class="relative">
                                <input type="checkbox" name="appForceUpdate" value="1" class="sr-only" <?= (!empty($settings['appForceUpdate']) ? 'checked' : '') ?>>
                                <div class="block bg-gray-700 w-10 h-6 rounded-full checkbox-bg"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition checkbox-dot"></div>
                            </div>
                            <div class="ml-2 text-xs font-medium text-gray-300">Bắt buộc cập nhật Android</div>
                        </label>
                        <p class="text-[11px] text-gray-500">Link tải Android sử dụng "Link Tải Cập Nhật (In-App Update)" ở trên.</p>
                    </div>

                    <!-- IOS SECTION -->
                    <div class="bg-gray-800/30 border border-gray-700 p-4 rounded-xl">
                        <h5 class="text-blue-500 font-semibold mb-3 flex items-center">
                            <i data-lucide="apple" class="w-4 h-4 mr-1.5"></i> iOS
                        </h5>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1">Phiên Bản Mới (VD: 1.1.0)</label>
                                <input type="text" name="appLatestVersionIos" value="<?= htmlspecialchars($settings['appLatestVersionIos'] ?? '1.0.0') ?>" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1">Build Number Mới (VD: 2)</label>
                                <input type="number" name="appBuildNumberIos" value="<?= htmlspecialchars($settings['appBuildNumberIos'] ?? '') ?>" placeholder="Tùy chọn" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:ring-1 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-400 mb-1">Link tải App Store / TestFlight</label>
                            <input type="text" name="appDownloadUrlIos" value="<?= htmlspecialchars($settings['appDownloadUrlIos'] ?? '') ?>" placeholder="https://apps.apple.com/..." class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="appForceUpdateIos" value="1" class="sr-only" <?= (!empty($settings['appForceUpdateIos']) ? 'checked' : '') ?>>
                                <div class="block bg-gray-700 w-10 h-6 rounded-full checkbox-bg"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition checkbox-dot"></div>
                            </div>
                            <div class="ml-2 text-xs font-medium text-gray-300">Bắt buộc cập nhật iOS</div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: App Schema -->
        <div id="tab-schema" class="tab-pane hidden">
            <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2">Cấu Hình SEO App Schema (SoftwareApplication)</h3>
            <p class="text-xs text-gray-500 mb-6">Mã Schema này giúp Google nhận diện website của bạn cung cấp một ứng dụng (Software), từ đó hiển thị kết quả tìm kiếm đẹp hơn kèm theo số sao đánh giá và giá tiền.</p>
            
            <div class="mb-6 bg-gray-800/30 p-4 rounded-lg border border-gray-700/50">
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="appSchemaEnabled" value="1" class="sr-only" <?= (!empty($settings['appSchemaEnabled']) ? 'checked' : '') ?>>
                        <div class="block bg-gray-700 w-10 h-6 rounded-full checkbox-bg"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition checkbox-dot"></div>
                    </div>
                    <div class="ml-3 text-sm font-medium text-gray-300">
                        Bật hiển thị thẻ meta App Schema trên giao diện web
                    </div>
                </label>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Tên Ứng Dụng (App Name)</label>
                    <input type="text" name="appSchemaName" value="<?= htmlspecialchars($settings['appSchemaName'] ?? '') ?>" placeholder="Để trống = lấy Tên Website" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Hệ Điều Hành Hỗ Trợ</label>
                    <input type="text" name="appSchemaOs" value="<?= htmlspecialchars($settings['appSchemaOs'] ?? 'Android, iOS') ?>" placeholder="VD: Android, iOS" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Thể Loại App</label>
                    <input type="text" name="appSchemaCategory" value="<?= htmlspecialchars($settings['appSchemaCategory'] ?? 'EntertainmentApplication') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Giá Tiền</label>
                        <input type="text" name="appSchemaPrice" value="<?= htmlspecialchars($settings['appSchemaPrice'] ?? '0') ?>" placeholder="0" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Đơn Vị Tiền</label>
                        <input type="text" name="appSchemaCurrency" value="<?= htmlspecialchars($settings['appSchemaCurrency'] ?? 'VND') ?>" placeholder="VND" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Điểm Đánh Giá Ảo (VD: 4.8)</label>
                    <input type="text" name="appSchemaRatingValue" value="<?= htmlspecialchars($settings['appSchemaRatingValue'] ?? '4.8') ?>" placeholder="4.8" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Số Lượng Đánh Giá (VD: 1250)</label>
                    <input type="text" name="appSchemaRatingCount" value="<?= htmlspecialchars($settings['appSchemaRatingCount'] ?? '1250') ?>" placeholder="1250" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-gray-300 focus:ring-1 focus:ring-red-500 outline-none">
                </div>
            </div>
        </div>

        <!-- The Submit Button is shared across General, Firebase, Schema tabs -->
        <div id="form-submit-container" class="mt-8 pt-6 border-t border-gray-800 block">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-6 rounded-lg transition-all shadow-lg shadow-red-600/20 flex items-center transform hover:-translate-y-0.5">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình
            </button>
        </div>
    </form>

    <!-- Tab Content: Guide & API (Outside the form since it's just info) -->
    <div id="tab-guide" class="tab-pane hidden">
        <?php
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $baseUrl = ($isHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-sm mb-6">
            <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2">Hướng dẫn Cấu Hình và Build App (Dành cho Developer/AI)</h3>
            
            <div class="space-y-4">
                <div class="bg-black/20 p-4 rounded-lg border border-gray-800/50">
                    <b class="text-blue-400 flex items-center"><i data-lucide="link" class="w-4 h-4 mr-2"></i>1. Cấu hình kết nối API trong Source Code Flutter</b>
                    <ol class="list-decimal list-inside space-y-2 mt-3 text-sm ml-1 text-gray-300">
                        <li>Mở file cấu hình trong source code Flutter tại: <br><code class="bg-gray-800 px-2 py-1 rounded text-green-400 font-mono text-xs inline-block mt-1 mb-2">phimtop1_flutter/lib/core/config.dart</code></li>
                        <li>Copy <b>App API Key</b> ở tab "Cài Đặt Chung" và dán vào biến <code class="bg-gray-800 px-1.5 py-0.5 rounded text-white font-mono text-xs">apiKey</code>.</li>
                        <li>Copy URL của website <code class="bg-gray-800 px-1.5 py-0.5 rounded text-white font-mono text-xs"><?= $baseUrl . '/' ?></code> và dán vào biến <code class="bg-gray-800 px-1.5 py-0.5 rounded text-white font-mono text-xs">baseUrl</code>.</li>
                    </ol>
                </div>

                <div class="bg-black/20 p-4 rounded-lg border border-gray-800/50">
                    <b class="text-blue-400 flex items-center"><i data-lucide="image" class="w-4 h-4 mr-2"></i>2. Đổi Tên và Logo Ứng Dụng</b>
                    <ul class="list-disc list-inside space-y-3 mt-3 text-sm ml-1 text-gray-300">
                        <li><b>Đổi tên App:</b> Mở file <code class="bg-gray-800 px-1.5 py-0.5 rounded text-green-400 font-mono text-xs inline-block">phimtop1_flutter/android/app/src/main/AndroidManifest.xml</code> <br>
                            Tìm và sửa nội dung bên trong thuộc tính <code class="text-yellow-400 text-xs">android:label="Tên App Của Bạn"</code>.
                        </li>
                        <li><b>Đổi Logo App:</b> Thay thế các file hình ảnh <code class="bg-gray-800 px-1.5 py-0.5 rounded text-white font-mono text-xs">ic_launcher.png</code> trong các thư mục <code class="text-xs">mipmap-*</code> nằm tại: <br>
                            <code class="bg-gray-800 px-2 py-1 rounded text-green-400 font-mono text-xs inline-block mt-1">phimtop1_flutter/android/app/src/main/res/</code>
                        </li>
                    </ul>
                </div>

                <div class="bg-black/20 p-4 rounded-lg border border-gray-800/50">
                    <b class="text-blue-400 flex items-center"><i data-lucide="smartphone" class="w-4 h-4 mr-2"></i>3. Lệnh Build App thành file cài đặt (APK / AAB)</b>
                    <div class="mt-3 text-sm text-gray-300 bg-gray-800/50 p-3 rounded font-mono text-xs space-y-3">
                        <div>
                            <span class="text-gray-500"># 1. Di chuyển vào thư mục code Flutter:</span><br>
                            <span class="text-green-400">cd</span> phimtop1_flutter
                        </div>
                        <div>
                            <span class="text-gray-500"># 2. Tải các package phụ thuộc:</span><br>
                            <span class="text-green-400">flutter</span> pub get
                        </div>
                        <div>
                            <span class="text-gray-500"># 3. Build ra file APK (Để cài trực tiếp lên điện thoại):</span><br>
                            <span class="text-green-400">flutter</span> build apk --release<br>
                            <span class="text-gray-500 italic mt-1 inline-block">File lưu tại: build/app/outputs/flutter-apk/app-release.apk</span>
                        </div>
                        <div>
                            <span class="text-gray-500"># 4. Build ra file AAB (App Bundle) (Dành cho việc upload lên Google Play):</span><br>
                            <span class="text-green-400">flutter</span> build appbundle --release
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2">Danh sách API Endpoints</h3>
            <p class="text-sm text-gray-400 mb-4">
                Danh sách các API hiện có để kết nối Native App.<br>
                <span class="text-yellow-400 mt-1 inline-block">Lưu ý Test bằng trình duyệt:</span> Nối thêm <code class="bg-gray-800 px-1 rounded">?key=MÃ_BẢO_MẬT</code> hoặc <code class="bg-gray-800 px-1 rounded">&key=MÃ_BẢO_MẬT</code> vào cuối link.
            </p>
            
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Khởi tạo (Init) <span class="text-gray-500 font-normal">- GET</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        <?= $baseUrl . '/api/v1/app_init.php' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Trang Chủ (Home) <span class="text-gray-500 font-normal">- GET</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        <?= $baseUrl . '/api/v1/home.php?page=1' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Chi Tiết Phim <span class="text-gray-500 font-normal">- GET</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        <?= $baseUrl . '/api/v1/movie.php?slug=ten-phim' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Lấy Toàn Bộ Thể Loại <span class="text-gray-500 font-normal">- GET</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        <?= $baseUrl . '/api/v1/categories.php' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Phim theo Thể Loại / Quốc Gia <span class="text-gray-500 font-normal">- GET</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        <?= $baseUrl . '/api/v1/category.php?type=the-loai&slug=hanh-dong&page=1' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Tìm Kiếm <span class="text-gray-500 font-normal">- GET</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        <?= $baseUrl . '/api/v1/search.php?keyword=batman&page=1' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Xác Thực (Login/Register) <span class="text-gray-500 font-normal">- POST</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        <?= $baseUrl . '/api/v1/auth.php?action=login' ?><br>
                        <?= $baseUrl . '/api/v1/auth.php?action=register' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Theo Dõi (Follow) <span class="text-gray-500 font-normal">- GET/POST</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        GET: <?= $baseUrl . '/api/v1/follow.php?action=list&type=movie' ?><br>
                        POST: <?= $baseUrl . '/api/v1/follow.php?action=toggle' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Bình Luận (Comments) <span class="text-gray-500 font-normal">- GET/POST</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        GET: <?= $baseUrl . '/api/v1/comments.php?slug=ten-phim' ?><br>
                        POST: <?= $baseUrl . '/api/v1/comments.php' ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Lịch Sử (History) <span class="text-gray-500 font-normal">- GET/POST</span>:</label>
                    <div class="bg-gray-950 border border-gray-800 rounded p-2 text-xs text-gray-400 font-mono break-all select-all">
                        GET: <?= $baseUrl . '/api/v1/history.php?action=list' ?><br>
                        POST: <?= $baseUrl . '/api/v1/history.php?action=add' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Toggle switch CSS */
    input:checked ~ .checkbox-bg {
        background-color: #ef4444; /* red-500 */
    }
    input:checked ~ .checkbox-dot {
        transform: translateX(100%);
    }
</style>

<script>
    function switchTab(tabId) {
        // Hide all panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('block');
            pane.classList.add('hidden');
        });
        
        // Reset all buttons styling
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-white', 'border-red-500');
            btn.classList.add('text-gray-400', 'border-transparent');
        });

        // Show active pane
        const activePane = document.getElementById('tab-' + tabId);
        if (activePane) {
            activePane.classList.remove('hidden');
            activePane.classList.add('block');
        }

        // Highlight active button
        const activeBtn = document.querySelector(`.tab-btn[data-target="${tabId}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('text-gray-400', 'border-transparent');
            activeBtn.classList.add('text-white', 'border-red-500');
        }
        
        // Hide submit button for Guide tab (as it has no form fields)
        const submitContainer = document.getElementById('form-submit-container');
        if (submitContainer) {
            if (tabId === 'guide') {
                submitContainer.style.display = 'none';
            } else {
                submitContainer.style.display = 'block';
            }
        }
    }

    function generateApiKey() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = 'pt1_';
        for (let i = 0; i < 32; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('appApiKey').value = result;
        
        // Add a subtle highlight effect to show it changed
        const input = document.getElementById('appApiKey');
        input.classList.add('ring-2', 'ring-red-500');
        setTimeout(() => {
            input.classList.remove('ring-2', 'ring-red-500');
        }, 500);
    }
</script>
