document.addEventListener("DOMContentLoaded", function () {
    let currentPage = 1;
    let deleteId = null;

    // ─── Show Toast ──────────────────────────────────────────────────────────
    function showToast(message, type = "success") {
        const toastEl = document.getElementById("flash-toast");
        const icon = document.getElementById("toast-icon");
        const msg = document.getElementById("toast-message");

        toastEl.classList.remove("bg-success", "bg-danger", "d-none");
        toastEl.classList.add(type === "success" ? "bg-success" : "bg-danger");
        icon.className =
            type === "success"
                ? "bi bi-check-circle-fill fs-5"
                : "bi bi-exclamation-triangle-fill fs-5";
        msg.innerText = message;

        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    // ─── Fetch & Render Announcements ────────────────────────────────────────
    function loadAnnouncements(page = 1) {
        currentPage = page;
        const tbody = document.getElementById("announcements-table-body");
        tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </td>
                </tr>`;

        fetch(`/api/admin/announcements?page=${page}`, {
            headers: {
                Accept: "application/json",
            },
            credentials: "include",
        })
            .then((res) => res.json())
            .then((res) => {
                if (!res.status) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load announcements.</td></tr>`;
                    return;
                }

                const data = res.data;

                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5" style="font-size:14px;">
                                    No announcements found.
                                </td>
                            </tr>`;
                    document.getElementById("pagination-container").innerHTML =
                        "";
                    return;
                }

                tbody.innerHTML = data.data
                    .map(
                        (a) => `
                        <tr>
                            <td>${a.id}</td>
                            <td title="${escAttr(a.name)}">${escHtmlOut(a.name)}</td>
                            <td>
                                ${
                                    a.link
                                        ? `<a href="${escAttr(a.link)}" target="_blank" class="edit-btn">Open link</a>`
                                        : "<span>—</span>"
                                }
                            </td>
                            <td>${a.sequence}</td>
                            <td>
                                ${
                                    a.attachment
                                        ? `<a href="/storage/${a.attachment}" target="_blank" class="edit-btn"> View file</a>`
                                        : "<span>—</span>"
                                }
                            </td>
                            <td>${formatDate(a.created_at)}</td>
                            <td>
                                <div class="an-actions-cell">
                                    <button class="edit-btn"
                                        onclick="openEdit(${a.id}, '${escHtml(a.name)}', '${escHtml(a.link || "")}', ${a.sequence}, '${a.attachment || ""}')">
                                        Edit
                                    </button>
                                    <button class="delete-btn"
                                        onclick="openDelete(${a.id}, '${escHtml(a.name)}')">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `,
                    )
                    .join("");

                renderPagination(data);
            })
            .catch(() => {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Something went wrong.</td></tr>`;
            });
    }

    // ─── Pagination ──────────────────────────────────────────────────────────
    function renderPagination(data) {
        const container = document.getElementById("pagination-container");
        if (data.last_page <= 1) {
            container.innerHTML = "";
            return;
        }

        let html = '<ul class="pagination">';
        for (let i = 1; i <= data.last_page; i++) {
            html += `<li class="page-item ${i === data.current_page ? "active" : ""}">
                    <button class="page-link" onclick="loadAnnouncementsPage(${i})">${i}</button>
                    </li>`;
        }
        html += "</ul>";
        container.innerHTML = html;
    }

    // expose for pagination
    window.loadAnnouncementsPage = function (page) {
        loadAnnouncements(page);
    };

    // ─── Helpers ─────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/\\/g, "\\\\")
            .replace(/'/g, "\\'")
            .replace(/"/g, "&quot;");
    }

    function escHtmlOut(str) {
        return String(str ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }

    function escAttr(str) {
        return String(str ?? "").replace(/"/g, "&quot;");
    }

    function formatDate(dateStr) {
        if (!dateStr) return "—";
        const d = new Date(dateStr);
        return d.toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    // ─── Mutual exclusion: link ↔ attachment ─────────────────────────────────
    function setLinkDisabled(
        linkEl,
        linkWrapper,
        fileEl,
        fileWrapper,
        disabled,
    ) {
        linkEl.disabled = disabled;
        linkWrapper.style.opacity = disabled ? "0.45" : "1";
        if (disabled) linkEl.value = "";
    }

    function setFileDisabled(
        fileEl,
        fileWrapper,
        linkEl,
        linkWrapper,
        disabled,
    ) {
        fileEl.disabled = disabled;
        fileWrapper.style.opacity = disabled ? "0.45" : "1";
        if (disabled) fileEl.value = "";
    }

    function setupMutualExclusion(linkEl, linkWrapper, fileEl, fileWrapper) {
        linkEl.addEventListener("input", function () {
            const hasLink = this.value.trim() !== "";
            setFileDisabled(fileEl, fileWrapper, linkEl, linkWrapper, hasLink);
        });
        fileEl.addEventListener("change", function () {
            const hasFile = this.files && this.files.length > 0;
            setLinkDisabled(linkEl, linkWrapper, fileEl, fileWrapper, hasFile);
        });
    }

    setupMutualExclusion(
        document.getElementById("add_link"),
        document.getElementById("add_link_wrapper"),
        document.getElementById("add_attachment"),
        document.getElementById("add_attachment_wrapper"),
    );

    setupMutualExclusion(
        document.getElementById("edit_link"),
        document.getElementById("edit_link_wrapper"),
        document.getElementById("edit_attachment"),
        document.getElementById("edit_attachment_wrapper"),
    );

    // ─── Reset Add modal on close ────────────────────────────────────────────
    document
        .getElementById("addAnnouncementModal")
        .addEventListener("hidden.bs.modal", function () {
            document.getElementById("addForm").reset();
            document.getElementById("add-spinner").classList.add("d-none");
            document.getElementById("add_link").disabled = false;
            document.getElementById("add_attachment").disabled = false;
            document.getElementById("add_link_wrapper").style.opacity = "1";
            document.getElementById("add_attachment_wrapper").style.opacity =
                "1";
        });

    // ─── Reset Edit modal on close ───────────────────────────────────────────
    document
        .getElementById("editAnnouncementModal")
        .addEventListener("hidden.bs.modal", function () {
            document.getElementById("editForm").reset();
            document.getElementById("edit-spinner").classList.add("d-none");
            document.getElementById("edit_link").disabled = false;
            document.getElementById("edit_attachment").disabled = false;
            document.getElementById("edit_link_wrapper").style.opacity = "1";
            document.getElementById("edit_attachment_wrapper").style.opacity =
                "1";
            document
                .getElementById("edit_current_attachment")
                .classList.add("d-none");
        });

    // ─── Open Edit Modal ─────────────────────────────────────────────────────
    window.openEdit = function (id, name, link, sequence, attachment) {
        document.getElementById("edit_id").value = id;
        document.getElementById("edit_name").value = name;
        document.getElementById("edit_link").value = link;
        document.getElementById("edit_sequence").value = sequence;

        // Reset disabled states first
        document.getElementById("edit_link").disabled = false;
        document.getElementById("edit_attachment").disabled = false;
        document.getElementById("edit_link_wrapper").style.opacity = "1";
        document.getElementById("edit_attachment_wrapper").style.opacity = "1";

        // Show current attachment if exists
        const currentAttachDiv = document.getElementById(
            "edit_current_attachment",
        );
        const currentAttachLink = document.getElementById(
            "edit_attachment_link",
        );
        if (attachment) {
            currentAttachLink.href = "/storage/" + attachment;
            currentAttachDiv.classList.remove("d-none");
        } else {
            currentAttachDiv.classList.add("d-none");
        }

        // If link exists, disable attachment input — but NOT the link field
        // FIX: only disable attachment when link is present, never both at same time
        if (link && link.trim() !== "") {
            document.getElementById("edit_attachment").disabled = true;
            document.getElementById("edit_attachment_wrapper").style.opacity =
                "0.45";
        } else if (attachment) {
            // attachment exists but no link — disable link field
            document.getElementById("edit_link").disabled = true;
            document.getElementById("edit_link_wrapper").style.opacity = "0.45";
        }

        new bootstrap.Modal(
            document.getElementById("editAnnouncementModal"),
        ).show();
    };

    // ─── Open Delete Modal ───────────────────────────────────────────────────
    window.openDelete = function (id, name) {
        deleteId = id;
        document.getElementById("announcement_name").innerText = name;
        new bootstrap.Modal(
            document.getElementById("deleteAnnouncementModal"),
        ).show();
    };

    // ─── ADD: Submit ─────────────────────────────────────────────────────────
    document.getElementById("addForm").addEventListener("submit", function (e) {
        e.preventDefault();
        const spinner = document.getElementById("add-spinner");
        spinner.classList.remove("d-none");

        const formData = new FormData(this);

        fetch("/api/admin/announcements", {
            method: "POST",
            body: formData,
            credentials: "include",
            headers: {
                Accept: "application/json",
            },
        })
            .then((res) => res.json())
            .then((res) => {
                spinner.classList.add("d-none");
                if (res.status) {
                    bootstrap.Modal.getInstance(
                        document.getElementById("addAnnouncementModal"),
                    ).hide();
                    showToast(res.message, "success");
                    loadAnnouncements(currentPage);
                } else {
                    const errors = res.errors
                        ? Object.values(res.errors).flat().join(" ")
                        : res.message;
                    showToast(errors, "error");
                }
            })
            .catch(() => {
                spinner.classList.add("d-none");
                showToast("Something went wrong. Please try again.", "error");
            });
    });

    // ─── EDIT: Submit ────────────────────────────────────────────────────────
    document
        .getElementById("editForm")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            const spinner = document.getElementById("edit-spinner");
            spinner.classList.remove("d-none");

            const id = document.getElementById("edit_id").value;
            const formData = new FormData(this);

            fetch(`/api/admin/announcements/${id}`, {
                method: "POST",
                body: formData,
                credentials: "include",
                headers: {
                    Accept: "application/json",
                },
            })
                .then((res) => res.json())
                .then((res) => {
                    spinner.classList.add("d-none");
                    if (res.status) {
                        bootstrap.Modal.getInstance(
                            document.getElementById("editAnnouncementModal"),
                        ).hide();
                        showToast(res.message, "success");
                        loadAnnouncements(currentPage);
                    } else {
                        const errors = res.errors
                            ? Object.values(res.errors).flat().join(" ")
                            : res.message;
                        showToast(errors, "error");
                    }
                })
                .catch(() => {
                    spinner.classList.add("d-none");
                    showToast(
                        "Something went wrong. Please try again.",
                        "error",
                    );
                });
        });

    // ─── DELETE: Confirm ─────────────────────────────────────────────────────
    // Using POST + _method=DELETE instead of actual DELETE
    // because DELETE requests can sometimes drop cookies/credentials
    document
        .getElementById("confirmDeleteBtn")
        .addEventListener("click", function () {
            if (!deleteId) return;

            const formData = new FormData();
            formData.append("_method", "DELETE");

            fetch(`/api/admin/announcements/${deleteId}`, {
                method: "POST",
                body: formData,
                credentials: "include",
                headers: {
                    Accept: "application/json",
                },
            })
                .then((res) => res.json())
                .then((res) => {
                    bootstrap.Modal.getInstance(
                        document.getElementById("deleteAnnouncementModal"),
                    ).hide();
                    if (res.status) {
                        showToast(res.message, "success");
                        loadAnnouncements(currentPage);
                    } else {
                        showToast(res.message, "error");
                    }
                })
                .catch(() => {
                    showToast(
                        "Something went wrong. Please try again.",
                        "error",
                    );
                });
        });

    // ─── Initial Load ────────────────────────────────────────────────────────
    loadAnnouncements();
});