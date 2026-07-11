const fs = require('fs');

const path = 'resources/js/Pages/Test/Reading/Show.vue';
let content = fs.readFileSync(path, 'utf8');

// Replace the Bottom Navigation bar
const bottomNavRegex = /<div class="fixed bottom-0 left-0 right-0 h-\[\60px\] bg-white border-t border-gray-200[\s\S]*?<\/div>\s*<\/main>/;// Hmm, wait, regex can be tricky.
