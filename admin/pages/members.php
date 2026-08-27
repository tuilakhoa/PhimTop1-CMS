<?php
require_once __DIR__ . '/../../includes/db.php';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';

if ($action === 'history') {
    $email = $_GET['email'] ?? '';
    if (!$email) {
        echo "<div class='p-6 text-red-500'>Thiếu email người dùng.</div>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<div class='p-6 text-red-500'>Người dùng không tồn tại.</div>";
        exit;
    }

    $historyStmt = $pdo->prepare("SELECT * FROM watch_history WHERE user_email = ? ORDER BY updated_at DESC");
    $historyStmt->execute([$email]);
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="p-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <a href="?page=members" class="text-gray-400 hover:text-white transition-colors bg-gray-800 p-2 rounded-xl border border-gray-700">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center">
                        <i data-lucide="history" class="w-6 h-6 mr-2 text-blue-500"></i> Lịch Sử Xem Phim
                    </h1>
                    <p class="text-gray-400 text-sm mt-1">Của thành viên: <span class="text-blue-400 font-semibold"><?= htmlspecialchars($user['name']) ?></span> (<?= htmlspecialchars($email) ?>)</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
            <?php if (empty($history)): ?>
                <div class="p-8 text-center">
                    <i data-lucide="film" class="w-12 h-12 text-gray-700 mx-auto mb-3"></i>
                    <p class="text-gray-500">Người dùng này chưa xem bộ phim nào.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-sm text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-800/50 border-b border-gray-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Tên Phim</th>
                                <th class="px-6 py-4 font-semibold">Đang Xem Tập</th>
                                <th class="px-6 py-4 font-semibold text-right">Lần Cuối Truy Cập</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <?php foreach ($history as $h): ?>
                                <tr class="hover:bg-gray-800/50 transition-colors group">
                                    <td class="px-6 py-4 font-medium text-gray-200">
                                        <a href="/watch.php?slug=<?= htmlspecialchars($h['movie_slug']) ?>&ep=<?= htmlspecialchars($h['episode_name']) ?>" target="_blank" class="hover:text-blue-400 transition-colors flex items-center">
                                            <?= htmlspecialchars($h['movie_name']) ?>
                                            <i data-lucide="external-link" class="w-3 h-3 ml-2 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-blue-500/10 text-blue-400 px-2.5 py-1 rounded-lg text-xs font-semibold border border-blue-500/20">
                                            <?= htmlspecialchars($h['episode_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs">
                                        <?= date('d/m/Y H:i', strtotime($h['updated_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
} else if ($action === 'toggle_admin') {
    $email = $_GET['email'] ?? '';
    if ($email) {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT role FROM members WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $newRole = ($user['role'] === 'admin') ? 'user' : 'admin';
                $pdo->prepare("UPDATE members SET role = ? WHERE email = ?")->execute([$newRole, $email]);
            }
        } else {
            $config = getDbConfig();
            if ($config && isset($config['type']) && $config['type'] === 'firestore') {
                require_once __DIR__ . '/../../includes/firestore_helper.php';
                $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
                $memberId = md5($email);
                $user = $fs->getDocument('members', $memberId);
                if ($user) {
                    $newRole = (($user['role'] ?? 'user') === 'admin') ? 'user' : 'admin';
                    $user['role'] = $newRole;
                    $fs->setDocument('members', $memberId, $user);
                }
            }
        }
    }
    echo "<script>window.location.href='?page=members';</script>";
    exit;
} else {
    // List view
    if ($pdo) {
        $stmt = $pdo->query("SELECT m.*, (SELECT COUNT(*) FROM watch_history w WHERE w.user_email = m.email) as watched_count FROM members m ORDER BY m.created_at DESC");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $config = getDbConfig();
        if ($config && isset($config['type']) && $config['type'] === 'firestore') {
            require_once __DIR__ . '/../../includes/firestore_helper.php';
            $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
            $members = $fs->getAllDocuments('members');
            foreach ($members as &$m) {
                $m['watched_count'] = 'N/A';
                $m['created_at'] = $m['created_at'] ?? date('Y-m-d H:i:s');
            }
        } else {
            $members = [];
        }
    }
    ?>
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <i data-lucide="users" class="w-6 h-6 mr-2 text-red-500"></i> Quản Lý Thành Viên
                </h1>
                <p class="text-gray-400 text-sm mt-1">Quản lý tài khoản người dùng và theo dõi lịch sử xem phim</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="bg-gray-800 text-gray-300 px-4 py-2 rounded-xl border border-gray-700 text-sm font-medium">
                    Tổng cộng: <?= count($members) ?> thành viên
                </span>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm text-gray-400">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-800/50 border-b border-gray-800">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Người Dùng</th>
                            <th class="px-6 py-4 font-semibold">Vai Trò</th>
                            <th class="px-6 py-4 font-semibold">Loại Tài Khoản</th>
                            <th class="px-6 py-4 font-semibold text-center">Đăng Nhập Cuối</th>
                            <th class="px-6 py-4 font-semibold text-center">Số Phim Đã Xem</th>
                            <th class="px-6 py-4 font-semibold">Ngày Tham Gia</th>
                            <th class="px-6 py-4 font-semibold text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php foreach ($members as $m): ?>
                            <tr class="hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <img src="<?= htmlspecialchars($m['avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($m['name']).'&background=random') ?>" class="w-10 h-10 rounded-full border border-gray-700 object-cover">
                                        <div>
                                            <p class="font-bold text-white"><?= htmlspecialchars($m['name']) ?></p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($m['email']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (($m['role'] ?? 'user') === 'admin'): ?>
                                        <span class="inline-flex items-center bg-red-500/10 text-red-500 px-2.5 py-1 rounded-lg text-xs font-bold border border-red-500/20">Admin</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center bg-gray-500/10 text-gray-400 px-2.5 py-1 rounded-lg text-xs font-semibold border border-gray-500/20">User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (empty($m['password'])): ?>
                                        <span class="inline-flex items-center space-x-1 bg-white/10 text-gray-200 px-2.5 py-1 rounded-lg text-xs font-semibold border border-white/20">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                            <span>Google</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center space-x-1 bg-green-500/10 text-green-400 px-2.5 py-1 rounded-lg text-xs font-semibold border border-green-500/20">
                                            <i data-lucide="mail" class="w-3 h-3"></i>
                                            <span>Email</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php 
                                    $method = $m['login_method'] ?? '';
                                    if ($method === 'google'): ?>
                                        <span class="inline-flex items-center space-x-1 bg-white/10 text-gray-200 px-2.5 py-1 rounded-lg text-xs font-semibold border border-white/20">
                                            <span>Google</span>
                                        </span>
                                    <?php elseif ($method === 'biometric'): ?>
                                        <span class="inline-flex items-center space-x-1 bg-purple-500/10 text-purple-400 px-2.5 py-1 rounded-lg text-xs font-semibold border border-purple-500/20">
                                            <i data-lucide="fingerprint" class="w-3 h-3"></i>
                                            <span>Sinh trắc học</span>
                                        </span>
                                    <?php elseif ($method === 'email'): ?>
                                        <span class="inline-flex items-center space-x-1 bg-blue-500/10 text-blue-400 px-2.5 py-1 rounded-lg text-xs font-semibold border border-blue-500/20">
                                            <i data-lucide="mail" class="w-3 h-3"></i>
                                            <span>Mật khẩu</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-xs italic">Chưa rõ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-gray-800 text-gray-300 px-2.5 py-1 rounded-lg text-xs font-bold border border-gray-700">
                                        <?= $m['watched_count'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <?= date('d/m/Y', strtotime($m['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end space-x-2">
                                    <a href="?page=members&action=history&email=<?= urlencode($m['email']) ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-600/10 hover:bg-blue-600/20 text-blue-500 border border-blue-600/20 rounded-lg transition-colors text-xs font-semibold">
                                        <i data-lucide="history" class="w-3 h-3 mr-1.5"></i> Lịch Sử
                                    </a>
                                    <?php if (($m['role'] ?? 'user') === 'admin'): ?>
                                        <a href="?page=members&action=toggle_admin&email=<?= urlencode($m['email']) ?>" onclick="return confirm('Gỡ quyền Admin của người này?')" class="inline-flex items-center px-3 py-1.5 bg-red-600/10 hover:bg-red-600/20 text-red-500 border border-red-600/20 rounded-lg transition-colors text-xs font-semibold">
                                            <i data-lucide="shield-off" class="w-3 h-3 mr-1.5"></i> Gỡ
                                        </a>
                                    <?php else: ?>
                                        <a href="?page=members&action=toggle_admin&email=<?= urlencode($m['email']) ?>" onclick="return confirm('Cấp quyền Admin cho người này?')" class="inline-flex items-center px-3 py-1.5 bg-green-600/10 hover:bg-green-600/20 text-green-500 border border-green-600/20 rounded-lg transition-colors text-xs font-semibold">
                                            <i data-lucide="shield-check" class="w-3 h-3 mr-1.5"></i> Cấp
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($members)): ?>
                <div class="p-8 text-center border-t border-gray-800">
                    <i data-lucide="users" class="w-12 h-12 text-gray-700 mx-auto mb-3"></i>
                    <p class="text-gray-500">Chưa có thành viên nào đăng ký.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>
