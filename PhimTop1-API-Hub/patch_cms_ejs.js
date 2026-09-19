const fs = require('fs');

let content = fs.readFileSync('views/cms.ejs', 'utf-8');

const themeLoop = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <% themes.forEach(theme => { %>
                <!-- Theme Card -->
                <div class="bg-card border border-card rounded-xl overflow-hidden group hover:border-cyan-500/50 transition duration-300">
                    <div class="relative aspect-video bg-[#1f2937] overflow-hidden">
                        <img src="<%= theme.image_url %>" alt="<%= theme.name %>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" onerror="this.src='https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Image+Not+Found'">
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4 backdrop-blur-sm">
                            <button class="bg-cyan-600 hover:bg-cyan-500 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center gap-2"><i data-lucide="eye" class="w-4 h-4"></i> Xem Trước</button>
                        </div>
                    </div>
                    <div class="p-5 flex flex-col h-[180px]">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-white"><%= theme.name %></h3>
                            <span class="bg-green-500/10 text-green-400 text-xs px-2.5 py-1 rounded-full font-medium border border-green-500/20">Miễn phí</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2 flex-grow"><%= theme.description %></p>
                        <div class="flex items-center justify-between border-t border-card pt-4 mt-auto">
                            <div class="flex items-center gap-1 text-yellow-500 text-sm">
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                <span class="font-medium text-white"><%= theme.rating %></span>
                                <span class="text-gray-500 text-xs ml-1">(<%= theme.downloads %>)</span>
                            </div>
                            <a href="<%= theme.download_url %>" class="bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500 hover:text-white border border-cyan-500/20 font-medium px-4 py-1.5 rounded text-sm flex items-center gap-2 transition">
                                <i data-lucide="download" class="w-4 h-4"></i> Cài đặt
                            </a>
                        </div>
                    </div>
                </div>
                <% }) %>
            </div>
`;

// Replace from '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">' to '</div>' before '<!-- Features Section -->'
const startIdx = content.indexOf('<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">');
const featuresIdx = content.indexOf('<!-- Features Section -->');
const searchEndStr = '</div>\n        </div>\n        \n        <!-- Features Section -->';
const endIdx = content.indexOf(searchEndStr, startIdx);

if (startIdx !== -1 && endIdx !== -1) {
    content = content.substring(0, startIdx) + themeLoop + '\n            <div class="mt-10 text-center">\n                <button class="px-6 py-2.5 bg-card border border-card hover:border-cyan-500/50 hover:text-cyan-400 text-white rounded-lg transition font-medium">Tải thêm Themes...</button>\n            </div>\n        </div>\n        \n        <!-- Features Section -->' + content.substring(endIdx + searchEndStr.length);
    fs.writeFileSync('views/cms.ejs', content);
    console.log("Successfully patched cms.ejs");
} else {
    console.log("Could not find replacement points.");
}
