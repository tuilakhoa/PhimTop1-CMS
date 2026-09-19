<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('live-search-input');
    const searchResults = document.getElementById('live-search-results');
    let timeoutId = null;

    if(!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(timeoutId);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        timeoutId = setTimeout(() => {
            searchResults.classList.remove('hidden');
            searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i> Đang tìm...</div>';

            fetch('/wp-admin/admin-ajax.php?action=live_search_movies&q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    let html = '';
                    data.forEach(movie => {
                        html += `
                        <a href="${movie.url}" class="flex items-center gap-3 p-3 hover:bg-gray-800 transition border-b border-gray-800/50 last:border-0 group">
                            <img src="${movie.thumb}" alt="${movie.title}" class="w-10 h-14 object-cover rounded shadow-md">
                            <div class="flex-1 min-w-0 text-left">
                                <h4 class="text-sm font-bold text-gray-200 truncate group-hover:text-red-500 transition-colors">${movie.title}</h4>
                                <p class="text-xs text-gray-500 truncate mt-0.5">${movie.origin_name} (${movie.year})</p>
                            </div>
                        </a>`;
                    });
                    searchResults.innerHTML = html;
                } else {
                    searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Không tìm thấy phim nào.</div>';
                }
            })
            .catch(() => {
                searchResults.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Lỗi kết nối.</div>';
            });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
});
</script>
