const fs = require('fs');

let content = fs.readFileSync('views/cms.ejs', 'utf-8');

// Update Theme 3 title in the overlay text
content = content.replace(
    '<span class="absolute text-xl font-bold opacity-30 text-purple-400">NetFlix Clone</span>',
    '<span class="absolute text-xl font-bold opacity-30 text-purple-400">NetStream</span>'
);

// Update Theme 3 title in the card content
content = content.replace(
    '<h3 class="text-xl font-bold text-white">Netflx Clone</h3>',
    '<h3 class="text-xl font-bold text-white">NetStream</h3>'
);

fs.writeFileSync('views/cms.ejs', content);
console.log('Successfully patched cms.ejs for theme name');
