const xmljs = require('xml-js');
const data = {
    status: true,
    items: [{id: 1, name: 'A'}, {id: 2, name: 'B'}],
    movie: { slug: 'phim-1' }
};
const xmlData = {
    _declaration: { _attributes: { version: '1.0', encoding: 'utf-8' } },
    response: data
};
console.log(xmljs.js2xml(xmlData, { compact: true, spaces: 4 }));
