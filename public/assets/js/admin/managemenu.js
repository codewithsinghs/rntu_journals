const PAGE_KEYS = JSON.parse(
    document.getElementById("pageKeysData").textContent || "{}",
);

const API = "/api/admin/menus";
const CSRF_TOKEN =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

const configEl = document.getElementById("manageMenuConfig");
const MENU_ID = parseInt(configEl?.dataset.menuId || "0", 10);
const MENUS_LIST_URL = configEl?.dataset.menusListUrl || "/admin/menus";
const ITEM_FORM_URL_BASE = configEl?.dataset.itemFormUrlBase || "";

let confirmDeleteModal, saveSuccessModal;
let currentMenu = null;

let pendingDeletePath = null;

const LEVEL_SHADES = ["#e6e6e6", "#eeeeee", "#f5f5f5", "#fafafa"];

// ── Boot ─────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    confirmDeleteModal = new bootstrap.Modal(
        document.getElementById("confirmDeleteModal"),
    );
    saveSuccessModal = new bootstrap.Modal(
        document.getElementById("saveSuccessModal"),
    );

    loadMenu();

    document
        .getElementById("menuForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            saveMenu();
        });

    document
        .getElementById("confirmDeleteBtn")
        .addEventListener("click", function () {
            confirmDeleteModal.hide();
            executeDeleteItem();
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

// ── Load the single menu being managed ────────────────────────────
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

            populateForm(currentMenu);
            document.getElementById("pageLoader").classList.add("d-none");
            document.getElementById("menuForm").classList.remove("d-none");
        })
        .catch((err) => {
            console.error("Load menu failed:", err.message);
            document.getElementById("pageLoader").innerHTML =
                '<div class="text-center text-danger py-4">Failed to load menu.</div>';
        });
}

function populateForm(menu) {
    document.getElementById("manageMenuHeading").textContent =
        "Manage Menu — " + menu.name;
    document.getElementById("name").value = menu.name;
    document.getElementById("location").value = menu.location;
    document.getElementById("is_active").value = menu.is_active ? "1" : "0";
    renderItemsTree();
}

function renderItemsTree() {
    const container = document.getElementById("itemsTree");
    container.innerHTML = "";
    (currentMenu.items || []).forEach((item, idx) => {
        container.appendChild(renderItemRow(item, [idx]));
    });
    toggleNoItemsMsg();
}

function renderItemRow(item, path) {
    const depth = path.length - 1;
    const pathStr = path.join("-");
    const bg = LEVEL_SHADES[Math.min(depth, LEVEL_SHADES.length - 1)];
    const hasChildren = Array.isArray(item.children) && item.children.length > 0;

    const div = document.createElement("div");
    div.className = "tree-item";
    div.dataset.path = pathStr;
    div.draggable = true;

    div.innerHTML = `
        <div class="tree-item-header d-flex align-items-center justify-content-between" style="background:${bg}">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-grip-vertical tree-drag-handle" aria-hidden="true"></i>
                ${
                    hasChildren
                        ? `<button type="button" class="tree-toggle-btn" data-toggle-btn aria-label="Toggle">
                             <i class="bi bi-dash-circle-fill"></i>
                           </button>`
                        : `<span class="tree-toggle-spacer"></span>`
                }
                <span class="tree-item-label">${escapeHtml(item.label || "(no label)")}</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="javascript:void(0)" class="tree-edit-link" data-edit-link>Edit Menu</a>
                <a href="javascript:void(0)" class="tree-delete-link" data-delete-link aria-label="Delete item">
                Delete
                </a>
            </div>
        </div>
        <div class="tree-item-children" data-children></div>`;

    const childWrap = div.querySelector("[data-children]");
    (item.children || []).forEach((child, cIdx) => {
        childWrap.appendChild(renderItemRow(child, [...path, cIdx]));
    });

    const toggleBtn = div.querySelector(":scope > .tree-item-header [data-toggle-btn]");
    if (toggleBtn) {
        toggleBtn.addEventListener("mousedown", (e) => e.stopPropagation());
        toggleBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleChildren(toggleBtn);
        });
    }

    const editLink = div.querySelector(":scope > .tree-item-header [data-edit-link]");
    editLink.addEventListener("mousedown", (e) => e.stopPropagation());
    editLink.addEventListener("click", (e) => {
        e.stopPropagation();
        goToItemForm(pathStr);
    });

    const deleteLink = div.querySelector(":scope > .tree-item-header [data-delete-link]");
    deleteLink.addEventListener("mousedown", (e) => e.stopPropagation());
    deleteLink.addEventListener("click", (e) => {
        e.stopPropagation();
        deleteItem(pathStr);
    });

    div.addEventListener("dragstart", function (e) {
        if (e.target.closest("[data-toggle-btn], [data-edit-link], [data-delete-link]")) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }
        handleDragStart.call(this, e);
    });
    div.addEventListener("dragover", handleDragOver);
    div.addEventListener("dragleave", handleDragLeave);
    div.addEventListener("drop", handleDrop);
    div.addEventListener("dragend", handleDragEnd);

    return div;
}

function toggleChildren(btn) {
    const treeItem = btn.closest(".tree-item");
    const childWrap = treeItem.querySelector(":scope > [data-children]");
    const icon = btn.querySelector("i");
    const isCollapsed = childWrap.style.display === "none";

    childWrap.style.display = isCollapsed ? "" : "none";
    icon.className = isCollapsed
        ? "bi bi-dash-circle-fill"
        : "bi bi-plus-circle-fill";
}

