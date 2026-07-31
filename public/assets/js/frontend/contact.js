document.addEventListener('DOMContentLoaded', async function () {
    const loadingEl = document.getElementById('contactLoading');
    const contentEl = document.getElementById('contactContent');

    try {
        const res = await fetch('/api/public/contact');
        if (!res.ok) throw new Error(`Request failed with status ${res.status}`);

        const json = await res.json();
        const c = json.data;

        loadingEl.classList.add('d-none');
        if (!c) return;

        if (c.contact_badge) {
            document.getElementById('contactBadge').textContent = c.contact_badge;
            document.getElementById('contactBadgeWrap').classList.remove('d-none');
        }

        document.getElementById('contactHeading1').textContent = c.contact_heading1 ?? '';
        document.getElementById('contactDetail1').innerHTML = c.contact_detail1 ?? '';

        document.getElementById('contactHeading2').textContent = c.contact_heading2 ?? '';
        document.getElementById('contactDetail2').innerHTML = c.contact_detail2 ?? '';

        document.getElementById('contactHeading3').textContent = c.contact_heading3 ?? '';
        document.getElementById('contactDetail3').innerHTML = c.contact_detail3 ?? '';

        contentEl.classList.remove('d-none');
    } catch (e) {
        loadingEl.classList.add('d-none');
        console.error('Failed to load contact content', e);
    }
});