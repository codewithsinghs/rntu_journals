document.addEventListener('DOMContentLoaded', async function () {
    try {
        const res = await fetch('/api/public/guidelines');
        const json = await res.json();
        const c = json.data;

        document.getElementById('guidelinesLoading').classList.add('d-none');

        if (!c) return;

        document.getElementById('authorBadge').textContent = c.author_badge ?? '';
        document.getElementById('authorHeading').textContent = c.author_heading ?? '';
        document.getElementById('authorDescription').innerHTML = c.author_description ?? '';

        // document.getElementById('processBadge').textContent = c.process_badge ?? '';
        document.getElementById('processHeading').textContent = c.process_heading ?? '';
        document.getElementById('processDescription').innerHTML = c.process_description ?? '';

        document.getElementById('manuscriptBadge').textContent = c.manuscript_badge ?? '';
        document.getElementById('manuscriptHeading').textContent = c.manuscript_heading ?? '';
        document.getElementById('manuscriptDescription').innerHTML = c.manuscript_description ?? '';

        document.getElementById('formattingBadge1').textContent = c.formatting_badge1 ?? '';
        document.getElementById('formattingHeading').textContent = c.formatting_heading ?? '';
        document.getElementById('formattingDescription').innerHTML = c.formatting_description ?? '';
        document.getElementById('formattingBadge2').textContent = c.formatting_badge2 ?? '';

        document.getElementById('layoutBadge1').textContent = c.layout_badge1 ?? '';
        document.getElementById('layoutHeading').textContent = c.layout_heading ?? '';
        document.getElementById('layoutDescription').innerHTML = c.layout_description ?? '';

        document.getElementById('acknowledgementBadge1').textContent = c.acknowlegdement_badge1 ?? '';
        document.getElementById('acknowledgementHeading').textContent = c.acknowlegdement_heading ?? '';
        document.getElementById('acknowledgementDescription').innerHTML = c.acknowlegdement_description ?? '';

        document.getElementById('guidelinesContent').classList.remove('d-none');
    } catch (e) {
        document.getElementById('guidelinesLoading').classList.add('d-none');
        console.error('Failed to load guidelines content', e);
    }
});