// ── Drag and drop reordering / moving (any level, no restriction) ──
let draggedPath = null;

function handleDragStart(e) {
    e.stopPropagation();
    draggedPath = this.dataset.path;
    this.classList.add("dragging");
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/plain", draggedPath);
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    if (this.dataset.path === draggedPath) return;
    e.dataTransfer.dropEffect = "move";
    this.classList.add("drag-over");
}

function handleDragLeave(e) {
    e.stopPropagation();
    this.classList.remove("drag-over");
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove("drag-over");

    const targetPath = this.dataset.path;
    if (!draggedPath || targetPath === draggedPath) return;

    const fromPath = draggedPath.split("-").map(Number);
    const toPath = targetPath.split("-").map(Number);

    const updatedItems = moveItem(currentMenu.items || [], fromPath, toPath);
    if (!updatedItems) return;

    persistItems(updatedItems);
}

function handleDragEnd() {
    document
        .querySelectorAll("#itemsTree .dragging, #itemsTree .drag-over")
        .forEach((el) => el.classList.remove("dragging", "drag-over"));
    draggedPath = null;
}

function getSiblingsArray(items, path) {
    if (path.length === 1) return items;
    let list = items;
    for (let i = 0; i < path.length - 1; i++) {
        list = list[path[i]].children || [];
    }
    return list;
}

function getItemByPath(items, path) {
    let list = items;
    let node = null;
    for (let i = 0; i < path.length; i++) {
        node = list[path[i]];
        if (!node) return null;
        list = node.children || [];
    }
    return node;
}

function isSameOrDescendantPath(maybePath, ancestorPath) {
    if (maybePath.length < ancestorPath.length) return false;
    for (let i = 0; i < ancestorPath.length; i++) {
        if (maybePath[i] !== ancestorPath[i]) return false;
    }
    return true;
}

function moveItem(items, fromPath, toPath) {
    if (isSameOrDescendantPath(toPath, fromPath)) return null;

    const clone = JSON.parse(JSON.stringify(items));
    const fromSiblings = getSiblingsArray(clone, fromPath);
    const fromIdx = fromPath[fromPath.length - 1];
    const toSiblings = getSiblingsArray(clone, toPath);
    let toIdx = toPath[toPath.length - 1];

    const [moved] = fromSiblings.splice(fromIdx, 1);

    if (fromSiblings === toSiblings && toIdx > fromIdx) {
        toIdx -= 1;
    }

    toSiblings.splice(toIdx, 0, moved);

    return clone;
}

// ── Persist item-tree changes immediately (used by drag-and-drop & delete) ─
function persistItems(items) {
    fetch(`${API}/${MENU_ID}`, {
        method: "PUT",
        credentials: "include",
        headers: jsonHeaders(),
        body: JSON.stringify({
            name: document.getElementById("name").value,
            location: document.getElementById("location").value,
            is_active: document.getElementById("is_active").value === "1" ? 1 : 0,
            items,
        }),
    })
        .then(handleAuthErrors)
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || "Update failed");
            return data;
        })
        .then(() => {
            showToast("Menu updated.", "success");
            loadMenu();
        })
        .catch((err) => showToast(err.message || "Update failed.", "danger"));
}

function toggleNoItemsMsg() {
    const tree = document.getElementById("itemsTree");
    document.getElementById("noItemsMsg").style.display =
        tree.children.length === 0 ? "block" : "none";
}

// ── Navigate to the item add/edit page ───────────────────────────
function goToItemForm(editPath) {
    if (!ITEM_FORM_URL_BASE) return;
    const qs = editPath ? `?path=${encodeURIComponent(editPath)}` : "";
    window.location.href = `${ITEM_FORM_URL_BASE}${qs}`;
}

// ── Delete a single menu item (and any of its sub-items) ──────────
function deleteItem(pathStr) {
    const path = pathStr.split("-").map(Number);
    const item = getItemByPath(currentMenu.items || [], path);
    if (!item) return;

    pendingDeletePath = pathStr;
    document.getElementById("deleteMenuName").textContent =
        item.label || "this item";
    confirmDeleteModal.show();
}

function executeDeleteItem() {
    if (!pendingDeletePath) return;

    const path = pendingDeletePath.split("-").map(Number);
    const clone = JSON.parse(JSON.stringify(currentMenu.items || []));
    const siblings = getSiblingsArray(clone, path);
    const idx = path[path.length - 1];

    siblings.splice(idx, 1);
    pendingDeletePath = null;

    persistItems(clone);
}

// ── Save (Name / Active only — items are managed via the tree above) ─
function saveMenu() {
    const payload = {
        name: document.getElementById("name").value,
        location: document.getElementById("location").value,
        is_active: document.getElementById("is_active").value === "1" ? 1 : 0,
        items: currentMenu ? currentMenu.items || [] : [],
    };

    const saveBtn = document.getElementById("saveBtn");
    saveBtn.disabled = true;
    saveBtn.textContent = "Saving...";

    fetch(`${API}/${MENU_ID}`, {
        method: "PUT",
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
            document.getElementById("saveSuccessTitle").textContent =
                "Updated Successfully";
            document.getElementById("saveSuccessMsg").textContent =
                data.message || "Menu updated successfully.";
            saveSuccessModal.show();
        })
        .catch((err) => {
            if (err.message !== "Validation failed") {
                showToast(err.message, "danger");
            }
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = "Save Menu";
        });
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
    ["name"].forEach((field) => {
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