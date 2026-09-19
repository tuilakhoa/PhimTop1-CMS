const fs = require('fs');
let content = fs.readFileSync('views/api-document.ejs', 'utf8');

// Insert XML parameter into Query Parameters for danh-sach
const formatParam = `<tr>
                        <td class="px-4 py-2 border-b border-[#272a30] font-mono text-kk-blue">format</td>
                        <td class="px-4 py-2 border-b border-[#272a30]">String</td>
                        <td class="px-4 py-2 border-b border-[#272a30]">Định dạng trả về: <b>json</b> (mặc định) hoặc <b>xml</b></td>
                    </tr>`;

content = content.replace(
    '<td class="px-4 py-2 border-b border-[#272a30]">Số trang cần lấy (Mặc định: 1)</td>\n                    </tr>',
    '<td class="px-4 py-2 border-b border-[#272a30]">Số trang cần lấy (Mặc định: 1)</td>\n                    </tr>\n                    ' + formatParam
);

// We can also inject an XML tab into the code samples!
// Look for JavaScript, PHP, Python tabs
const tabListHead = `<div class="flex items-center gap-2 mb-2 bg-[#0b0c0f] p-1 rounded-t-lg border-b border-[#272a30]">
                        <button onclick="switchTabList('js')" id="btn-list-js" class="px-4 py-1.5 text-xs font-bold rounded-md bg-[#181a20] text-white">JavaScript (Fetch)</button>
                        <button onclick="switchTabList('php')" id="btn-list-php" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500 hover:text-gray-300">PHP (JSON)</button>
                        <button onclick="switchTabList('php-xml')" id="btn-list-php-xml" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500 hover:text-gray-300">PHP (XML)</button>
                        <button onclick="switchTabList('python')" id="btn-list-python" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500 hover:text-gray-300">Python</button>
                    </div>`;

content = content.replace(/<div class="flex items-center gap-2 mb-2 bg=\[#0b0c0f\] p-1 rounded-t-lg border-b border-\[#272a30\]">\s*<button onclick="switchTabList\('js'\)".*?<\/button>\s*<button onclick="switchTabList\('php'\)".*?<\/button>\s*<button onclick="switchTabList\('python'\)".*?<\/button>\s*<\/div>/, tabListHead);

const phpXmlContent = `<div id="tab-list-php-xml" class="tab-list-content hidden">
                        <pre><code>&lt;?php
$url = "https://api.phimtop1.asia/api/danh-sach?page=1&format=xml";
$response = file_get_contents($url);
// Parse XML thành Object
$xml = simplexml_load_string($response);
print_r($xml);
?&gt;</code></pre>
                    </div>`;

content = content.replace(
    /(<div id="tab-list-php".*?<\/div>)/s,
    '$1\n                    ' + phpXmlContent
);

// We also need to update switchTabList function
content = content.replace(
    /const tabs = \['js', 'php', 'python'\];/,
    "const tabs = ['js', 'php', 'php-xml', 'python'];"
);

fs.writeFileSync('views/api-document.ejs', content, 'utf8');
console.log('Patched XML tabs');
