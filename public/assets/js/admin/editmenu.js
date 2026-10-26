const PAGE_KEYS = JSON.parse(
    document.getElementById("pageKeysData").textContent || "{}",
);

const API = "/api/admin/menus";
const CSRF_TOKEN =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

const configEl = document.getElementById("itemFormConfig");
const MENU_ID = parseInt(configEl?.dataset.menuId || "0", 10);
const MANAGE_MENU_URL = configEl?.dataset.manageMenuUrl || "/admin/menus";

const params = new URLSearchParams(window.location.search);
const EDIT_PATH = params.get("path");
const EDIT_PATH_ARR = EDIT_PATH ? EDIT_PATH.split("-").map(Number) : null;

let currentMenu = null;
let confirmRemoveItemModal;

document.addEventListener("DOMContentLoaded", function () {
    confirmRemoveItemModal = new bootstrap.Modal(
        document.getElementById("confirmRemoveItemModal"),
    );

    buildPageSelectOptions();
    loadMenu();

    document.getElementById("label").addEventListener("input", function () {
        document.getElementById("slug").value = slugify(this.value);
    });

    document
        .getElementById("itemForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            saveItem();
        });

    document
        .getElementById("removeItemBtn")
        .addEventListener("click", function () {
            const label = document.getElementById("label").value || "this item";
            document.getElementById("removeItemLabel").textContent = label;
            confirmRemoveItemModal.show();
        });

    document
        .getElementById("confirmRemoveItemBtn")
        .addEventListener("click", function () {
            confirmRemoveItemModal.hide();
            executeDeleteItem();
        });
});

function jsonHeaders() {
    return {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": CSRF_TOKEN,
    };
}

// ── Load the parent menu so we can read/write its items array ────
function loadMenu() {
    fetch(API, {
        credentials: "include",
        headers: { Accept: "application/json" },
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            const menus = data.data || [];
            currentMenu = menus.find((m) => m.id === MENU_ID);

            if (!currentMenu) {
                document.getElementById("pageLoader").innerHTML =
                    '<div class="text-center text-danger py-4">Menu not found.</div>';
                return;
            }

            buildParentSelectOptions();
            populateForm();
            document.getElementById("pageLoader").classList.add("d-none");
            document.getElementById("itemForm").classList.remove("d-none");
        })
        .catch((err) => {
            console.error("Load menu failed:", err.message);
            document.getElementById("pageLoader").innerHTML =
                '<div class="text-center text-danger py-4">Failed to load menu.</div>';
        });
}

// ── Tree helpers ───────────────────────────────────────────────────
function getNodeByPath(items, path) {
    let node = null;
    let list = items;
    for (let i = 0; i < path.length; i++) {
        node = list[path[i]];
        if (!node) return null;
        list = node.children || [];
    }
    return node;
}

function getSiblingsAndIndex(items, path) {
    const idx = path[path.length - 1];
    if (path.length === 1) return { siblings: items, idx };
    let list = items;
    for (let i = 0; i < path.length - 1; i++) {
        list = list[path[i]].children || (list[path[i]].children = []);
    }
    return { siblings: list, idx };
}

// Children array a given parent path should insert into. [] path => root.
function getChildrenArrayForParentPath(items, parentPath) {
    if (!parentPath || parentPath.length === 0) return items;
    const node = getNodeByPath(items, parentPath);
    if (!node) return items;
    if (!node.children) node.children = [];
    return node.children;
}

// True if `maybePath` is the same as, or inside the subtree of, `ancestorPath`.
function isSameOrDescendantPath(maybePath, ancestorPath) {
    if (maybePath.length < ancestorPath.length) return false;
    for (let i = 0; i < ancestorPath.length; i++) {
        if (maybePath[i] !== ancestorPath[i]) return false;
    }
    return true;
}

// Flattens the whole tree into [{ path, label, depth }], in display order.
function flattenItems(items, path = []) {
    const out = [];
    (items || []).forEach((item, idx) => {
        const itemPath = [...path, idx];
        out.push({ path: itemPath, label: item.label || "(no label)", depth: itemPath.length - 1 });
        out.push(...flattenItems(item.children || [], itemPath));
    });
    return out;
}

// ── Parent Menu dropdown ────────────────────────────────────────────
function buildParentSelectOptions() {
    const select = document.getElementById("parentSelect");
    select.innerHTML = '<option value="">— Top Level —</option>';

    const flat = flattenItems(currentMenu.items || []);
    flat.forEach(({ path, label, depth }) => {
        if (EDIT_PATH_ARR && isSameOrDescendantPath(path, EDIT_PATH_ARR)) return;

        const opt = document.createElement("option");
        opt.value = path.join("-");
        opt.textContent = `${"— ".repeat(depth)}${label}`;
        select.appendChild(opt);
    });
}

// ── Select Page dropdown (from config('menu.pages')) ────────────────
function buildPageSelectOptions() {
    const select = document.getElementById("pageSelect");
    select.innerHTML = '<option value="">Select One</option>';

    Object.keys(PAGE_KEYS).forEach((key) => {
        const opt = document.createElement("option");
        opt.value = key;
        opt.textContent = PAGE_KEYS[key];
        select.appendChild(opt);
    });
}

