import os
import re

# 1. Read member.php
with open('themes/phimhayok/member.php', 'r', encoding='utf-8') as f:
    member_html = f.read()

# 2. Extract common parts
# The header, footer, etc.
# We will split member_html into top and bottom parts.
# The middle part is the tabs and the form.
header_split = member_html.split('<div class="flex bg-gray-900 border border-white/10 rounded-2xl p-1 mb-8">')
top_part = header_split[0]
bottom_part = member_html.split('</div>\n\n                \n            <?php endif; ?>')[1]

# We need to replace the form in top_part to be specific to login/register

login_form = """
                    <div class="mb-8 text-center">
                        <h2 class="text-3xl font-bold text-white mb-2">Đăng Nhập</h2>
                        <p class="text-gray-400">Chào mừng bạn quay lại với hệ thống</p>
                    </div>

                    <form method="POST" action="/api/auth.php" id="auth-form" class="space-y-5">
                        <input type="hidden" name="action" id="action-input" value="login">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                            <div class="relative">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="email" name="email" required class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow  placeholder-gray-600" placeholder="bạn@domain.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2 flex justify-between">
                                <span>Mật khẩu</span>
                                <a href="/forgot_password.php" id="forgot-link" class="text-xs text-phim-yellow hover:text-white block">Quên mật khẩu?</a>
                            </label>
                            <div class="relative">
                                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="password" name="password" required class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow  placeholder-gray-600" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full bg-phim-yellow hover:bg-yellow-400 text-black font-bold py-3 px-4 rounded-xl  shadow-[0_0_15px_rgba(234,179,8,0.3)] flex items-center justify-center">
                            <i data-lucide="log-in" class="w-5 h-5 mr-2" id="submit-icon"></i> 
                            <span id="submit-text">Đăng Nhập</span>
                        </button>
                    </form>

                    <?php do_action('social_login_buttons'); ?>
                    
                    <div class="mt-6 text-center text-gray-400 text-sm">
                        Chưa có tài khoản? <a href="/register.php" class="text-phim-yellow hover:text-white font-bold transition-colors">Đăng ký ngay</a>
                    </div>
"""

register_form = """
                    <div class="mb-8 text-center">
                        <h2 class="text-3xl font-bold text-white mb-2">Đăng Ký</h2>
                        <p class="text-gray-400">Tạo tài khoản để xem phim không giới hạn</p>
                    </div>

                    <form method="POST" action="/api/auth.php" id="auth-form" class="space-y-5">
                        <input type="hidden" name="action" id="action-input" value="register">
                        
                        <div id="name-field" class="block">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Tên hiển thị</label>
                            <div class="relative">
                                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="text" name="name" required class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow  placeholder-gray-600" placeholder="Nguyễn Văn A">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                            <div class="relative">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="email" name="email" required class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow  placeholder-gray-600" placeholder="bạn@domain.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2 flex justify-between">
                                <span>Mật khẩu</span>
                            </label>
                            <div class="relative">
                                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                                <input type="password" name="password" required class="w-full bg-[#1a1a1a] border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white focus:outline-none focus:border-phim-yellow  placeholder-gray-600" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full bg-phim-yellow hover:bg-yellow-400 text-black font-bold py-3 px-4 rounded-xl  shadow-[0_0_15px_rgba(234,179,8,0.3)] flex items-center justify-center">
                            <i data-lucide="user-plus" class="w-5 h-5 mr-2" id="submit-icon"></i> 
                            <span id="submit-text">Đăng Ký Tài Khoản</span>
                        </button>
                    </form>

                    <?php do_action('social_login_buttons'); ?>
                    
                    <div class="mt-6 text-center text-gray-400 text-sm">
                        Đã có tài khoản? <a href="/login.php" class="text-phim-yellow hover:text-white font-bold transition-colors">Đăng nhập</a>
                    </div>
"""

# Modify top part to remove member logic
login_html = top_part + login_form + '</div>' + bottom_part
register_html = top_part + register_form + '</div>' + bottom_part

# Remove the setMode function from bottom_part for login and register
login_html = re.sub(r'var currentMode.*?lucide\.createIcons\(\);\n        }', '', login_html, flags=re.DOTALL)
register_html = re.sub(r'var currentMode.*?lucide\.createIcons\(\);\n        }', '', register_html, flags=re.DOTALL)

with open('themes/phimhayok/login.php', 'w', encoding='utf-8') as f:
    f.write(login_html)

with open('themes/phimhayok/register.php', 'w', encoding='utf-8') as f:
    f.write(register_html)

# Create root login.php and register.php
root_login = """<?php
session_start();
require_once __DIR__ . '/includes/db.php';
checkSetup();

$settings = getSettings();

if (isset($_SESSION['user'])) {
    header("Location: /");
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// Support social login plugin callback
do_action('admin_login_auth', $_GET['action'] ?? '');

$theme = $settings['theme'] ?? 'phimhayok';
$themeFile = __DIR__ . "/themes/{$theme}/login.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/login.php";
}
"""

root_register = """<?php
session_start();
require_once __DIR__ . '/includes/db.php';
checkSetup();

$settings = getSettings();

if (isset($_SESSION['user'])) {
    header("Location: /");
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

$theme = $settings['theme'] ?? 'phimhayok';
$themeFile = __DIR__ . "/themes/{$theme}/register.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/register.php";
}
"""

with open('login.php', 'w', encoding='utf-8') as f:
    f.write(root_login)

with open('register.php', 'w', encoding='utf-8') as f:
    f.write(root_register)

print("Created login and register pages.")
