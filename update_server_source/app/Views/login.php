<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Update Server</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-600/20 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="w-full max-w-md p-8 bg-gray-900 rounded-3xl border border-gray-800 shadow-2xl relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-tr from-blue-600 to-purple-600 rounded-2xl mx-auto flex items-center justify-center mb-4 shadow-lg shadow-blue-500/30">
                <i data-lucide="server" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">Update Server</h1>
            <p class="text-gray-400 mt-2">Đăng nhập để quản lý các bản cập nhật CMS.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl flex items-center">
                <i data-lucide="alert-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <p class="text-sm font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/login" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">Tên Đăng Nhập</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <input type="text" name="username" required placeholder="Nhập tên đăng nhập" class="w-full bg-gray-950 border border-gray-800 rounded-xl py-3 pl-11 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-600">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">Mật Khẩu</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <input type="password" name="password" required placeholder="Nhập mật khẩu" class="w-full bg-gray-950 border border-gray-800 rounded-xl py-3 pl-11 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-600">
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 group">
                Đăng Nhập
                <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