function populateForm() {
    if (EDIT_PATH_ARR) {
        const item = getNodeByPath(currentMenu.items || [], EDIT_PATH_ARR);

        if (!item) {
            document.getElementById("pageLoader").innerHTML =
                '<div class="text-center text-danger py-4">Item not found.</div>';
            return;
        }

        document.getElementById("label").value = item.label || "";
        document.getElementById("type").value = item.type || "url";
        document.getElementById("url").value = item.url || "";
        document.getElementById("pageSelect").value = item.page_key || "";
        document.getElementById("css_class").value = item.css_class || "";
        document.getElementById("css_id").value = item.css_id || "";
        document.getElementById("slug").value = item.slug || slugify(item.label || "");

        const parentPath = EDIT_PATH_ARR.slice(0, -1);
        document.getElementById("parentSelect").value = parentPath.join("-");
    } else {
        document.getElementById("type").value = "url";
        const parentParam = params.get("parent");
        if (parentParam) {
            document.getElementById("parentSelect").value = parentParam;
        }
    }
}

// ── Save (create or update, including reparenting via Parent Menu) ──
function saveItem() {
    const label = document.getElementById("label").value.trim();
    if (!label) {
        showFieldError("label", "Label is required.");
        return;
    }
    clearFieldError("label");

    const type = document.getElementById("type").value;
    const parentValue = document.getElementById("parentSelect").value;
    const newParentPath = parentValue ? parentValue.split("-").map(Number) : [];

    const clone = JSON.parse(JSON.stringify(currentMenu.items || []));

    const fieldsFromForm = {
        label,
        type,
        url: document.getElementById("url").value.trim(),
        page_key: document.getElementById("pageSelect").value,
        css_class: document.getElementById("css_class").value.trim(),
        css_id: document.getElementById("css_id").value.trim(),
        slug: document.getElementById("slug").value || slugify(label),
    };

    let node;

    if (EDIT_PATH_ARR) {
        const targetChildren = getChildrenArrayForParentPath(clone, newParentPath);

        const { siblings, idx } = getSiblingsAndIndex(clone, EDIT_PATH_ARR);
        const [existing] = siblings.splice(idx, 1);

        node = { ...existing, ...fieldsFromForm, children: existing.children || [] };
        targetChildren.push(node);
    } else {
        const targetChildren = getChildrenArrayForParentPath(clone, newParentPath);
        node = { ...fieldsFromForm, is_active: true, children: [] };
        targetChildren.push(node);
    }

    const saveBtn = document.getElementById("saveBtn");
    saveBtn.disabled = true;
    saveBtn.textContent = "Saving...";

    fetch(`${API}/${MENU_ID}`, {
        method: "PUT",
        credentials: "include",
        headers: jsonHeaders(),
        body: JSON.stringify({
            name: currentMenu.name,
            location: currentMenu.location,
            is_active: currentMenu.is_active ? 1 : 0,
            items: clone,
        }),
    })
        .then(handleAuthErrors)
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    throw new Error(
                        (firstError && firstError[0]) || "Validation failed",
                    );
                }
                throw new Error(data.message || "Save failed");
            }
            return data;
        })
        .then(() => {
            window.location.href = MANAGE_MENU_URL;
        })
        .catch((err) => {
            alert(err.message || "Save failed.");
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = "Update";
        });
}

// ── Delete: remove this item, promoting its children into its place ──
function executeDeleteItem() {
    if (!EDIT_PATH_ARR) return;

    const clone = JSON.parse(JSON.stringify(currentMenu.items || []));
    const { siblings, idx } = getSiblingsAndIndex(clone, EDIT_PATH_ARR);

    const [removed] = siblings.splice(idx, 1);
    const promoted = removed.children || [];
    siblings.splice(idx, 0, ...promoted);

    fetch(`${API}/${MENU_ID}`, {
        method: "PUT",
        credentials: "include",
        headers: jsonHeaders(),
        body: JSON.stringify({
            name: currentMenu.name,
            location: currentMenu.location,
            is_active: currentMenu.is_active ? 1 : 0,
            items: clone,
        }),
    })
        .then(handleAuthErrors)
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || "Delete failed");
            return data;
        })
        .then(() => {
            window.location.href = MANAGE_MENU_URL;
        })
        .catch((err) => {
            alert(err.message || "Delete failed.");
        });
}

// ── Slug ─────────────────────────────────────────────────────────
function slugify(str) {
    return (str || "")
        .toString()
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
}

// ── Field errors ─────────────────────────────────────────────────
function showFieldError(field, message) {
    const input = document.getElementById(field);
    const errorEl = document.getElementById(`${field}Error`);
    if (input) input.classList.add("is-invalid");
    if (errorEl) errorEl.textContent = message;
}

function clearFieldError(field) {
    const input = document.getElementById(field);
    const errorEl = document.getElementById(`${field}Error`);
    if (input) input.classList.remove("is-invalid");
    if (errorEl) errorEl.textContent = "";
}

// ── Auth errors ──────────────────────────────────────────────────
function handleAuthErrors(res) {
    if (res.status === 401) {
        window.location.href = "/login";
        throw new Error("Not authenticated");
    }
    if (res.status === 419) {
        alert("Session expired. Reloading...");
        setTimeout(() => window.location.reload(), 1500);
        throw new Error("CSRF token expired");
    }
    return res;
}