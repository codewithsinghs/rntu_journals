(function () {
    // Columns that always stay visible in the compact layout
    const PRIMARY = ['Article ID', 'Stage', 'Actions'];

    const table = document.querySelector('.status-table');
    const tbody = document.getElementById('saTableBody');
    if (!table || !tbody) return;

    function enhance() {
        // Only count headers that are actually visible, so the labels line up
        // with the cells that were rendered (reviewer columns can be hidden)
        const labels = [...table.querySelectorAll('thead th:not(.d-none)')]
            .map(th => th.textContent.trim());

        tbody.querySelectorAll('tr:not([data-sa-enh])').forEach(tr => {
            tr.dataset.saEnh = '1';
            const cells = tr.querySelectorAll(':scope > td');
            if (cells.length < 2) return;   // "empty" / colspan rows

            cells.forEach((td, i) => {
                const label = labels[i] || '';
                td.dataset.label = label;
                if (!PRIMARY.includes(label)) td.classList.add('sa-secondary');
            });

            // Toggle button goes in the first (Article ID) cell
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'sa-toggle';
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('aria-label', 'Show details');
            btn.textContent = '+';
            cells[0].prepend(btn);
        });
    }

    // Open / close a row with the + button
    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.sa-toggle');
        if (!btn) return;
        const tr = btn.closest('tr');
        const open = tr.classList.toggle('sa-open');
        btn.textContent = open ? '−' : '+';
        btn.setAttribute('aria-expanded', open);
    });

    // Re-run whenever submitarticle.js re-renders rows (search, pagination, etc.)
    new MutationObserver(enhance).observe(tbody, { childList: true });
    enhance();

    // Raise the cell while its dropdown is open (fallback for browsers without :has())
    document.addEventListener('show.bs.dropdown', e => {
        const td = e.target.closest('td');
        if (td) td.style.zIndex = 50;
    });
    document.addEventListener('hidden.bs.dropdown', e => {
        const td = e.target.closest('td');
        if (td) td.style.zIndex = '';
    });
})();