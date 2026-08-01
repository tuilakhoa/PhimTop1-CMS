document.addEventListener('DOMContentLoaded', function() {
    const commentsSection = document.getElementById('comments-section');
    if (!commentsSection) return; // Exit if there's no comment section on this page
    
    const movieSlug = commentsSection.getAttribute('data-slug');
    if (!movieSlug) return;
    
    const anonCheckbox = document.getElementById('comment-anon');
    const nameInput = document.getElementById('comment-name');
    const contentInput = document.getElementById('comment-content');
    const submitBtn = document.getElementById('btn-submit-comment');
    const commentsList = document.getElementById('comments-list');
    const countSpan = document.getElementById('comment-count');
    
    if(anonCheckbox && nameInput) {
        anonCheckbox.addEventListener('change', function() {
            if (this.checked) {
                nameInput.classList.add('hidden');
            } else {
                nameInput.classList.remove('hidden');
                nameInput.focus();
            }
        });
    }
    
    function fetchComments() {
        if (!commentsList) return;
        fetch('/api/comments.php?slug=' + encodeURIComponent(movieSlug))
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    if (countSpan) countSpan.textContent = res.data.length;
                    
                    if (res.data.length === 0) {
                        commentsList.innerHTML = '<div class="text-center text-gray-500 text-sm py-4">Chưa có bình luận nào. Hãy là người đầu tiên!</div>';
                        return;
                    }
                    
                    commentsList.innerHTML = '';
                    res.data.forEach(comment => {
                        const date = new Date(comment.created_at).toLocaleString('vi-VN');
                        const displayName = comment.is_anonymous == 1 ? 'Khách (Ẩn danh)' : comment.name;
                        const avatarInitial = displayName.charAt(0).toUpperCase();
                        
                        // Slightly generic template
                        const html = `
                        <div class="flex space-x-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold shrink-0">
                                ${avatarInitial}
                            </div>
                            <div class="flex-1 bg-gray-800 rounded-2xl rounded-tl-none px-4 py-3 border border-gray-700">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="font-bold text-gray-200 text-sm">${displayName}</h4>
                                    <span class="text-xs text-gray-500">${date}</span>
                                </div>
                                <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">${comment.content}</p>
                            </div>
                        </div>
                        `;
                        commentsList.insertAdjacentHTML('beforeend', html);
                    });
                }
            })
            .catch(err => {
                console.error('Fetch comments error:', err);
                commentsList.innerHTML = '<div class="text-red-500 text-sm text-center">Lỗi khi tải bình luận.</div>';
            });
    }
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            const isAnon = anonCheckbox && anonCheckbox.checked ? 1 : 0;
            const name = isAnon ? '' : (nameInput ? nameInput.value.trim() : '');
            const content = contentInput ? contentInput.value.trim() : '';
            
            if (!isAnon && !name) {
                alert('Vui lòng nhập tên của bạn.');
                return;
            }
            
            if (!content) {
                alert('Vui lòng nhập nội dung bình luận.');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang gửi...';
            
            const formData = new FormData();
            formData.append('slug', movieSlug);
            formData.append('name', name);
            formData.append('content', content);
            formData.append('is_anonymous', isAnon);
            
            fetch('/api/comments.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Gửi bình luận';
                
                if (res.success) {
                    contentInput.value = '';
                    fetchComments();
                } else {
                    alert('Lỗi: ' + res.message);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Gửi bình luận';
                alert('Lỗi kết nối. Vui lòng thử lại sau.');
                console.error(err);
            });
        });
    }
    
    fetchComments();
});
