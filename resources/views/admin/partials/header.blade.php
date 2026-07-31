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
                    <button type="button" class="blue" data-bs-dismiss="modal" aria-label="Close"> Stay Logged In
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>
