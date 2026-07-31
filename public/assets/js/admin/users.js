const API_BASE = "/api/admin/users";
const META_API = "/api/admin/users/meta";

let currentPage = 1;
let currentSearch = "";
let currentPerPage = 10;
let searchTimer = null;
let allRoles = [];

async function authFetch(url, options = {}) {
    options.credentials = "same-origin";
    options.headers = {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
        ...options.headers,
    };
    return fetch(url, options);
}

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

function setFieldError(id, message) {
    const input = document.getElementById(id);
    const error = document.getElementById(id + "Error");
    if (input) input.classList.add("is-invalid");
    if (error) error.textContent = message;
}

function clearAllErrors(...ids) {
    ids.forEach((id) => {
        const input = document.getElementById(id);
        const error = document.getElementById(id + "Error");
        if (input) input.classList.remove("is-invalid");
        if (error) error.textContent = "";
    });
}

function getChecked(containerId) {
    return [
        ...document.querySelectorAll(
            `#${containerId} input[type=checkbox]:checked`,
        ),
    ].map((cb) => parseInt(cb.value));
}

async function fetchMeta() {
    if (allRoles.length) return;
    try {
        const res = await authFetch(META_API);
        const json = await res.json();
        if (json.status) allRoles = json.roles;
    } catch (err) {
        console.error("Failed to fetch meta", err);
    }
}

function renderRoleCheckboxes(containerId, checkedIds = []) {
    const box = document.getElementById(containerId);
    if (!allRoles.length) {
        box.innerHTML = `<div class="col-12 text-muted text-center py-2">No roles available.</div>`;
        return;
    }
    box.innerHTML = allRoles
        .map(
            (role) => `
                <div class="col-md-3 mb-1">
                    <div class="form-check">
                        <input class="form-check-input role-checkbox" type="checkbox"
                            value="${role.id}" id="${containerId}_${role.id}"
                            data-container="${containerId}"
                            ${checkedIds.includes(role.id) ? "checked" : ""}>
                        <label class="form-check-label" for="${containerId}_${role.id}">${role.name}</label>
                    </div>
                </div>
            `,
        )
        .join("");

    if (containerId === "editRolesBox") {
        box.querySelectorAll(".role-checkbox").forEach((cb) => {
            cb.addEventListener("change", updatePermissionsPreview);
        });
        updatePermissionsPreview();
    }
}

function updatePermissionsPreview() {
    const checkedRoleIds = getChecked("editRolesBox");
    const preview = document.getElementById("rolePermsPreview");
    const wrap = document.getElementById("rolePermsPreviewWrap");

    const permsMap = {};
    allRoles
        .filter((r) => checkedRoleIds.includes(r.id))
        .forEach((r) =>
            (r.permissions || []).forEach((p) => (permsMap[p.id] = p.name)),
        );

    const perms = Object.values(permsMap);

    if (!perms.length) {
        wrap.style.display = "none";
        return;
    }

    wrap.style.display = "block";
    preview.innerHTML = perms
        .map((name) => `<span class="green-btn">${name}</span>`)
        .join("");
}

