const fs = require('fs');
let content = fs.readFileSync('views/api-document.ejs', 'utf8');

// Insert info about format=xml
content = content.replace(
    '1. Trả về định dạng chuẩn JSON',
    '1. Hỗ trợ đa định dạng: Trả về JSON (mặc định) và XML (tuỳ chọn) khi thêm `?format=xml`'
);

content = content.replace(
    '<li>Phản hồi API luôn là <code>application/json</code></li>',
    '<li>Phản hồi API là <code>application/json</code> (mặc định) hoặc <code>application/xml</code> (nếu dùng ?format=xml)</li>'
);

content = content.replace(
    '<h3 class="text-white font-bold mb-3 flex items-center gap-2"><i data-lucide="code" class="w-4 h-4 text-orange-400"></i> Code Mẫu (PHP)</h3>',
    `<h3 class="text-white font-bold mb-3 flex items-center gap-2"><i data-lucide="code" class="w-4 h-4 text-orange-400"></i> Code Mẫu Lấy JSON (PHP)</h3>`
);

// Add sample for XML
const xmlSample = `
<div class="mt-8">
    <h3 class="text-white font-bold mb-3 flex items-center gap-2"><i data-lucide="code" class="w-4 h-4 text-pink-400"></i> Code Mẫu Lấy XML (PHP)</h3>
    <pre><code>&lt;?php
$url = "https://api.phimtop1.asia/api/danh-sach/phim-moi-cap-nhat?page=1&format=xml";
$response = file_get_contents($url);
// Parse XML thành Object
$xml = simplexml_load_string($response);

foreach ($xml-&gt;data-&gt;items as $movie) {
    echo "Tên phim: " . $movie-&gt;name . "&lt;br&gt;";
    echo "Năm sản xuất: " . $movie-&gt;year . "&lt;br&gt;";
}
?&gt;</code></pre>
</div>
`;

content = content.replace(
    '<!-- End Sample PHP -->',
    '<!-- End Sample PHP -->' + xmlSample
);

fs.writeFileSync('views/api-document.ejs', content, 'utf8');

// Do the same for views/cms.ejs if it has sample code
if(fs.existsSync('views/cms.ejs')){
    let cmsContent = fs.readFileSync('views/cms.ejs', 'utf8');
    // Just replace a small snippet in CMS text to mention XML
    cmsContent = cmsContent.replace('Dễ dàng bóc tách dữ liệu JSON', 'Dễ dàng bóc tách dữ liệu JSON hoặc XML');
    fs.writeFileSync('views/cms.ejs', cmsContent, 'utf8');
}

console.log('Patched docs');
