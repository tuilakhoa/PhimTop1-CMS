<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$settings = getSettings();

if (!isset($_SESSION['user'])) {
    header('Location: /member.php');
    exit;
}

$themeName = 'phimhayok';
$themePath = __DIR__ . '/themes/' . $themeName;
?>
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ai đang xem? - <?= htmlspecialchars($settings['siteName']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#fcc526', // Phimhayok yellow
                        blackbg: '#0a0a0a'
                    }
                }
            }
        }
    </script>
    <style>
        .profile-card:hover .avatar-img {
            border-color: #fcc526;
        }
        .profile-card:hover .profile-name {
            color: #fcc526;
        }
    </style>
</head>
<body class="bg-blackbg text-white min-h-screen flex flex-col items-center justify-center font-sans relative">
    
    <div class="absolute top-6 left-8">
        <a href="/">
            <?php if (!empty($settings['logoUrl'])): ?>
                <img src="<?= htmlspecialchars($settings['logoUrl']) ?>" alt="Logo" class="h-8 md:h-10">
            <?php else: ?>
                <span class="text-3xl font-bold text-primary"><?= htmlspecialchars($settings['siteName']) ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="text-center w-full max-w-4xl px-4">
        <h1 class="text-3xl md:text-5xl font-medium mb-8">Ai đang xem?</h1>
        
        <div id="profiles-container" class="flex flex-wrap justify-center gap-4 md:gap-8 mb-12">
            <!-- Loaded via JS -->
            <div class=" flex space-x-4">
                <div class="rounded-md bg-gray-700 h-24 w-24 md:h-32 md:w-32"></div>
                <div class="rounded-md bg-gray-700 h-24 w-24 md:h-32 md:w-32"></div>
            </div>
        </div>

        <button id="manage-btn" class="border border-gray-500 text-gray-500 hover:text-white hover:border-white px-6 py-2 text-lg uppercase tracking-widest ">
            Quản lý hồ sơ
        </button>
    </div>

    <!-- Create Profile Modal -->
    <div id="create-modal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50">
        <div class="bg-blackbg p-8 max-w-xl w-full">
            <h2 class="text-4xl mb-6 font-medium">Thêm Hồ Sơ</h2>
            <p class="text-gray-400 mb-6 text-lg">Thêm một hồ sơ cho người xem khác trên tài khoản của bạn.</p>
            <div class="flex items-center gap-6 mb-8 border-t border-b border-gray-700 py-6">
                <div class="relative group cursor-pointer" onclick="rollAvatar()" title="Tạo avatar ngẫu nhiên">
                    <img id="new-avatar-img" src="https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y&s=200" class="w-24 h-24 md:w-28 md:h-28 rounded-md bg-blackbg" alt="">
                    <div class="absolute inset-0 bg-black/60 text-white rounded-md flex items-center justify-center opacity-0 group-hover:opacity-100 ">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </div>
                </div>
                <div class="flex-1">
                    <input type="text" id="new-name" placeholder="Tên" class="w-full bg-[#333] text-white px-4 py-2 text-lg focus:outline-none">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="kids-mode" class="w-6 h-6 rounded bg-[#333] border-none text-primary focus:ring-0">
                    <label for="kids-mode" class="text-lg">Trẻ em</label>
                </div>
            </div>
            <div class="flex gap-4">
                <button onclick="createProfile()" class="bg-white text-black hover:bg-gray-200 px-8 py-2 text-lg font-medium ">Tiếp tục</button>
                <button onclick="document.getElementById('create-modal').classList.replace('flex', 'hidden')" class="border border-gray-500 text-gray-500 hover:text-white hover:border-white px-8 py-2 text-lg font-medium ">Hủy</button>
            </div>
        </div>
    </div>

    <script>
        var isManageMode = false;
        
        async function fetchProfiles() {
            var res = await fetch('/api/v1/profiles.php?action=list');
            var data = await res.json();
            if (data.status === 'success') {
                renderProfiles(data.data);
            }
        }

        function renderProfiles(profiles) {
            var container = document.getElementById('profiles-container');
            var html = '';
            
            profiles.forEach(p => {
                var kidBadge = p.is_kids_mode == 1 ? '<span class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded shadow">KIDS</span>' : '';
                
                html += `
                    <div class="profile-card group cursor-pointer relative w-24 md:w-36 flex flex-col items-center" onclick="handleProfileClick(${p.id})">
                        <div class="relative">
                            <img src="${p.avatar_url}" class="avatar-img w-24 h-24 md:w-36 md:h-36 rounded-md border-2 border-transparent  object-cover shadow-lg" alt="">
                            ${kidBadge}
                            ${isManageMode ? '<div class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-md border-2 border-white"><svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></div>' : ''}
                        </div>
                        <span class="profile-name text-gray-400 mt-4 text-sm md:text-lg  truncate w-full text-center">${p.profile_name}</span>
                    </div>
                `;
            });
            
            if (profiles.length < 5 && !isManageMode) {
                html += `
                    <div class="profile-card cursor-pointer w-24 md:w-36 flex flex-col items-center" onclick="showCreateModal()">
                        <div class="w-24 h-24 md:w-36 md:h-36 rounded-md border-2 border-transparent hover:border-white  hover:bg-gray-200 flex items-center justify-center group bg-blackbg">
                            <svg class="w-16 h-16 text-gray-500 group-hover:text-black " fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <span class="profile-name text-gray-400 mt-4 text-sm md:text-lg ">Thêm hồ sơ</span>
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }

        async function handleProfileClick(id) {
            if (isManageMode) {
                if (confirm('Bạn có muốn xóa hồ sơ này không?')) {
                    var res = await fetch('/api/v1/profiles.php?action=delete', {
                        method: 'POST',
                        body: JSON.stringify({profile_id: id}),
                        headers: {'Content-Type': 'application/json'}
                    });
                    var data = await res.json();
                    if(data.status === 'success') fetchProfiles();
                    else alert(data.message);
                }
            } else {
                var res = await fetch('/api/v1/profiles.php?action=select', {
                    method: 'POST',
                    body: JSON.stringify({profile_id: id}),
                    headers: {'Content-Type': 'application/json'}
                });
                var data = await res.json();
                if(data.status === 'success') {
                    window.location.href = '/';
                }
            }
        }

        function showCreateModal() {
            document.getElementById('create-modal').classList.replace('hidden', 'flex');
        }

        var currentTempAvatar = '';
        function rollAvatar() {
            var styles = ['identicon', 'monsterid', 'wavatar', 'retro', 'robohash'];
            var randomStyle = styles[Math.floor(Math.random() * styles.length)];
            var hash = Array.from({length: 32}, () => Math.floor(Math.random()*16).toString(16)).join('');
            currentTempAvatar = `https://www.gravatar.com/avatar/${hash}?d=${randomStyle}&s=200`;
            document.getElementById('new-avatar-img').src = currentTempAvatar;
        }

        async function createProfile() {
            var name = document.getElementById('new-name').value;
            var kids = document.getElementById('kids-mode').checked ? 1 : 0;
            if(!name) return alert('Vui lòng nhập tên');
            
            var res = await fetch('/api/v1/profiles.php?action=create', {
                method: 'POST',
                body: JSON.stringify({profile_name: name, is_kids_mode: kids, avatar_url: currentTempAvatar}),
                headers: {'Content-Type': 'application/json'}
            });
            var data = await res.json();
            if(data.status === 'success') {
                document.getElementById('create-modal').classList.replace('flex', 'hidden');
                document.getElementById('new-name').value = '';
                document.getElementById('kids-mode').checked = false;
                currentTempAvatar = '';
                document.getElementById('new-avatar-img').src = 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y&s=200';
                fetchProfiles();
            } else {
                alert(data.message);
            }
        }

        document.getElementById('manage-btn').addEventListener('click', function() {
            isManageMode = !isManageMode;
            this.textContent = isManageMode ? 'Hoàn tất' : 'Quản lý hồ sơ';
            this.classList.toggle('bg-white');
            this.classList.toggle('text-black');
            fetchProfiles();
        });

        fetchProfiles();
    </script>
</body>
</html>
