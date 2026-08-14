document.addEventListener('DOMContentLoaded', function() {
    const versionDisplay = document.getElementById('cms-version-display');
    const updateMessage = document.getElementById('update-message');
    const btnCheckUpdate = document.getElementById('btn-check-update');
    
    function checkUpdate(force = false) {
        if (force) {
            versionDisplay.innerHTML = '<span class="text-gray-500 animate-pulse">Đang kiểm tra...</span>';
            updateMessage.innerHTML = '<div class="flex items-center gap-2 text-gray-400"><i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Vui lòng đợi trong giây lát...</div>';
            lucide.createIcons();
        }
        
        if (btnCheckUpdate.querySelector('i, svg')) {
            btnCheckUpdate.querySelector('i, svg').classList.add('animate-spin');
        }
        
        const timestamp = new Date().getTime();
        const basePath = typeof ADMIN_PATH !== 'undefined' ? ADMIN_PATH : '/admin';
        const url = `${basePath}/api/check_update.php?_=${timestamp}${force ? '&force=1' : ''}`;
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (btnCheckUpdate.querySelector('i, svg')) {
                    btnCheckUpdate.querySelector('i, svg').classList.remove('animate-spin');
                }
                
                if (data.success) {
                    versionDisplay.innerHTML = `Phiên Bản <span class="text-blue-400">v${data.current}</span>`;
                    
                    if (data.releases && data.releases.length > 0) {
                        const currentClean = data.current;
                        let optionsHtml = '';
                        data.releases.forEach(r => {
                            let label = r.version === currentClean ? `v${r.version} (Hiện tại)` : `v${r.version}`;
                            let selected = r.version === data.latest ? 'selected' : '';
                            optionsHtml += `<option value="${r.tag_name}" data-changelog="${r.changelog}" data-desc="${r.description.replace(/"/g, '&quot;')}" ${selected}>${label}</option>`;
                        });

                        updateMessage.innerHTML = `
                            <div class="bg-blue-500/10 border border-blue-500/20 p-5 rounded-2xl mb-4 mt-2">
                                <span class="text-blue-400 flex items-center gap-2 mb-2 font-bold text-lg">
                                    <i data-lucide="layers" class="w-5 h-5"></i>
                                    Chọn Phiên Bản Cài Đặt
                                </span>
                                
                                <select id="version-selector" class="mt-3 block w-full bg-gray-800 border border-gray-700 text-white rounded-xl py-2 px-3 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm mb-4">
                                    ${optionsHtml}
                                </select>
                                
                                <p id="version-desc" class="text-gray-400 text-sm leading-relaxed max-h-32 overflow-y-auto custom-scrollbar"></p>
                                
                                <div class="flex flex-wrap gap-3 mt-5">
                                    <button id="btn-do-update-selected" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 rounded-xl text-white font-bold flex items-center shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5"><i data-lucide="zap" class="w-4 h-4 mr-2"></i> Cài Đặt Phiên Bản Này</button>
                                    <a id="btn-view-changelog" href="#" target="_blank" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 rounded-xl text-white font-medium flex items-center transition-colors border border-gray-700"><i data-lucide="file-text" class="w-4 h-4 mr-2 text-gray-400"></i> Xem Chi Tiết</a>
                                </div>
                            </div>
                        `;
                        
                        setTimeout(() => {
                            const selector = document.getElementById('version-selector');
                            const descEl = document.getElementById('version-desc');
                            const changelogBtn = document.getElementById('btn-view-changelog');
                            const installBtn = document.getElementById('btn-do-update-selected');
                            
                            function updateVersionInfo() {
                                const selectedOption = selector.options[selector.selectedIndex];
                                descEl.innerText = selectedOption.getAttribute('data-desc') || 'Không có mô tả.';
                                changelogBtn.href = selectedOption.getAttribute('data-changelog') || '#';
                            }
                            
                            selector.addEventListener('change', updateVersionInfo);
                            updateVersionInfo();
                            
                            installBtn.addEventListener('click', function() {
                                doAutoUpdate(selector.value);
                            });
                            lucide.createIcons();
                        }, 100);

                    } else {
                        updateMessage.innerHTML = `<div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500/10 text-yellow-400 font-medium rounded-xl border border-yellow-500/20 mt-2"><i data-lucide="alert-circle" class="w-5 h-5"></i> Không tìm thấy phiên bản nào trên máy chủ.</div>`;
                    }
                } else {
                    versionDisplay.innerHTML = '<span class="text-red-500">Lỗi kiểm tra</span>';
                    updateMessage.innerHTML = `<span class="text-red-400 flex items-center gap-2 mt-2 font-medium bg-red-500/10 p-3 rounded-xl border border-red-500/20"><i data-lucide="alert-octagon" class="w-5 h-5"></i> ${data.message || 'Không thể kết nối máy chủ cập nhật.'}</span>`;
                }
                lucide.createIcons();
            })
            .catch(error => {
                if (btnCheckUpdate.querySelector('i, svg')) {
                    btnCheckUpdate.querySelector('i, svg').classList.remove('animate-spin');
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
    
    // Auto-update logic using AJAX
    window.doAutoUpdate = function(downloadUrl) {
        if (!confirm("Hệ thống sẽ tải và cài đặt bản cập nhật. Việc này có thể mất vài phút. Bạn có chắc chắn muốn tiến hành?")) {
            return;
        }
        
        const btnDoUpdate = document.getElementById('btn-do-update-selected');
        const logContainer = document.getElementById('update-log-container');
        const logOutput = document.getElementById('update-log');
        const progressBar = document.getElementById('update-progress-bar');
        
        if (btnDoUpdate) {
            btnDoUpdate.disabled = true;
            btnDoUpdate.classList.add('opacity-50', 'cursor-not-allowed', 'transform-none');
            btnDoUpdate.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Đang tải và cài đặt (chờ vài phút)...';
        }
        lucide.createIcons();
        
        // Show terminal with animation
        logContainer.classList.remove('hidden');
        setTimeout(() => {
            logContainer.classList.remove('opacity-0', 'translate-y-4');
            logContainer.classList.add('opacity-100', 'translate-y-0');
        }, 50);
        
        logOutput.innerHTML = '';
        progressBar.style.width = '30%'; // Fake progress for UX
        progressBar.className = 'bg-gradient-to-r from-blue-500 to-purple-500 h-full rounded-full transition-all duration-500 relative animate-pulse';
        
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
        
        appendLog('Khởi động tiến trình cập nhật tự động bằng AJAX...', 'info');
        appendLog('Đang lấy dữ liệu và tải tệp tin. Có thể mất từ 1-2 phút, vui lòng KHÔNG tắt trang web.', 'warning');
        
        const basePath = typeof ADMIN_PATH !== 'undefined' ? ADMIN_PATH : '/admin';
        
        fetch(`${basePath}/api/do_update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ download_url: downloadUrl })
        })
        .then(res => res.json())
        .then(data => {
            progressBar.style.width = '100%';
            progressBar.className = 'bg-gradient-to-r from-green-500 to-emerald-400 h-full rounded-full transition-all duration-500 relative shadow-[0_0_15px_rgba(16,185,129,0.5)]';
            
            if (data.logs && data.logs.length > 0) {
                data.logs.forEach(log => appendLog(log.message, log.type));
            }
            
            appendLog(data.message, data.status === 'error' ? 'error' : 'success');
            
            if (data.status === 'success' || data.status === 'warning') {
                if (btnDoUpdate) {
                    btnDoUpdate.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> Cài Đặt Hoàn Tất';
                    btnDoUpdate.className = "px-6 py-2.5 bg-green-600 rounded-xl text-white font-bold flex items-center shadow-lg shadow-green-500/30";
                }
                appendLog('Trang web sẽ tự động tải lại trong 3 giây...', 'info');
                setTimeout(() => window.location.reload(), 3000);
            } else {
                if (btnDoUpdate) {
                    btnDoUpdate.disabled = false;
                    btnDoUpdate.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnDoUpdate.innerHTML = '<i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Thử Lại';
                    btnDoUpdate.className = "px-6 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-bold flex items-center border border-gray-600 transition-colors";
                }
            }
        })
        .catch(err => {
            console.error(err);
            progressBar.style.width = '100%';
            progressBar.className = 'bg-red-500 h-full rounded-full transition-all duration-500 relative';
            appendLog('Có lỗi xảy ra trong quá trình cập nhật (Timeout hoặc lỗi mạng).', 'error');
            
            if (btnDoUpdate) {
                btnDoUpdate.disabled = false;
                btnDoUpdate.classList.remove('opacity-50', 'cursor-not-allowed');
                btnDoUpdate.innerHTML = '<i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Thử Lại';
                btnDoUpdate.className = "px-6 py-2.5 bg-gray-700 hover:bg-gray-600 rounded-xl text-white font-bold flex items-center border border-gray-600 transition-colors";
            }
            lucide.createIcons();
        });
    };
    
    // Auto check on load
    if (document.getElementById('cms-version-display')) {
        checkUpdate(false);
    }
    
});
