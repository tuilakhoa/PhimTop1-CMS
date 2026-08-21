<?php
require_once __DIR__ . '/header.php';

$pdo = getPDO();
$userEmail = $_SESSION['user']['email'];
try {
    $stmt = $pdo->prepare("SELECT coins FROM members WHERE email = ?");
    $stmt->execute([$userEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $coins = $user ? (int)$user['coins'] : 0;
} catch (PDOException $e) {
    try { $pdo->exec("ALTER TABLE members ADD COLUMN coins INT DEFAULT 0"); } catch (PDOException $ex) {}
    try { $pdo->exec("ALTER TABLE members ADD COLUMN active_frame_id INT DEFAULT NULL"); } catch (PDOException $ex) {}
    $coins = 0;
}
?>

<!-- Load SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mx-auto px-4 py-8 max-w-6xl min-h-[70vh]">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white flex items-center">
                <i data-lucide="store" class="w-8 h-8 mr-3 text-red-500"></i> Cửa Hàng Vật Phẩm
            </h1>
            <p class="text-gray-400 mt-2">Mua sắm khung ảnh đại diện và vật phẩm trang trí</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-6 py-3 flex items-center shadow-lg">
            <span class="text-gray-400 mr-3">Số dư của bạn:</span>
            <div class="flex items-center text-yellow-400 font-bold text-xl">
                <i data-lucide="coins" class="w-5 h-5 mr-1.5"></i>
                <span id="userCoins"><?= number_format($coins) ?></span>
            </div>
        </div>
    </div>

    <div id="shopContainer" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <!-- Shop items will be injected here via JS -->
    </div>
    
    <div id="loading" class="flex justify-center items-center py-12">
        <i data-lucide="loader-2" class="w-8 h-8 text-red-500 animate-spin"></i>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadShop();
});

function loadShop() {
    document.getElementById('loading').style.display = 'flex';
    document.getElementById('shopContainer').innerHTML = '';
    
    fetch('/api/v1/shop.php?action=list', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            document.getElementById('loading').style.display = 'none';
            if (data.status === 'success') {
                renderShop(data.data);
            } else {
                Swal.fire('Lỗi', data.message || 'Không thể tải cửa hàng', 'error');
            }
        })
        .catch(err => {
            document.getElementById('loading').style.display = 'none';
            Swal.fire('Lỗi', 'Lỗi kết nối', 'error');
        });
}

function renderShop(items) {
    const container = document.getElementById('shopContainer');
    
    if (items.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center text-gray-500 py-12">Chưa có vật phẩm nào trong cửa hàng.</div>';
        return;
    }
    
    items.forEach(item => {
        let actionBtn = '';
        if (item.is_active) {
            actionBtn = `<button onclick="equipItem(${item.id}, false)" class="w-full bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg font-medium transition-colors text-sm">Bỏ trang bị</button>`;
        } else if (item.is_owned) {
            actionBtn = `<button onclick="equipItem(${item.id}, true)" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition-colors text-sm">Trang bị</button>`;
        } else {
            actionBtn = `<button onclick="buyItem(${item.id}, ${item.price}, '${item.name}')" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition-colors text-sm flex justify-center items-center">
                <i data-lucide="shopping-cart" class="w-4 h-4 mr-1.5"></i> Mua ngay
            </button>`;
        }

        const card = document.createElement('div');
        card.className = `bg-gray-900 border ${item.is_active ? 'border-red-500 shadow-red-500/20 shadow-lg' : 'border-gray-800'} rounded-xl p-4 flex flex-col items-center transition-all hover:scale-[1.02]`;
        card.innerHTML = `
            <div class="relative w-24 h-24 mb-4 flex items-center justify-center">
                <!-- Avatar placeholder to show how frame looks -->
                <img src="https://ui-avatars.com/api/?name=U&background=random" class="w-16 h-16 rounded-full absolute z-0" />
                <img src="${item.image_url}" class="w-24 h-24 absolute z-10 scale-125 object-contain" alt="${item.name}" />
            </div>
            <h3 class="text-white font-bold text-center mb-1 line-clamp-1" title="${item.name}">${item.name}</h3>
            
            <div class="flex items-center text-yellow-400 font-bold mb-4 ${item.is_owned ? 'opacity-50' : ''}">
                <i data-lucide="coins" class="w-4 h-4 mr-1"></i>
                <span>${item.price}</span>
            </div>
            
            <div class="w-full mt-auto">
                ${actionBtn}
            </div>
        `;
        container.appendChild(card);
    });
    
    if(window.lucide) {
        lucide.createIcons();
    }
}

function buyItem(id, price, name) {
    Swal.fire({
        title: 'Xác nhận mua',
        text: `Bạn có chắc muốn mua ${name} với giá ${price} Xu?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Mua ngay',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title: 'Đang xử lý...', didOpen: () => {Swal.showLoading()}});
            
            fetch('/api/v1/shop.php?action=buy', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({frame_id: id})
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update coins visually
                    let coinsEl = document.getElementById('userCoins');
                    let currentCoins = parseInt(coinsEl.innerText.replace(/,/g, ''));
                    coinsEl.innerText = (currentCoins - price).toLocaleString();
                    
                    Swal.fire('Thành công', data.message, 'success');
                    loadShop(); // Reload items
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Lỗi', 'Lỗi kết nối', 'error');
            });
        }
    });
}

function equipItem(id, equip) {
    Swal.fire({title: 'Đang xử lý...', didOpen: () => {Swal.showLoading()}});
            
    fetch('/api/v1/shop.php?action=equip', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({frame_id: equip ? id : 0})
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('Thành công', data.message, 'success');
            loadShop();
        } else {
            Swal.fire('Lỗi', data.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Lỗi', 'Lỗi kết nối', 'error');
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
