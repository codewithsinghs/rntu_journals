// document.addEventListener('DOMContentLoaded', function () {

//     // ── Generic dropdown toggle (kept from your existing sidebar.js) ──────
//     const dropdownBtn = document.querySelector('.dropdown-btn');
//     const dropdownContent = document.querySelector('.dropdown-content');

//     if (dropdownBtn) {
//         dropdownBtn.addEventListener('click', function () {
//             dropdownContent.classList.toggle('show');
//         });
//     }

//     const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
//     const logoutPopupEl = document.getElementById('logoutPopup');
//     const logoutPopup = logoutPopupEl ? bootstrap.Modal.getOrCreateInstance(logoutPopupEl) : null;

//     confirmLogoutBtn?.addEventListener('click', function () {
//         confirmLogoutBtn.disabled = true;
//         confirmLogoutBtn.textContent = 'Logging out...';
//         logoutUser();
//     });

//     function logoutUser() {
//         const token = document.querySelector('meta[name="csrf-token"]')?.content;

//         fetch("/logout", {
//             method: "POST",
//             credentials: "include",
//             headers: {
//                 "Accept": "application/json",
//                 "X-CSRF-TOKEN": token,
//                 "X-Requested-With": "XMLHttpRequest",
//             },
//         })
//         .then(async (res) => {
//             if (!res.ok && res.status !== 401) {
//                 const text = await res.text();
//                 throw new Error(text || "Logout failed");
//             }
//         })
//         .catch((error) => {
//             console.error("Logout error:", error.message);
//         })
//         .finally(() => {
//             logoutPopup?.hide();
//             window.location.href = "/login";
//         });
//     }

// });