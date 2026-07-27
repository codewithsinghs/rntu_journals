// Header Page JS

// -- Logout Handler
document.addEventListener("DOMContentLoaded", function () {
    const confirmLogoutBtn = document.getElementById("confirmLogoutBtn");
    const logoutPopupEl = document.getElementById("logoutPopup");
    const logoutPopup = logoutPopupEl
        ? bootstrap.Modal.getOrCreateInstance(logoutPopupEl)
        : null;

    confirmLogoutBtn?.addEventListener("click", function () {
        confirmLogoutBtn.disabled = true;
        confirmLogoutBtn.textContent = "Logging out...";
        logoutUser();
    });

    function logoutUser() {
        const token = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;

        fetch("{{ route('admin.logout') }}", {
            method: "POST",
            credentials: "include",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": token,
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then(async (res) => {
                if (!res.ok && res.status !== 401) {
                    const text = await res.text();
                    throw new Error(text || "Logout failed");
                }
            })
            .catch((error) => {
                console.error("Logout error:", error.message);
            })
            .finally(() => {
                logoutPopup?.hide();
                window.location.href = "/login";
            });
    }
});


// Sidebar Page  JS

// -- Sidebar Handburgur
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggle-btn"); // topbar hamburger, if present
    const toggleBtnSmall = document.getElementById("toggle-btn-small-screen");

    function toggleSidebar() {
        sidebar.classList.toggle("collapsed");
    }

    toggleBtn?.addEventListener("click", toggleSidebar);
    toggleBtnSmall?.addEventListener("click", toggleSidebar);

    // Simple sidebar search filter
    const searchInput = document.getElementById("sidebarSearch");
    searchInput?.addEventListener("input", () => {
        const term = searchInput.value.toLowerCase();
        document.querySelectorAll(".nav > li").forEach((li) => {
            const text = li.textContent.toLowerCase();
            li.style.display = text.includes(term) ? "" : "none";
        });
    });
});
