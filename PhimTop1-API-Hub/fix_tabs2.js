const fs = require('fs');
let content = fs.readFileSync('views/api-document.ejs', 'utf8');

// DETAIL
content = content.replace(
    '<button onclick="switchTab(event, \'detail\', \'php\')" class="tab-detail-btn px-4 py-2.5 text-sm font-bold text-gray-500 hover:text-white border-b-2 border-transparent whitespace-nowrap transition">PHP</button>',
    `<button onclick="switchTab(event, 'detail', 'php')" class="tab-detail-btn px-4 py-2.5 text-sm font-bold text-gray-500 hover:text-white border-b-2 border-transparent whitespace-nowrap transition">PHP (JSON)</button>
                    <button onclick="switchTab(event, 'detail', 'php-xml')" class="tab-detail-btn px-4 py-2.5 text-sm font-bold text-gray-500 hover:text-white border-b-2 border-transparent whitespace-nowrap transition">PHP (XML)</button>`
);

const phpXmlDetail = `<div id="tab-detail-php-xml" class="tab-detail-content hidden">
                        <pre><code>&lt;?php
$url = "https://api.phimtop1.asia/api/phim/dau-pha-thuong-khung?format=xml";
$response = file_get_contents($url);
$xml = simplexml_load_string($response);
print_r($xml);
?&gt;</code></pre>
                    </div>`;

if (!content.includes('id="tab-detail-php-xml"')) {
    content = content.replace(
        '<div id="tab-detail-python" class="tab-detail-content hidden">',
        phpXmlDetail + '\n                    <div id="tab-detail-python" class="tab-detail-content hidden">'
    );
}

// Clean up duplicate tab-list-php-xml
// Regex to find multiple tab-list-php-xml and keep only one
// Actually, I'll just leave them for now or write a clean regex.
let occurrences = content.split('<div id="tab-list-php-xml"');
if (occurrences.length > 2) {
    // Has duplicates
    // Just find the first one and keep it, remove the rest
    // Easier to just use string replace to remove the second block
    const blockToRemove = `<div id="tab-list-php-xml" class="tab-list-content hidden">
                        <pre><code>&lt;?php
$url = "https://api.phimtop1.asia/api/danh-sach?page=1&format=xml";
$response = file_get_contents($url);
$xml = simplexml_load_string($response);
print_r($xml);
?&gt;</code></pre>
                    </div>`;
    content = content.replace(blockToRemove, ''); // Removes only one occurrence
}

fs.writeFileSync('views/api-document.ejs', content, 'utf8');
console.log("Fixed detail tabs");
