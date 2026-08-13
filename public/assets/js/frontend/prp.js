document.addEventListener('DOMContentLoaded', async function () {
    try {
        const res = await fetch('/api/public/prp');
        const json = await res.json();
        const c = json.data;

        document.getElementById('guidelinesLoading')?.classList.add('d-none');

        if (!c) return;

        document.getElementById('authorHeading').textContent = c.author_heading ?? '';
        document.getElementById('authorDescription').innerHTML = c.author_description ?? '';

        document.getElementById('guidelinesContent')?.classList.remove('d-none');

    } catch (e) {
        document.getElementById('guidelinesLoading')?.classList.add('d-none');
        console.error('Failed to load peer review process content', e);
    }
});