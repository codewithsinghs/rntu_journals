document.addEventListener('DOMContentLoaded', async function () {
    const app = document.getElementById('guidelinesApp');
    if (!app) return;

    const journalParam = app.dataset.journalParam;

    try {
        const res = await fetch(`/api/public/guidelines/${encodeURIComponent(journalParam)}`);
        const json = await res.json();
        const c = json.data;

        document.getElementById('guidelinesLoading').classList.add('d-none');

        if (!c) return;

        function setBadge(id, value) {
            const el = document.getElementById(id);
            el.textContent = value ?? '';
            el.classList.toggle('d-none', !value);
        }

        setBadge('authorBadge', c.author_badge);
        document.getElementById('authorHeading').textContent = c.author_heading ?? '';
        document.getElementById('authorDescription').innerHTML = c.author_description ?? '';

        setBadge('processBadge', c.process_badge);
        document.getElementById('processHeading').textContent = c.process_heading ?? '';
        document.getElementById('processDescription').innerHTML = c.process_description ?? '';

        setBadge('manuscriptBadge', c.manuscript_badge);
        document.getElementById('manuscriptHeading').textContent = c.manuscript_heading ?? '';
        document.getElementById('manuscriptDescription').innerHTML = c.manuscript_description ?? '';

        setBadge('formattingBadge1', c.formatting_badge1);
        document.getElementById('formattingHeading').textContent = c.formatting_heading ?? '';
        document.getElementById('formattingDescription').innerHTML = c.formatting_description ?? '';
        setBadge('formattingBadge2', c.formatting_badge2);

        setBadge('layoutBadge1', c.layout_badge1);
        document.getElementById('layoutHeading').textContent = c.layout_heading ?? '';
        document.getElementById('layoutDescription').innerHTML = c.layout_description ?? '';

        setBadge('acknowledgementBadge1', c.acknowlegdement_badge1);
        document.getElementById('acknowledgementHeading').textContent = c.acknowlegdement_heading ?? '';
        document.getElementById('acknowledgementDescription').innerHTML = c.acknowlegdement_description ?? '';

        document.getElementById('guidelinesContent').classList.remove('d-none');
    } catch (e) {
        document.getElementById('guidelinesLoading').classList.add('d-none');
        console.error('Failed to load guidelines content', e);
    }
});