async function loadUsers(page = 1) {
    currentPage = page;

    const params = new URLSearchParams({
        page: currentPage,
        per_page: currentPerPage,
    });
    if (currentSearch.trim()) params.append("search", currentSearch.trim());

    const tbody = document.getElementById("usersTableBody");
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Loading…</td></tr>`;

    try {
        const res = await authFetch(`${API_BASE}?${params}`);
        const json = await res.json();

        if (!json.status || !json.data.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>`;
            document.getElementById("paginationInfo").textContent =
                "Showing 0 to 0 of 0 entries";
            document.getElementById("paginationLinks").innerHTML = "";
            return;
        }

        const { current_page, last_page, per_page, total } = json.meta;
        const from = (current_page - 1) * per_page + 1;
        const to = Math.min(current_page * per_page, total);

        tbody.innerHTML = json.data
            .map((user, index) => {
                const roleBadges = user.roles.length
                    ? user.roles
                          .map((r) => `<span class="edit-btn">${r.name}</span>`)
                          .join("")
                    : `<span class="adm-pill-muted">No roles</span>`;

                const permsMap = {};
                user.roles.forEach((r) =>
                    (r.permissions || []).forEach(
                        (p) => (permsMap[p.id] = p.name),
                    ),
                );
                const permBadges = Object.values(permsMap).length
                    ? Object.values(permsMap)
                          .map((n) => `<span class="green-btn">${n}</span>`)
                          .join("")
                    : `<span class="adm-pill-muted">None</span>`;

                const roleIds = user.roles.map((r) => r.id);

                return `
                    <tr>
                        <td>${(currentPage - 1) * currentPerPage + index + 1}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td >${roleBadges}</td>
                        <td style="text-wrap-mode: wrap;">${permBadges}</td>
                        <td>
                            <div class="d-flex">
                                <button class="edit-btn" onclick="openEditModal(${user.id}, '${user.name.replace(/'/g, "\\'")}', [${roleIds}])">Edit</button>
                                <button class="delete-btn" onclick="openDeleteModal(${user.id}, '${user.name.replace(/'/g, "\\'")}')">Delete</button>
                            </div>
                        </td>
                    </tr>
                `;
            })
            .join("");

        document.getElementById("paginationInfo").textContent =
            `Showing ${from}–${to} of ${total} users`;
        renderPagination(current_page, last_page);
    } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load users.</td></tr>`;
        showToast("Failed to load users.", "danger");
    }
}

function renderPagination(current, last) {
    const ul = document.getElementById("paginationLinks");
    ul.innerHTML = "";
    if (last <= 1) return;

    const makeItem = (label, page, disabled = false, active = false) => {
        const li = document.createElement("li");
        li.className = `page-item${disabled ? " disabled" : ""}${active ? " active" : ""}`;
        const a = document.createElement("a");
        a.className = "page-link";
        a.href = "#";
        a.innerHTML = label;
        if (!disabled && !active) {
            a.addEventListener("click", (e) => {
                e.preventDefault();
                loadUsers(page);
            });
        }
        li.appendChild(a);
        ul.appendChild(li);
    };

    makeItem("&laquo;", current - 1, current === 1);
    const pages = new Set();
    [1, last, current - 1, current, current + 1].forEach((p) => {
        if (p >= 1 && p <= last) pages.add(p);
    });
    let prev = null;
    [...pages]
        .sort((a, b) => a - b)
        .forEach((p) => {
            if (prev && p - prev > 1) makeItem("…", null, true);
            makeItem(p, p, false, p === current);
            prev = p;
        });
    makeItem("&raquo;", current + 1, current === last);
}

document.getElementById("searchInput").addEventListener("input", (e) => {
    clearTimeout(searchTimer);
    currentSearch = e.target.value;
    searchTimer = setTimeout(() => loadUsers(1), 400);
});
document.getElementById("clearSearch").addEventListener("click", () => {
    document.getElementById("searchInput").value = "";
    currentSearch = "";
    loadUsers(1);
});

document.getElementById("perPageSelect").addEventListener("change", (e) => {
    currentPerPage = parseInt(e.target.value);
    loadUsers(1);
});

document
    .getElementById("openCreateModalBtn")
    .addEventListener("click", async () => {
        await fetchMeta();
        clearAllErrors("createName", "createEmail", "createPassword");
        [
            "createName",
            "createEmail",
            "createPassword",
            "createPasswordConfirm",
        ].forEach((id) => (document.getElementById(id).value = ""));
        renderRoleCheckboxes("createRolesBox", []);
        new bootstrap.Modal(document.getElementById("createUserModal")).show();
    });

document.getElementById("createUserBtn").addEventListener("click", async () => {
    clearAllErrors("createName", "createEmail", "createPassword");

    const btn = document.getElementById("createUserBtn");
    const btnText = document.getElementById("createBtnText");
    const spinner = document.getElementById("createBtnSpinner");

    btn.disabled = true;
    btnText.textContent = "Creating...";
    spinner.classList.remove("d-none");

    try {
        const res = await authFetch(API_BASE, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                name: document.getElementById("createName").value,
                email: document.getElementById("createEmail").value,
                password: document.getElementById("createPassword").value,
                password_confirmation: document.getElementById(
                    "createPasswordConfirm",
                ).value,
                roles: getChecked("createRolesBox"),
            }),
        });
        const json = await res.json();

        if (!json.status) {
            if (json.errors) {
                const map = {
                    name: "createName",
                    email: "createEmail",
                    password: "createPassword",
                };
                Object.entries(json.errors).forEach(([field, msgs]) => {
                    if (map[field]) setFieldError(map[field], msgs[0]);
                });
            } else {
                showToast(json.message, "danger");
            }
            return;
        }

        bootstrap.Modal.getInstance(
            document.getElementById("createUserModal"),
        ).hide();
        showToast(json.message, "success");
        loadUsers(1);
    } catch (err) {
        console.error(err);
        showToast("Something went wrong.", "danger");
    } finally {
        btn.disabled = false;
        btnText.textContent = "Create User";
        spinner.classList.add("d-none");
    }
});

async function openEditModal(id, name, roleIds) {
    await fetchMeta();
    document.getElementById("editUserId").value = id;
    document.getElementById("editUserName").textContent = name;
    document.getElementById("rolePermsPreviewWrap").style.display = "none";
    renderRoleCheckboxes("editRolesBox", roleIds);
    new bootstrap.Modal(document.getElementById("editUserModal")).show();
}

document.getElementById("editUserBtn").addEventListener("click", async () => {
    const id = document.getElementById("editUserId").value;
    const btn = document.getElementById("editUserBtn");
    const btnText = document.getElementById("editBtnText");
    const spinner = document.getElementById("editBtnSpinner");

    btn.disabled = true;
    btnText.textContent = "Saving...";
    spinner.classList.remove("d-none");

    try {
        const res = await authFetch(`${API_BASE}/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                roles: getChecked("editRolesBox"),
            }),
        });
        const json = await res.json();

        if (!json.status) {
            showToast(json.message, "danger");
            return;
        }

        bootstrap.Modal.getInstance(
            document.getElementById("editUserModal"),
        ).hide();
        showToast(json.message, "success");
        loadUsers(currentPage);
    } catch (err) {
        console.error(err);
        showToast("Something went wrong.", "danger");
    } finally {
        btn.disabled = false;
        btnText.textContent = "Save Changes";
        spinner.classList.add("d-none");
    }
});

