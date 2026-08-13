const PAGE_KEYS = JSON.parse(
    document.getElementById("pageKeysData").textContent || "{}",
);

const API = "/api/admin/menus";
const CSRF_TOKEN =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

let menuModal,
    confirmDeleteModal,
    confirmRemoveItemModal,
    deleteSuccessModal,
    saveSuccessModal;
let isEditMode = false;
let pendingDeleteId = null;
let pendingRemoveItemEl = null;

let allMenus = [];
let filteredMenus = [];
let currentPage = 1;
let perPage = 10;
let itemIdCounter = 0;

const DEPTH_COLORS = ["#0d6efd", "#198754", "#fd7e14", "#6f42c1", "#d63384"];
const DEPTH_LABELS = ["Top Level", "Level 2", "Level 3", "Level 4", "Level 5+"];

// ── Boot ─────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    menuModal = new bootstrap.Modal(document.getElementById("menuModal"));
    confirmDeleteModal = new bootstrap.Modal(
        document.getElementById("confirmDeleteModal"),
    );
    confirmRemoveItemModal = new bootstrap.Modal(
        document.getElementById("confirmRemoveItemModal"),
    );
    deleteSuccessModal = new bootstrap.Modal(
        document.getElementById("deleteSuccessModal"),
    );
    saveSuccessModal = new bootstrap.Modal(
        document.getElementById("saveSuccessModal"),
    );

    perPage = parseInt(document.getElementById("perPage").value, 10);
    loadMenus();

    document
        .getElementById("menuForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            saveMenu();
        });

    document
        .getElementById("confirmDeleteBtn")
        .addEventListener("click", function () {
            if (pendingDeleteId === null) return;
            confirmDeleteModal.hide();
            executeDelete(pendingDeleteId);
            pendingDeleteId = null;
        });

    document
        .getElementById("confirmRemoveItemBtn")
        .addEventListener("click", function () {
            confirmRemoveItemModal.hide();
            executeRemoveTreeItem();
        });

    document
        .getElementById("saveSuccessOkBtn")
        .addEventListener("click", function () {
            saveSuccessModal.hide();
        });
});

// ── Headers ──────────────────────────────────────────────────────
function jsonHeaders() {
    return {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": CSRF_TOKEN,
    };
}

// ── Load menus ───────────────────────────────────────────────────
function loadMenus() {
    fetch(API, {
        credentials: "include",
        headers: {
            Accept: "application/json",
        },
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            allMenus = data.data || [];
            applyFilterAndRender();
        })
        .catch((err) => {
            console.error("Load menus failed:", err.message);
            document.getElementById("menuTable").innerHTML =
                `<tr><td colspan="4" class="text-center text-danger py-4">Failed to load menus.</td></tr>`;
        });
}

function countItems(items) {
    if (!items || !items.length) return 0;
    return items.reduce((sum, i) => sum + 1 + countItems(i.children), 0);
}

// ── Search + Pagination ──────────────────────────────────────────
function onSearchInput() {
    currentPage = 1;
    applyFilterAndRender();
}

function onPerPageChange() {
    perPage = parseInt(document.getElementById("perPage").value, 10);
    currentPage = 1;
    applyFilterAndRender();
}

function applyFilterAndRender() {
    const query = document
        .getElementById("searchInput")
        .value.trim()
        .toLowerCase();
    filteredMenus = query
        ? allMenus.filter((m) => m.name.toLowerCase().includes(query))
        : allMenus;
    renderTable();
    renderPagination();
}

