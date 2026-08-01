document.addEventListener('DOMContentLoaded', function() {
    const versionDisplay = document.getElementById('cms-version-display');
    const updateMessage = document.getElementById('update-message');
    const btnCheckUpdate = document.getElementById('btn-check-update');
    
    function checkUpdate(force = false) {
        versionDisplay.innerHTML = '<span class="text-gray-500 animate-pulse">Đang kiểm tra...</span>';
        updateMessage.innerHTML = '<div class="flex items-center gap-2 text-gray-400"><i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Vui lòng đợi trong giây lát...</div>';
        lucide.createIcons();
        
        if (btnCheckUpdate.querySelector('i')) {
            btnCheckUpdate.querySelector('i').classList.add('animate-spin');
        }
        
        const url = `/admin/api/check_update.php${force ? '?force=1' : ''}`;
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (btnCheckUpdate.querySelector('i')) {
                    btnCheckUpdate.querySelector('i').classList.remove('animate-spin');
                }
                
                if (data.success) {
                    versionDisplay.innerHTML = `Phiên Bản <span class="text-blue-400">v${data.current}</span>`;
                    
                    if (data.hasUpdate) {
                        updateMessage.innerHTML = `
                            <div class="bg-blue-500/10 border border-blue-500/20 p-5 rounded-2xl mb-4 mt-2">
                                <span class="text-blue-400 flex items-center gap-2 mb-2 font-bold text-lg">
                                    <span class="relative flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                    </span>
                                    Phát Hiện Phiên Bản Mới: v${data.latest}
                                </span>
                                ${data.title ? `<strong class="block text-white text-base mb-1">${data.title}</strong>` : ''}
                                ${data.description ? `<p class="text-gray-400 text-sm leading-relaxed">${data.description}</p>` : ''}
                                <div class="flex flex-wrap gap-3 mt-5">
                                    ${data.download ? `<button onclick="doAutoUpdate('${data.download}')" id="btn-do-update" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 rounded-xl text-white font-bold flex items-center shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5"><i data-lucide="zap" class="w-4 h-4 mr-2"></i> Cập Nhật Ngay</button>` : ''}
                                    ${data.changelog ? `<a href="${data.changelog}" target="_blank" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 rounded-xl text-white font-medium flex items-center transition-colors border border-gray-700"><i data-lucide="file-text" class="w-4 h-4 mr-2 text-gray-400"></i> Xem Chi Tiết</a>` : ''}
                                </div>
                            </div>
                        `;
                    } else {
                        updateMessage.innerHTML = `<div class="inline-flex items-center gap-2 px-4 py-2 bg-green-500/10 text-green-400 font-medium rounded-xl border border-green-500/20 mt-2"><i data-lucide="check-circle-2" class="w-5 h-5"></i> Tuyệt vời! Bạn đang ở phiên bản mới nhất.</div>`;
                    }
                } else {
                    versionDisplay.innerHTML = '<span class="text-red-500">Lỗi kiểm tra</span>';
                    updateMessage.innerHTML = `<span class="text-red-400 flex items-center gap-2 mt-2 font-medium bg-red-500/10 p-3 rounded-xl border border-red-500/20"><i data-lucide="alert-octagon" class="w-5 h-5"></i> ${data.message || 'Không thể kết nối máy chủ cập nhật.'}</span>`;
                }
                lucide.createIcons();
            })
            .catch(error => {
                if (btnCheckUpdate.querySelector('i')) {
                    btnCheckUpdate.querySelector('i').classList.remove('animate-spin');
                }
                versionDisplay.innerHTML = '<span class="text-red-500">Mất Kết Nối</span>';
                updateMessage.innerHTML = `<span class="text-red-400 flex items-center gap-2 mt-2 font-medium bg-red-500/10 p-3 rounded-xl border border-red-500/20"><i data-lucide="wifi-off" class="w-5 h-5"></i> Lỗi kết nối đến máy chủ cập nhật. Hãy kiểm tra mạng hoặc thử lại sau.</span>`;
                console.error('Update check error:', error);
                lucide.createIcons();
            });
    }

    if (btnCheckUpdate) {
        btnCheckUpdate.addEventListener('click', () => checkUpdate(true));
    }
    
    // Auto-update logic using SSE (Server-Sent Events)
    window.doAutoUpdate = function(downloadUrl) {
        if (!confirm("Hệ thống sẽ tải và cài đặt bản cập nhật tự động. Việc này có thể mất vài phút. Bạn có chắc chắn muốn tiến hành?")) {
            return;
        }
        
        const btnDoUpdate = document.getElementById('btn-do-update');
        const logContainer = document.getElementById('update-log-container');
        const logOutput = document.getElementById('update-log');
        const progressBar = document.getElementById('update-progress-bar');
        
        btnDoUpdate.disabled = true;
        btnDoUpdate.classList.add('opacity-50', 'cursor-not-allowed', 'transform-none');
        btnDoUpdate.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Đang xử lý...';
        lucide.createIcons();
        
        // Show terminal with animation
        logContainer.classList.remove('hidden');
        setTimeout(() => {
            logContainer.classList.remove('opacity-0', 'translate-y-4');
            logContainer.classList.add('opacity-100', 'translate-y-0');
        }, 50);
        
        logOutput.innerHTML = '';
        progressBar.style.width = '0%';
        progressBar.className = 'bg-gradient-to-r from-blue-500 to-purple-500 h-full rounded-full transition-all duration-500 relative';
        
        const appendLog = (msg, type) => {
            const el = document.createElement('div');
            el.className = 'flex items-start gap-3 log-entry';
            let color = 'text-gray-300';
            let iconHtml = '<i data-lucide="info" class="w-4 h-4 text-blue-400 mt-0.5 shrink-0"></i>';
            let prefix = 'INFO';
            let prefixColor = 'text-blue-400';
            
            if (type === 'success') { 
                color = 'text-gray-100'; 
                prefix = 'OK'; 
                prefixColor = 'text-green-400';
                iconHtml = '<i data-lucide="check" class="w-4 h-4 text-green-400 mt-0.5 shrink-0"></i>';
            }
            if (type === 'error') { 
                color = 'text-red-400'; 
                prefix = 'ERR'; 
                prefixColor = 'text-red-500';
                iconHtml = '<i data-lucide="x-circle" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>';
            }
            if (type === 'warning') { 
                color = 'text-yellow-200'; 
                prefix = 'WARN'; 
                prefixColor = 'text-yellow-500';
                iconHtml = '<i data-lucide="alert-triangle" class="w-4 h-4 text-yellow-500 mt-0.5 shrink-0"></i>';
            }
            
            const time = new Date().toLocaleTimeString('vi-VN', { hour12: false });
            el.innerHTML = `
                ${iconHtml}
                <span class="text-gray-500 shrink-0 font-mono text-xs mt-0.5">[${time}]</span> 
                <span class="${prefixColor} font-bold text-xs shrink-0 mt-0.5 w-10">${prefix}</span> 
                <span class="${color}">${msg}</span>
            `;
            logOutput.appendChild(el);
            lucide.createIcons();
            logOutput.scrollTop = logOutput.scrollHeight;
        };
        
        appendLog('Khởi động tiến trình cập nhật tự động (vui lòng không tắt trình duyệt)...', 'info');
        
        const source = new EventSource('/admin/api/do_update.php?download_url=' + encodeURIComponent(downloadUrl));
        
        source.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                
                if (data.message) {
                    appendLog(data.message, data.type || 'info');
                }
                
                if (data.progress) {
                    progressBar.style.width = data.progress + '%';
                    if (data.progress >= 100) {
                        progressBar.className = 'bg-gradient-to-r from-green-500 to-emerald-400 h-full rounded-full transition-all duration-500 relative shadow-[0_0_15px_rgba(16,185,129,0.5)]';
                    }
                }
                
                if (data.complete) {
                    source.close();
                    if (data.type === 'success') {
                        btnDoUpdate.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> Cập Nhật Hoàn Tất';
                        btnDoUpdate.className = "px-6 py-2.5 bg-green-600 rounded-xl text-white font-bold flex items-center shadow-lg shadow-green-500/30";
                        lucide.createIcons();
                        appendLog('Trang web sẽ tự động tải lại trong 3 giây...', 'info');
                        setTimeout(() => window.location.reload(), 3000);
                    } else {
                        btnDoUpdate.disabled = false;
                        btnDoUpdate.classList.remove('opacity-50', 'cursor-not-allowed');
                        btnDoUpdate.innerHTML = '<i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Thử Lại';
                        btnDoUpdate.className = "px-6 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-bold flex items-center border border-gray-600 transition-colors";
                        lucide.createIcons();
                    }
                }
            } catch (e) {
                console.error("Lỗi phân tích cú pháp SSE", e);
            }
        };
        
        source.onerror = function() {
            appendLog('Mất kết nối với máy chủ trong quá trình cập nhật! Quá trình có thể vẫn đang chạy ngầm.', 'error');
            source.close();
            btnDoUpdate.disabled = false;
            btnDoUpdate.classList.remove('opacity-50', 'cursor-not-allowed');
            btnDoUpdate.innerHTML = '<i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Thử Lại';
            btnDoUpdate.className = "px-6 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-bold flex items-center border border-gray-600 transition-colors";
            lucide.createIcons();
        };
    };
    
    // Auto check on load
    if (document.getElementById('cms-version-display')) {
        checkUpdate(false);
    }
    
    // Manual Update Form
    const manualForm = document.getElementById('manual-update-form');
    if (manualForm) {
        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-manual-update');
            const resultDiv = document.getElementById('manual-result');
            const path = document.getElementById('manual-path').value;
            const fileInput = document.getElementById('manual-file');
            
            if (!fileInput.files[0]) {
                return alert('Vui lòng chọn file.');
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Đang tải lên...';
            lucide.createIcons();
            
            const formData = new FormData();
            formData.append('path', path);
            formData.append('file', fileInput.files[0]);
            
            fetch('/admin/api/manual_update.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                resultDiv.classList.remove('hidden', 'bg-green-500/10', 'border-green-500/20', 'text-green-400', 'bg-red-500/10', 'border-red-500/20', 'text-red-400');
                if (data.success) {
                    resultDiv.classList.add('bg-green-500/10', 'border-green-500/20', 'text-green-400');
                    resultDiv.innerHTML = `<i data-lucide="check-circle" class="w-4 h-4 inline-block mr-1"></i> ${data.message}`;
                    manualForm.reset();
                } else {
                    resultDiv.classList.add('bg-red-500/10', 'border-red-500/20', 'text-red-400');
                    resultDiv.innerHTML = `<i data-lucide="alert-octagon" class="w-4 h-4 inline-block mr-1"></i> ${data.message}`;
                }
                lucide.createIcons();
            })
            .catch(err => {
                resultDiv.classList.remove('hidden');
                resultDiv.classList.add('bg-red-500/10', 'border-red-500/20', 'text-red-400');
                resultDiv.innerHTML = `<i data-lucide="wifi-off" class="w-4 h-4 inline-block mr-1"></i> Lỗi kết nối máy chủ.`;
                lucide.createIcons();
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="hard-drive-upload" class="w-4 h-4 mr-2"></i> Ghi Đè File';
                lucide.createIcons();
            });
        });
    }
});
