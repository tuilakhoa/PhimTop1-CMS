const fs = require('fs');
let content = fs.readFileSync('server.js', 'utf8');

const xmlRequire = `const express = require('express');
const xmljs = require('xml-js');

function sendApiResponse(req, res, data) {
    const format = req.query.format ? req.query.format.toLowerCase() : 'json';
    if (format === 'xml') {
        res.header('Content-Type', 'application/xml');
        // Try to handle null/undefined safely
        const cleanData = JSON.parse(JSON.stringify(data));
        const xmlData = {
            _declaration: { _attributes: { version: '1.0', encoding: 'utf-8' } },
            response: cleanData
        };
        try {
            const xmlString = xmljs.js2xml(xmlData, { compact: true, spaces: 4 });
            res.send(xmlString);
        } catch(e) {
            res.status(500).send('<error>XML Conversion Failed</error>');
        }
    } else {
        res.json(data);
    }
}`;

content = content.replace("const express = require('express');", xmlRequire);
fs.writeFileSync('server.js', content, 'utf8');
