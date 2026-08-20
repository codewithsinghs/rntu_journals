document.addEventListener("DOMContentLoaded", async () => {
    try {
        const res = await fetch("/api/public/footer");
        const json = await res.json();

        if (!json.status) {
            console.error("Footer fetch failed:", json.message);
            return;
        }

        const data = json.data;
        renderAbout(data.about_description);
        renderLinks("footerUsefulLinks", data.useful_links, "No links available.");
        renderLinks("footerJournalPolicies", data.journal_policies, "No policies available.");
        renderContact(data.settings);
        renderCopyright(data.settings);
        renderSocialLinks(data.settings);
        renderBottomLinks(data.bottom_links);

    } catch (err) {
        console.error("Footer load failed:", err);
    }
});

function renderAbout(html) {
    const el = document.getElementById("footerAboutDescription");
    if (el) el.innerHTML = html || "";
}

function renderLinks(containerId, links, emptyMessage) {
    const el = document.getElementById(containerId);
    if (!el) return;

    if (!links || links.length === 0) {
        el.innerHTML = `<li>${emptyMessage}</li>`;
        return;
    }

    el.innerHTML = links
        .map(link => `<li><a href="${link.url}" target="${link.target}" style="color:inherit; text-decoration:none;">${link.label}</a></li>`)
        .join("");
}

function renderContact(settings) {
    const el = document.getElementById("footerContact");
    if (!el) return;

    let html = "";

    if (settings.address) {
        html += contactBlock("footer_address.png", "Address", `<p>${settings.address}</p>`);
    }
    if (settings.email) {
        html += contactBlock("footer_email.png", "Email", `<a href="mailto:${settings.email}" style="color:inherit;">${settings.email}</a>`);
    }
    if (settings.phone) {
        html += contactBlock("footer_phone.png", "Phone", `<a href="tel:${settings.phone}" style="color:inherit;">${settings.phone}</a>`);
    }
    if (settings.website_url) {
        html += contactBlock("footer_website.png", "Website", `<a href="${settings.website_url}" style="color:inherit;" target="_blank">${settings.website_name || 'RNTU Journals'}</a>`);
    }

    el.innerHTML = html;
}

function contactBlock(icon, label, contentHtml) {
    return `
        <ol style="padding:0;">
            <div class="contact_item">
                <div class="contact_icon">
                    <img src="/images/home_page/${icon}" alt="${label}">
                </div>
                <div class="contact_text">
                    <h4>${label}</h4>
                    ${contentHtml}
                </div>
            </div>
        </ol>
    `;
}

function renderCopyright(settings) {
    const el = document.getElementById("footerWebsiteName");
    if (el) el.textContent = settings.website_name || "RNTU Journal";
}

function renderSocialLinks(settings) {
    const el = document.getElementById("footerSocialLinks");
    if (!el) return;

    const icons = [
        ["facebook_url", "Facebook", "fa-facebook-f"],
        ["instagram_url", "Instagram", "fa-instagram"],
        ["twitter_url", "Twitter", "fa-twitter"],
        ["youtube_url", "YouTube", "fa-youtube"],
        ["linkedin_url", "LinkedIn", "fa-linkedin-in"],
    ];

    el.innerHTML = icons
        .filter(([key]) => settings[key])
        .map(([key, label, iconClass]) =>
            `<a href="${settings[key]}" aria-label="${label}" target="_blank" rel="noopener"><span><i class="fa-brands ${iconClass}"></i></span></a>`
        ).join("");
}

function renderBottomLinks(links) {
    const el = document.getElementById("footerBottomLinks");
    if (!el || !links || links.length === 0) return; // keep static fallback if empty

    el.innerHTML = links
        .map(link => `<li><a href="${link.url}" target="${link.target}" style="color:inherit;text-decoration:none;">${link.label}</a></li>`)
        .join("");
}

document.addEventListener('DOMContentLoaded', async function () {
    const visitorCountEl = document.getElementById('visitor-count');

    if (!visitorCountEl) return;

    try {
        const res = await fetch('/api/public/visitor-count');
        const json = await res.json();

        if (!json.status) {
            console.error('Visitor count fetch failed:', json.message);
            return;
        }

        visitorCountEl.textContent = json.data.count.toLocaleString();
    } catch (err) {
        console.error('Visitor count load failed:', err);
    }
});