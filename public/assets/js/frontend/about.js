
    document.addEventListener('DOMContentLoaded', async function () {
        try {
            const res = await fetch('/api/public/about');
            const json = await res.json();
            const c = json.data;

            document.getElementById('aboutLoading').classList.add('d-none');
                
            if (!c) return;

            // About section
            if (c.about_badge) {
                const el = document.getElementById('aboutBadge');
                el.textContent = c.about_badge;
                el.classList.remove('d-none');
            }
            document.getElementById('aboutHeading').textContent = c.about_heading ?? '';
            document.getElementById('aboutDesc1').innerHTML = c.about_description_1 ?? '';
            document.getElementById('aboutDesc2').innerHTML = c.about_description_2 ?? '';
            document.getElementById('aboutJournalContent').classList.remove('d-none');

            const imgWrap = document.getElementById('journalImages');
            [c.about_section_img1_url, c.about_section_img2_url].forEach(url => {
                if (url) {
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = 'Journal Cover';
                    imgWrap.appendChild(img);
                }
            });
            if (imgWrap.children.length) imgWrap.classList.remove('d-none');

            // Why section
            if (c.why_section_image_url) {
                const img = document.createElement('img');
                img.src = c.why_section_image_url;
                img.alt = 'Researchers';
                const wrap = document.getElementById('whyImageWrap');
                wrap.appendChild(img);
                wrap.classList.remove('d-none');
            }

            if (c.why_badge) {
                const el = document.getElementById('whyBadge');
                el.textContent = c.why_badge;
                el.classList.remove('d-none');
            }
            document.getElementById('whyHeading').textContent = c.why_heading ?? '';
            document.getElementById('whyDesc1').innerHTML = c.why_description_1 ?? '';
            document.getElementById('whyDesc2').innerHTML = c.why_description_2 ?? '';
            document.getElementById('whyContent').classList.remove('d-none');

        } catch (e) {
            document.getElementById('aboutLoading').classList.add('d-none');
            console.error('Failed to load about content', e);
        }
    });
