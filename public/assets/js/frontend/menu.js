function urlPath(url) {
    try {
        return new URL(url, window.location.origin).pathname.replace(/^\/|\/$/g, "");
    } catch {
        return String(url).replace(/^\/|\/$/g, "");
    }
}

function renderMenu(items, currentPath) {
    return items.map(item => {
        const itemPath = urlPath(item.url);
        const isActive = itemPath === currentPath;
        const children = item.children ?? [];

        if (children.length === 0) {
            return `<a href="${item.url}" target="${item.target ?? '_self'}" class="${isActive ? 'active' : ''}">${item.label}</a>`;
        }

        const hasActiveChild = children.some(c => urlPath(c.url) === currentPath);

        const childLinks = children.map(child => {
            const childActive = urlPath(child.url) === currentPath;
            return `<a href="${child.url}" target="${child.target ?? '_self'}" class="${childActive ? 'active' : ''}">${child.label}</a>`;
        }).join("");

        return `
            <div class="nav-dropdown">
                <a href="${item.url}" target="${item.target ?? '_self'}" class="${isActive || hasActiveChild ? 'active' : ''}">${item.label} ▾</a>
                <div class="nav-dropdown-menu">${childLinks}</div>
            </div>
        `;
    }).join("");
}

// ── Logo ──────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", async () => {
    const logoContainer = document.getElementById("siteLogo");

    try {
        const res = await fetch("/api/public/settings/logo");
        const json = await res.json();

        if (!json.status) {
            console.error("Logo fetch failed:", json.message);
            return;
        }

        const { logo_url, website_name } = json.data;

        if (logo_url) {
            logoContainer.innerHTML = `<img src="${logo_url}" alt="${website_name ?? 'Logo'}">`;
        }
    } catch (err) {
        console.error("Logo load failed:", err);
    }
});

// ── Header Menu (single source of truth — resolves page key first) ─────
document.addEventListener("DOMContentLoaded", async () => {
    const menuContainer = document.getElementById("header-menu");
    const currentPath = window.location.pathname;

    try {
        // Step 1: resolve page key from current path
        const pageRes = await fetch(`/api/public/current-page?path=${encodeURIComponent(currentPath)}`);
        const pageJson = await pageRes.json();
        const pageKey = pageJson.data?.page || "";

        // Step 2: fetch menu filtered by that page
        const menuRes = await fetch(`/api/public/menus/location/header?page=${encodeURIComponent(pageKey)}`);
        const menuJson = await menuRes.json();

        if (!menuJson.status) {
            console.error("Menu fetch failed:", menuJson.message);
            return;
        }

        const menu = menuJson.data[0];
        menuContainer.innerHTML = renderMenu(menu?.items ?? [], currentPath.replace(/^\/|\/$/g, ""));
    } catch (err) {
        console.error("Menu load failed:", err);
    }
});