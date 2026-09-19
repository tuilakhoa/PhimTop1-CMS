const fs = require('fs');
let content = fs.readFileSync('views/api-document.ejs', 'utf8');

// For "list" (Danh sách phim)
content = content.replace(
    '<button onclick="switchTab(event, \'list\', \'php\')" class="tab-list-btn px-3 py-1.5 text-xs font-bold rounded-md bg-transparent text-gray-400 hover:text-white transition-colors shrink-0">PHP</button>',
    `<button onclick="switchTab(event, 'list', 'php')" class="tab-list-btn px-3 py-1.5 text-xs font-bold rounded-md bg-transparent text-gray-400 hover:text-white transition-colors shrink-0">PHP (JSON)</button>
                    <button onclick="switchTab(event, 'list', 'php-xml')" class="tab-list-btn px-3 py-1.5 text-xs font-bold rounded-md bg-transparent text-gray-400 hover:text-white transition-colors shrink-0">PHP (XML)</button>`
);

const phpXmlList = `<div id="tab-list-php-xml" class="tab-list-content hidden">
                        <pre><code>&lt;?php
$url = "https://api.phimtop1.asia/api/danh-sach?page=1&format=xml";
$response = file_get_contents($url);
$xml = simplexml_load_string($response);
print_r($xml);
?&gt;</code></pre>
                    </div>`;
content = content.replace(
    '<div id="tab-list-python" class="tab-list-content hidden">',
    phpXmlList + '\n                    <div id="tab-list-python" class="tab-list-content hidden">'
);


// For "detail" (Chi tiết phim)
content = content.replace(
    '<button onclick="switchTab(event, \'detail\', \'php\')" class="tab-detail-btn px-3 py-1.5 text-xs font-bold rounded-md bg-transparent text-gray-400 hover:text-white transition-colors shrink-0">PHP</button>',
    `<button onclick="switchTab(event, 'detail', 'php')" class="tab-detail-btn px-3 py-1.5 text-xs font-bold rounded-md bg-transparent text-gray-400 hover:text-white transition-colors shrink-0">PHP (JSON)</button>
                    <button onclick="switchTab(event, 'detail', 'php-xml')" class="tab-detail-btn px-3 py-1.5 text-xs font-bold rounded-md bg-transparent text-gray-400 hover:text-white transition-colors shrink-0">PHP (XML)</button>`
);

const phpXmlDetail = `<div id="tab-detail-php-xml" class="tab-detail-content hidden">
                        <pre><code>&lt;?php
$url = "https://api.phimtop1.asia/api/phim/dau-pha-thuong-khung?format=xml";
$response = file_get_contents($url);
$xml = simplexml_load_string($response);
print_r($xml);
?&gt;</code></pre>
                    </div>`;
content = content.replace(
    '<div id="tab-detail-python" class="tab-detail-content hidden">',
    phpXmlDetail + '\n                    <div id="tab-detail-python" class="tab-detail-content hidden">'
);

fs.writeFileSync('views/api-document.ejs', content, 'utf8');
console.log("Fixed Tabs");