function renderTable() {
    const tbody = document.getElementById("menuTable");

    if (filteredMenus.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No menus found.</td></tr>`;
        document.getElementById("entriesInfo").textContent =
            "Showing 0 to 0 of 0 entries";
        return;
    }

    const totalPages = Math.max(1, Math.ceil(filteredMenus.length / perPage));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * perPage;
    const pageItems = filteredMenus.slice(start, start + perPage);
    const locPill = {
        topbar: "edit-btn-topbar",
        header: "edit-btn-header",
        footer: "edit-btn-footer",
    };

    let rows = "";
    pageItems.forEach((menu) => {
        rows += `
                <tr>
                    <td>${escapeHtml(menu.name)}</td>
                    <td><span class="edit-btn ${locPill[menu.location] || "edit-btn-footer"}">${menu.location}</span></td>
                    <td><span class="green-btn">${countItems(menu.items)} items</span></td>
                    <td>
                        <div class="d-flex">
                            <button class="edit-btn" onclick="openEditModal(${menu.id})">Edit</button>
                            <button class="delete-btn" onclick="deleteMenu(${menu.id}, '${escapeHtml(menu.name).replace(/'/g, "\\'")}')">Delete</button>
                        </div>
                    </td>
                </tr>`;
    });

    tbody.innerHTML = rows;
    document.getElementById("entriesInfo").textContent =
        `Showing ${start + 1} to ${Math.min(start + perPage, filteredMenus.length)} of ${filteredMenus.length} entries`;
}

function renderPagination() {
    const totalPages = Math.max(1, Math.ceil(filteredMenus.length / perPage));
    const ul = document.getElementById("paginationControls");
    let html = "";

    html += `<li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">Previous</a>
                 </li>`;

    for (let p = 1; p <= totalPages; p++) {
        html += `<li class="page-item ${p === currentPage ? "active" : ""}">
                        <a class="page-link" href="#" onclick="goToPage(${p}); return false;">${p}</a>
                     </li>`;
    }

    html += `<li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">Next</a>
                 </li>`;

    ul.innerHTML = html;
}

function goToPage(page) {
    const totalPages = Math.max(1, Math.ceil(filteredMenus.length / perPage));
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
    renderPagination();
}

// ── Location toggle ──────────────────────────────────────────────
function onLocationChange() {
    const isHeader = document.getElementById("location").value === "header";
    document.getElementById("nestedHint").style.display = isHeader
        ? "block"
        : "none";
    document.querySelectorAll(".btn-add-child").forEach((btn) => {
        btn.style.display = isHeader ? "inline-block" : "none";
    });
}

function isHeaderMode() {
    return document.getElementById("location").value === "header";
}

// ── Page-visibility picker (new) ──────────────────────────────────
// Builds the "Everywhere / Only on / Hide on" control for one tree item.
// Storage model on the item element itself:
//   el.dataset.visMode = 'everywhere' | 'show' | 'hide'
//   el.dataset.visPages = JSON array of selected page keys
function buildVisibilityBlock(data) {
    const showOn = Array.isArray(data.show_on_pages) ? data.show_on_pages : [];
    const hideOn = Array.isArray(data.hide_on_pages) ? data.hide_on_pages : [];

    let mode = "everywhere";
    let selected = [];
    if (showOn.length) {
        mode = "show";
        selected = showOn;
    } else if (hideOn.length) {
        mode = "hide";
        selected = hideOn;
    }

    const wrap = document.createElement("div");
    wrap.className = "tree-item-visibility";
    wrap.dataset.visMode = mode;
    wrap.dataset.visPages = JSON.stringify(selected);

    const modeToggle = document.createElement("div");
    modeToggle.className = "vis-mode-toggle";
    modeToggle.innerHTML = `
            <button type="button" class="vis-mode-btn" data-mode="everywhere">Everywhere</button>
            <button type="button" class="vis-mode-btn" data-mode="show">Only on…</button>
            <button type="button" class="vis-mode-btn" data-mode="hide">Hide on…</button>
        `;
    wrap.appendChild(modeToggle);

    const label = document.createElement("span");
    label.className = "vis-label";
    label.textContent = "Page visibility";
    wrap.insertBefore(label, modeToggle);

    const chipRow = document.createElement("div");
    chipRow.className = "vis-chip-row";
    chipRow.style.display = "flex";
    chipRow.style.flexWrap = "wrap";
    chipRow.style.gap = "6px";
    wrap.appendChild(chipRow);

    function renderChips() {
        chipRow.innerHTML = "";
        const currentMode = wrap.dataset.visMode;
        if (currentMode === "everywhere") {
            chipRow.style.display = "none";
            return;
        }
        chipRow.style.display = "flex";
        const currentSelected = JSON.parse(wrap.dataset.visPages || "[]");

        Object.entries(PAGE_KEYS).forEach(([key, labelText]) => {
            const chip = document.createElement("span");
            chip.className =
                "vis-chip " +
                (currentMode === "show" ? "show-mode" : "hide-mode");
            chip.textContent = labelText;
            chip.dataset.key = key;
            if (currentSelected.includes(key)) chip.classList.add("active");

            chip.addEventListener("click", () => {
                let sel = JSON.parse(wrap.dataset.visPages || "[]");
                if (sel.includes(key)) {
                    sel = sel.filter((k) => k !== key);
                } else {
                    sel.push(key);
                }
                wrap.dataset.visPages = JSON.stringify(sel);
                renderChips();
            });

            chipRow.appendChild(chip);
        });
    }

    modeToggle.querySelectorAll(".vis-mode-btn").forEach((btn) => {
        if (btn.dataset.mode === mode) btn.classList.add("active");
        btn.addEventListener("click", () => {
            modeToggle
                .querySelectorAll(".vis-mode-btn")
                .forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");
            wrap.dataset.visMode = btn.dataset.mode;
            if (btn.dataset.mode === "everywhere") {
                wrap.dataset.visPages = "[]";
            }
            renderChips();
        });
    });

    renderChips();
    return wrap;
}

function getVisibilityPayload(itemEl) {
    const visWrap = itemEl.querySelector(".tree-item-visibility");
    if (!visWrap)
        return {
            show_on_pages: [],
            hide_on_pages: [],
        };

    const mode = visWrap.dataset.visMode;
    const pages = JSON.parse(visWrap.dataset.visPages || "[]");

    return {
        show_on_pages: mode === "show" ? pages : [],
        hide_on_pages: mode === "hide" ? pages : [],
    };
}

// ── Tree builder ─────────────────────────────────────────────────
function createTreeItem(data, depth) {
    data = data || {};
    depth = depth || 0;

    const id = ++itemIdCounter;
    const color = DEPTH_COLORS[Math.min(depth, 4)];
    const label = DEPTH_LABELS[Math.min(depth, 4)];
    const showChild = isHeaderMode() ? "inline-block" : "none";

    const div = document.createElement("div");
    div.className = "tree-item depth-" + Math.min(depth, 4);
    div.dataset.id = id;
    div.dataset.depth = depth;

    div.innerHTML = `
            <div class="tree-item-header" data-header>
                <span class="depth-badge" style="background:${color}">${label}</span>
                <div class="tree-item-fields">
                    <input class="form-control form-control-sm item-label" style="min-width:110px;flex:2"
                           placeholder="Label *" value="${escAttr(data.label || "")}">
                    <input class="form-control form-control-sm item-url" style="min-width:130px;flex:2"
                           placeholder="URL (e.g. /page)" value="${escAttr(data.url || "")}">
                    <input class="form-control form-control-sm item-order" type="number"
                           style="width:64px" placeholder="Order" value="${data.order ?? 0}">
                    <select class="form-select form-select-sm item-target" style="width:100px">
                        <option value="_self"  ${(data.target || "_self") === "_self" ? "selected" : ""}>Self</option>
                        <option value="_blank" ${data.target === "_blank" ? "selected" : ""}>New Tab</option>
                    </select>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input item-active" type="checkbox" role="switch"
                               ${data.is_active !== false ? "checked" : ""}>
                        <label class="form-check-label small">Active</label>
                    </div>
                </div>
                <div class="d-flex">
                    <button type="button" class="green-btn btn-add-child" style="display:${showChild}" onclick="addChildItem(this)">+ Child</button>
                    <button type="button" class="delete-btn" onclick="removeTreeItem(this)">Remove</button>
                </div>
            </div>
            <div class="tree-item-children d-none" data-children></div>`;

    // Insert the page-visibility picker into the header, after the fields.
    const header = div.querySelector("[data-header]");
    header.appendChild(buildVisibilityBlock(data));

    if (data.children && data.children.length) {
        const childWrap = div.querySelector("[data-children]");
        childWrap.classList.remove("d-none");
        data.children.forEach((child) =>
            childWrap.appendChild(createTreeItem(child, depth + 1)),
        );
    }

    return div;
}

function escAttr(s) {
    return String(s)
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function addTopLevelItem(data) {
    document
        .getElementById("itemsTree")
        .appendChild(createTreeItem(data || {}, 0));
    toggleNoItemsMsg();
}

function addChildItem(btn) {
    if (!isHeaderMode()) return;
    const parentItem = btn.closest(".tree-item");
    const depth = parseInt(parentItem.dataset.depth) + 1;
    const childWrap = parentItem.querySelector("[data-children]");
    childWrap.classList.remove("d-none");
    childWrap.appendChild(createTreeItem({}, depth));
    toggleNoItemsMsg();
}

// Clicking "Remove" no longer deletes immediately — it opens a confirm modal.
function removeTreeItem(btn) {
    const item = btn.closest(".tree-item");
    if (!item) return;

    pendingRemoveItemEl = item;

    const labelInput = item.querySelector(".item-label");
    const label = (labelInput && labelInput.value.trim()) || "this item";
    document.getElementById("removeItemLabel").textContent = label;

    const childWrap = item.querySelector("[data-children]");
    const childCount = childWrap
        ? childWrap.querySelectorAll(".tree-item").length
        : 0;
    document.getElementById("removeItemWarning").textContent =
        childCount > 0
            ? `This will also remove ${childCount} child item${childCount > 1 ? "s" : ""}.`
            : "This action cannot be undone.";

    confirmRemoveItemModal.show();
}

// Runs only after "Yes, Remove" is clicked in the confirm modal.
function executeRemoveTreeItem() {
    if (!pendingRemoveItemEl) return;

    const item = pendingRemoveItemEl;
    const parent = item.parentElement;
    item.remove();

    if (
        parent &&
        parent.hasAttribute("data-children") &&
        parent.children.length === 0
    ) {
        parent.classList.add("d-none");
    }

    pendingRemoveItemEl = null;
    toggleNoItemsMsg();
}

function toggleNoItemsMsg() {
    const tree = document.getElementById("itemsTree");
    document.getElementById("noItemsMsg").style.display =
        tree.children.length === 0 ? "block" : "none";
}

// ── Collect tree ─────────────────────────────────────────────────
function collectFromContainer(container) {
    const items = [];
    Array.from(container.children).forEach((el) => {
        if (!el.classList.contains("tree-item")) return;
        const label = el.querySelector(".item-label").value.trim();
        const url = el.querySelector(".item-url").value.trim();
        if (!label && !url) return;
        const childWrap = el.querySelector("[data-children]");
        const children =
            childWrap && !childWrap.classList.contains("d-none")
                ? collectFromContainer(childWrap)
                : [];
        const visibility = getVisibilityPayload(el);
        items.push({
            label,
            url,
            order: parseInt(el.querySelector(".item-order").value) || 0,
            target: el.querySelector(".item-target").value,
            is_active: el.querySelector(".item-active").checked,
            show_on_pages: visibility.show_on_pages,
            hide_on_pages: visibility.hide_on_pages,
            children,
        });
    });
    return items;
}

function collectItems() {
    return collectFromContainer(document.getElementById("itemsTree"));
}

// ── Modal open ───────────────────────────────────────────────────
function openCreateModal() {
    isEditMode = false;
    document.getElementById("menuForm").reset();
    document.getElementById("menuId").value = "";
    document.getElementById("is_active").checked = true;
    document.getElementById("menuModalTitle").textContent = "Add Menu";
    document.getElementById("itemsTree").innerHTML = "";
    itemIdCounter = 0;
    clearFieldErrors();
    onLocationChange();
    toggleNoItemsMsg();
    menuModal.show();
}

function openEditModal(menuId) {
    const menu = allMenus.find((m) => m.id === menuId);
    if (!menu) {
        showToast("Menu not found.", "danger");
        return;
    }

    isEditMode = true;
    document.getElementById("menuId").value = menu.id;
    document.getElementById("name").value = menu.name;
    document.getElementById("location").value = menu.location;
    document.getElementById("is_active").checked = !!menu.is_active;
    document.getElementById("menuModalTitle").textContent = "Edit Menu";
    document.getElementById("itemsTree").innerHTML = "";
    itemIdCounter = 0;
    clearFieldErrors();
    onLocationChange();
    (menu.items || []).forEach((item) => addTopLevelItem(item));
    toggleNoItemsMsg();
    menuModal.show();
}

// ── Save ─────────────────────────────────────────────────────────
function saveMenu() {
    const id = document.getElementById("menuId").value;
    const payload = {
        name: document.getElementById("name").value,
        location: document.getElementById("location").value,
        is_active: document.getElementById("is_active").checked ? 1 : 0,
        items: collectItems(),
    };

    const url = isEditMode ? `${API}/${id}` : API;
    const method = isEditMode ? "PUT" : "POST";

    const saveBtn = document.getElementById("saveBtn");
    saveBtn.disabled = true;
    saveBtn.textContent = "Saving...";

    fetch(url, {
        method,
        credentials: "include",
        headers: jsonHeaders(),
        body: JSON.stringify(payload),
    })
        .then(handleAuthErrors)
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    showFieldErrors(data.errors);
                    throw new Error("Validation failed");
                }
                throw new Error(data.message || "Save failed");
            }
            return data;
        })
        .then((data) => {
            loadMenus();
            menuModal.hide();

            document.getElementById("saveSuccessTitle").textContent = isEditMode
                ? "Updated Successfully"
                : "Saved Successfully";
            document.getElementById("saveSuccessMsg").textContent =
                data.message ||
                (isEditMode
                    ? "Menu updated successfully."
                    : "Menu created successfully.");
            saveSuccessModal.show();
        })
        .catch((err) => {
            if (err.message !== "Validation failed") {
                showToast(err.message, "danger");
            }
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = "Save";
        });
}

// ── Delete (whole menu, from the list) ───────────────────────────
function deleteMenu(id, name) {
    pendingDeleteId = id;
    document.getElementById("deleteMenuName").textContent = name;
    confirmDeleteModal.show();
}

function executeDelete(id) {
    fetch(`${API}/${id}`, {
        method: "DELETE",
        credentials: "include",
        headers: jsonHeaders(),
    })
        .then(handleAuthErrors)
        .then((res) => res.json())
        .then((data) => {
            loadMenus();

            document.getElementById("deleteSuccessMsg").textContent =
                data.message || "The menu has been deleted.";
            deleteSuccessModal.show();
        })
        .catch((err) => showToast(err.message || "Delete failed.", "danger"));
}

// ── Auth errors ──────────────────────────────────────────────────
function handleAuthErrors(res) {
    if (res.status === 401) {
        window.location.href = "/login";
        throw new Error("Not authenticated");
    }
    if (res.status === 419) {
        showToast("Session expired. Reloading...", "warning");
        setTimeout(() => window.location.reload(), 1500);
        throw new Error("CSRF token expired");
    }
    return res;
}

// ── Toast ────────────────────────────────────────────────────────
function showToast(message, type = "success") {
    const toast = document.getElementById("appToast");
    const icon = document.getElementById("toastIcon");
    const titleEl = document.getElementById("toastTitle");
    const messageEl = document.getElementById("toastMessage");

    toast.classList.remove("toast-success", "toast-danger", "toast-warning");
    toast.classList.add(`toast-${type}`);

    const config = {
        success: {
            icon: "bi-check-circle-fill",
            title: "Success",
        },
        danger: {
            icon: "bi-x-circle-fill",
            title: "Error",
        },
        warning: {
            icon: "bi-exclamation-circle-fill",
            title: "Warning",
        },
    };
    const cfg = config[type] || config.success;

    icon.className = `bi ${cfg.icon} fs-5`;
    titleEl.textContent = cfg.title;
    messageEl.textContent = message;

    const bar = document.getElementById("toastProgressBar");
    bar.style.display = "none";
    void bar.offsetWidth;
    bar.style.display = "";

    new bootstrap.Toast(toast, {
        delay: 4000,
    }).show();
}

// ── Field errors ─────────────────────────────────────────────────
function showFieldErrors(errors) {
    clearFieldErrors();
    Object.keys(errors).forEach((field) => {
        const input = document.getElementById(field);
        const errorEl = document.getElementById(`${field}Error`);
        if (input) input.classList.add("is-invalid");
        if (errorEl) errorEl.textContent = errors[field][0];
    });
}

function clearFieldErrors() {
    ["name", "location"].forEach((field) => {
        const input = document.getElementById(field);
        const errorEl = document.getElementById(`${field}Error`);
        if (input) input.classList.remove("is-invalid");
        if (errorEl) errorEl.textContent = "";
    });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
}
