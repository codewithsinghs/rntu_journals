<!-- Header -->
<header class="topbar">
  <span class="toggle_p">
    <button id="toggle-btn">&#9776;</button>
    <span class="search_icon">
      <input type="text" class="search-bar" placeholder="Search..." />
      <i class="fa-solid fa-magnifying-glass" style="position: absolute;top: 16px;right: 14px;"></i>
    </span>
  </span>

  <div class="topbar-right">

    <div class="user-section">
      <span class="user-initial">
        {{ auth('api')->check() ? strtoupper(substr(auth('api')->user()->name, 0, 1)) : 'G' }}
      </span>
    </div>

    <div class="dropdown">
      <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        @auth('api')
  {{ auth('api')->user()->name }}
@else
  Guest
@endauth
      </button>
      <ul class="dropdown-menu custom-style-ul">
        <li><a class="dropdown-item custom-style-li" href="notification.html">Notification</a></li>
        <li><a class="dropdown-item custom-style-li" data-bs-toggle="modal" data-bs-target="#logoutPopup">Log Out</a></li>
      </ul>
    </div>

  </div>
</header>


<!-- Logout Popup -->
<div class="modal fade" id="logoutPopup" tabindex="-1" aria-labelledby="logoutPopupLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <div class="top">
          <div class="pop-title-remove">Confirm Logout</div>
        </div>

        <div class="middle-content">
          <span>
            Do you really want to log out? <br>
            Make sure your work is saved before leaving.
          </span>
        </div>

        <div class="bottom-btn">
          <button type="button" id="confirmLogoutBtn" class="red"> Logout </button>
          <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Stay Logged In </button>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- Drop Down -->
<script>
  document.querySelectorAll(".dropdown-btn").forEach(button => {
    button.addEventListener("click", function () {

      const parent = this.parentElement;

      document.querySelectorAll(".dropdown-menu-item").forEach(item => {
        if (item !== parent) {
          item.classList.remove("active");
        }
      });

      parent.classList.toggle("active");
    });
  });
</script>

<!-- Logout Handler -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
    const logoutPopupEl = document.getElementById('logoutPopup');
    const logoutPopup = logoutPopupEl ? bootstrap.Modal.getOrCreateInstance(logoutPopupEl) : null;

    confirmLogoutBtn?.addEventListener('click', function () {
      confirmLogoutBtn.disabled = true;
      confirmLogoutBtn.textContent = 'Logging out...';
      logoutUser();
    });

    function logoutUser() {
      const token = document.querySelector('meta[name="csrf-token"]')?.content;

      fetch("{{ route('admin.logout') }}", {
        method: "POST",
        credentials: "include",
        headers: {
          "Accept": "application/json",
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
</script>