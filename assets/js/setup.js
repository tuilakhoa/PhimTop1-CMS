document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    // Step logic (made global so it can be called inline if needed)
    window.goToStep = function(step) {
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        const stepEl = document.getElementById('step-' + step);
        if (stepEl) {
            stepEl.classList.remove('hidden');
        }

        // Update indicators
        for (let i = 1; i <= 3; i++) {
            const ind = document.getElementById('ind-' + i);
            if (!ind) continue;
            
            const circle = ind.querySelector('div');
            const text = ind.querySelector('span');

            if (i < step) {
                // Passed steps
                ind.classList.remove('opacity-50');
                circle.className = 'w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm z-10 shadow-[0_0_0_4px_#1e293b] ring-2 ring-blue-500/50 transition-all';
                circle.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                text.classList.remove('text-slate-300');
                text.classList.add('text-white', 'font-semibold');
            } else if (i === step) {
                // Current step
                ind.classList.remove('opacity-50');
                circle.className = 'w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm z-10 shadow-[0_0_0_4px_#1e293b] ring-2 ring-blue-500/50 transition-all';
                circle.innerHTML = i;
                text.classList.remove('text-slate-300');
                text.classList.add('text-white', 'font-semibold');
            } else {
                // Future steps
                ind.classList.add('opacity-50');
                circle.className = 'w-8 h-8 rounded-full bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-sm z-10 shadow-[0_0_0_4px_#1e293b] transition-all';
                circle.innerHTML = i;
                text.classList.remove('text-white', 'font-semibold');
                text.classList.add('text-slate-300', 'font-medium');
            }
        }
    };

    // Checkbox listener step 1
    const agreeCheckbox = document.getElementById('agree-terms');
    const btnStep1 = document.getElementById('btn-step1');
    if (agreeCheckbox && btnStep1) {
        agreeCheckbox.addEventListener('change', function() {
            btnStep1.disabled = !this.checked;
        });
    }

    // DB Type Toggle
    const dbTypeSelect = document.getElementById('dbType');
    if (dbTypeSelect) {
        dbTypeSelect.addEventListener('change', function() {
            if (this.value === 'mysql') {
                document.getElementById('mysqlFields').classList.remove('hidden');
                document.getElementById('firestoreFields').classList.add('hidden');
            } else {
                document.getElementById('mysqlFields').classList.add('hidden');
                document.getElementById('firestoreFields').classList.remove('hidden');
            }
        });
    }
});
