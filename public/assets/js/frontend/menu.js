function urlPath(url) {
    try {
        return new URL(url, window.location.origin).pathname.replace(/^\/|\/$/g, "");
    } catch {
        return String(url).replace(/^\/|\/$/g, "");
    }
}

// Recursively checks if this item OR any descendant matches the current path
function hasActiveDescendant(item, currentPath) {
    const children = item.children ?? [];
    return children.some(child =>
        urlPath(child.url) === currentPath || hasActiveDescendant(child, currentPath)
    );
}

function renderMenuItem(item, currentPath, depth = 0) {
    const itemPath = urlPath(item.url);
    const isActive = itemPath === currentPath;
    const children = item.children ?? [];
    const hasChildren = children.length > 0;
    const hasActiveChild = hasChildren && hasActiveDescendant(item, currentPath);

    return `
        <div class="nav-item ${hasChildren ? "has-children" : ""} depth-${depth}">
            <a href="${item.url}"
               target="${item.target ?? "_self"}"
               class="${isActive || hasActiveChild ? "" : ""}">
                ${item.label}
                ${hasChildren ? '<span class="arrow">▾</span>' : ""}
            </a>

            ${hasChildren ? `
                <div class="nav-dropdown-menu">
                    ${children.map(child => renderMenuItem(child, currentPath, depth + 1)).join("")}
                </div>
            ` : ""}
        </div>
    `;
}

function renderMenu(items, currentPath) {
    return items.map(item => renderMenuItem(item, currentPath, 0)).join("");
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

// ── Header Menu ──────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", async () => {
    const menuContainer = document.getElementById("header-menu");
    const currentPath = window.location.pathname;

    try {
        const menuRes = await fetch(`/api/public/menus/location/header`);
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

document.addEventListener("DOMContentLoaded", () => {
    const hamburger = document.getElementById("hamburger");
    const navbar = document.getElementById("navbar");

    hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("active");
        navbar.classList.toggle("active");
    });
});