function openDeleteModal(id, name) {
    document.getElementById("deleteUserId").value = id;
    document.getElementById("deleteUserName").textContent = name;

    const errBox = document.getElementById("deleteModalError");
    errBox.classList.add("d-none");
    document.getElementById("deleteModalErrorText").textContent = "";

    new bootstrap.Modal(document.getElementById("deleteUserModal")).show();
}

document.getElementById("deleteUserBtn").addEventListener("click", async () => {
    const id = document.getElementById("deleteUserId").value;
    const errBox = document.getElementById("deleteModalError");
    const errText = document.getElementById("deleteModalErrorText");
    const btn = document.getElementById("deleteUserBtn");
    const btnText = document.getElementById("deleteBtnText");
    const spinner = document.getElementById("deleteBtnSpinner");

    errBox.classList.add("d-none");
    btn.disabled = true;
    btnText.innerHTML = "Deleting...";
    spinner.classList.remove("d-none");

    try {
        const res = await authFetch(`${API_BASE}/${id}`, {
            method: "DELETE",
        });
        const json = await res.json();

        if (!json.status) {
            errText.textContent = json.message;
            errBox.classList.remove("d-none");
            return;
        }

        bootstrap.Modal.getInstance(
            document.getElementById("deleteUserModal"),
        ).hide();
        showToast(json.message, "success");
        loadUsers(currentPage > 1 ? currentPage - 1 : 1);
    } catch (err) {
        console.error(err);
        errText.textContent = "Something went wrong. Please try again.";
        errBox.classList.remove("d-none");
    } finally {
        btn.disabled = false;
        btnText.innerHTML = '<i class="bi bi-trash3 me-1"></i>Yes, Delete';
        spinner.classList.add("d-none");
    }
});

loadUsers();
