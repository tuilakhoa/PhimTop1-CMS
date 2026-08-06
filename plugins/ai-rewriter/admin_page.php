<h2 class="text-2xl font-bold text-white mb-6">Tích hợp Trí Tuệ Nhân Tạo (AI)</h2>
<p class="text-gray-400 mb-6">Cấu hình API Key của các nhà cung cấp AI để sử dụng tính năng "Viết lại Mô tả bằng AI" và các tính năng tạo nội dung khác.</p>

<form method="POST" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-3xl relative">
    <input type="hidden" name="action" value="update_settings">
    
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2 flex items-center">
            <i data-lucide="bot" class="w-5 h-5 mr-2 text-purple-500"></i> Cấu hình chung AI
        </h3>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Mô Hình Đang Sử Dụng</label>
        <select name="aiProvider" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-purple-500 outline-none transition-shadow appearance-none">
            <option value="gemini" <?= ($settings['aiProvider'] ?? 'gemini') === 'gemini' ? 'selected' : '' ?>>Google Gemini (Khuyên dùng)</option>
            <option value="openai" <?= ($settings['aiProvider'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI ChatGPT</option>
        </select>
        <p class="text-xs text-gray-500 mt-2">Tính năng sinh nội dung trên hệ thống sẽ sử dụng mô hình được chọn ở trên làm mặc định.</p>
    </div>

    <div class="mb-8">
        <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2 flex items-center">
            <img src="https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg" class="w-5 h-5 mr-2" alt="Gemini"> Google Gemini
        </h3>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">Gemini API Key</label>
        <input type="password" name="geminiApiKey" value="<?= htmlspecialchars($settings['geminiApiKey'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-purple-500 outline-none transition-shadow" placeholder="AIzaSy...">
        <p class="text-xs text-gray-500 mt-2">Lấy API Key miễn phí tại <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-purple-400 hover:underline">Google AI Studio</a>.</p>
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-800 pb-2 flex items-center">
            <i data-lucide="cpu" class="w-5 h-5 mr-2 text-green-500"></i> OpenAI ChatGPT
        </h3>
        <label class="block text-sm font-medium text-gray-300 mb-1.5">OpenAI API Key</label>
        <input type="password" name="openaiApiKey" value="<?= htmlspecialchars($settings['openaiApiKey'] ?? '') ?>" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-1 focus:ring-purple-500 outline-none transition-shadow" placeholder="sk-proj-...">
        <p class="text-xs text-gray-500 mt-2">Lấy API Key tại <a href="https://platform.openai.com/api-keys" target="_blank" class="text-purple-400 hover:underline">OpenAI Platform</a>.</p>
    </div>

    <div class="mt-8 pt-6 border-t border-gray-800">
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2.5 px-6 rounded-lg transition-all shadow-lg shadow-purple-600/20 flex items-center transform hover:-translate-y-0.5">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> Lưu Cấu Hình AI
        </button>
    </div>
</form>
