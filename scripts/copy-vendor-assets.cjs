const fs = require('fs');
const path = require('path');

const copies = [
    {
        from: 'node_modules/fullcalendar/index.global.min.js',
        to: 'public/vendor/fullcalendar/index.global.min.js',
    },
];

for (const { from, to } of copies) {
    if (!fs.existsSync(from)) {
        console.warn('[copy-vendor-assets] Skip (missing):', from);
        continue;
    }
    fs.mkdirSync(path.dirname(to), { recursive: true });
    fs.copyFileSync(from, to);
    console.log('[copy-vendor-assets] Copied', to);
}
