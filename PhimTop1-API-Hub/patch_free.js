const fs = require('fs');

let content = fs.readFileSync('views/cms.ejs', 'utf-8');

// Update Select Dropdown
content = content.replace(
    '<option>Miễn phí</option>\n                        <option>Cao cấp</option>',
    '<option>Nhiều lượt tải</option>\n                        <option>Đánh giá cao</option>'
);

// Update Theme 3 title
content = content.replace(
    '<h3 class="text-xl font-bold text-white">Netflx Premium</h3>',
    '<h3 class="text-xl font-bold text-white">Netflx Clone</h3>'
);

// Update Premium badge to Free (Green)
content = content.replace(
    '<span class="bg-purple-500/10 text-purple-400 text-xs px-2.5 py-1 rounded-full font-medium border border-purple-500/20">Premium</span>',
    '<span class="bg-green-500/10 text-green-400 text-xs px-2.5 py-1 rounded-full font-medium border border-green-500/20">Miễn phí</span>'
);

// Update Buy button to Install
content = content.replace(
    '<button class="bg-purple-600 hover:bg-purple-500 text-white font-medium px-4 py-1.5 rounded text-sm flex items-center gap-2 transition">\n                                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Mua $19\n                            </button>',
    '<button class="bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500 hover:text-white border border-cyan-500/20 font-medium px-4 py-1.5 rounded text-sm flex items-center gap-2 transition">\n                                <i data-lucide="download" class="w-4 h-4"></i> Cài đặt\n                            </button>'
);

fs.writeFileSync('views/cms.ejs', content);
console.log('Successfully patched cms.ejs for free themes');
