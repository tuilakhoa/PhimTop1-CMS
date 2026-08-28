import os

file_path = 'themes/phimhayok/movie.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replacement 1
content = content.replace(
    "const movieSlug = '<?= htmlspecialchars($slug) ?>';",
    "const movieSlug = '<?= htmlspecialchars($slug) ?>';\n                const currentUser = <?= json_encode($_SESSION['user']['name'] ?? '') ?>;\n                const isAdmin = <?= isset($_SESSION['admin']) ? 'true' : 'false' ?>;"
)

# Replacement 2
old_fetch = "                function fetchComments() {"
new_fetch = """                window.deleteComment = function(id) {
                    if(confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
                        fetch('/api/comments.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({action: 'delete', id: id})
                        })
                        .then(res => res.json())
                        .then(res => {
                            if(res.success) fetchComments();
                            else alert(res.message);
                        });
                    }
                };
                
                function fetchComments() {"""
content = content.replace(old_fetch, new_fetch)

# Replacement 3
old_html = """                                let html = '';
                                res.data.forEach(c => {
                                    html += `
                                        <div class="flex gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center shrink-0 border border-gray-700">
                                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-baseline gap-2 mb-1">
                                                    <span class="font-bold text-gray-200 text-sm">${c.user_name}</span>
                                                    <span class="text-xs text-gray-500">${c.time_ago}</span>
                                                </div>"""

new_html = """                                let html = '';
                                res.data.forEach(c => {
                                    let deleteBtn = '';
                                    if (isAdmin || (currentUser && currentUser === c.user_name)) {
                                        deleteBtn = `<button onclick="deleteComment(${c.id})" class="text-red-500 text-xs ml-3 hover:underline font-medium border border-red-500/30 px-2 py-0.5 rounded">Xóa</button>`;
                                    }
                                    
                                    html += `
                                        <div class="flex gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center shrink-0 border border-gray-700">
                                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-baseline mb-1">
                                                    <span class="font-bold text-gray-200 text-sm mr-2">${c.user_name}</span>
                                                    <span class="text-xs text-gray-500">${c.time_ago}</span>
                                                    ${deleteBtn}
                                                </div>"""

content = content.replace(old_html, new_html)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched movie.php")
