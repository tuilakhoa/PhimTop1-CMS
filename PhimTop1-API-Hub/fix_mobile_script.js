const fs = require('fs');
let code = fs.readFileSync('new_header.php', 'utf8');

// Fix the tailwind script tag issue
code = code.replace(`<script src="https://cdn.tailwindcss.com">    
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
</script>`, `<script src="https://cdn.tailwindcss.com"></script>`);

// Fix the button placement
// We want the button inside the <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
const flexDivEnd = `        </div>
        <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-white focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>`;
const newFlexDivEnd = `        <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-white focus:outline-none ml-4">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        </div>`;
code = code.replace(flexDivEnd, newFlexDivEnd);

// Add the JS logic at the VERY END of the file (after the live search script)
if (!code.includes('mobileMenu.classList.toggle')) {
    code += `
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
`;
}
fs.writeFileSync('new_header.php', code);
