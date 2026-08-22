<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-header">
        <button id="toggle-btn-small-screen">&#9776;</button>
        <a href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('images/dashboard/logo.png') }}" alt="Logo">
        </a>
        @php
        $can = fn(string $perm) => in_array($perm, $authUserPerms ?? []);
        @endphp
    </div>

    <input type="text" placeholder="Search" class="search-box" id="sidebarSearch" />

    <!-- Journal Management -->
    <p>Journal Management</p>

    <ul class="nav">

        <!-- Dashboard -->
        <li>
            <a href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-column"></i>
                Dashboard
            </a>
        </li>

        <!-- All Article List -->
        @if ($can('view submit article'))
        <li>
            <a href="{{ route('admin.submit-article') }}"
                class="{{ request()->routeIs('admin.submit-article*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-alt"></i>
                All Article Lists
            </a>
        </li>
        @endif

        <!-- Journal Management -->
        @if ($can('view journals'))
        <li>
            <a href="{{ route('admin.journals') }}"
                class="{{ request()->routeIs('admin.journals*') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i>
                Journal Management
            </a>
        </li>
        @endif

        <!-- Volume Management -->
        @if ($can('view volumes'))
        <li>
            <a href="{{ route('admin.volume') }}" class="{{ request()->routeIs('admin.volume*') ? 'active' : '' }}">
                <i class="fa-solid fa-book-open"></i>
                Volume Management
            </a>
        </li>
        @endif

        <!-- Issues Management -->
        @if ($can('view issues'))
        <li>
            <a href="{{ route('admin.issue') }}" class="{{ request()->routeIs('admin.issue*') ? 'active' : '' }}">
                <i class="fa-solid fa-newspaper"></i>
                Issue Management
            </a>
        </li>
        @endif

        <!-- Article Management -->
        @if ($can('view'))
        <li>
            <a href="{{ route('admin.issue') }}" class="{{ request()->routeIs('admin.issue*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-alt"></i>
                Article Management
            </a>
        </li>
        @endif

        <!-- Review Management -->
        @if ($can('view'))
        <li>
            <a href="{{ route('admin.issue') }}" class="{{ request()->routeIs('admin.issue*') ? 'active' : '' }}">
                <i class="fa-solid fa-star"></i>
                Review Management
            </a>
        </li>
        @endif

    </ul>

    <!-- CMS Management -->
    <p>CMS Management</p>

    <ul class="nav">

        <!-- Navbar -->
        @if ($can('view menus'))
        <li>
            <a href="{{ route('admin.menus') }}" class="{{ request()->routeIs('admin.menus') ? 'active' : '' }}">
                <i class="fa-solid fa-bars"></i>
                Navbar
            </a>
        </li>
        @endif

        <!-- Home Page -->
        @if ($can('view home content'))
        <li>
            <a href="{{ route('admin.homebasiccontent') }}"
                class="{{ request()->routeIs('admin.homebasiccontent*') ? 'active' : '' }}">
                <i class="fa-solid fa-home"></i>
                Home Page
            </a>
        </li>
        @endif

        <!-- Announcements -->
        @if ($can('view announcements'))
        <li>
            <a href="{{ route('admin.announcements') }}"
                class="{{ request()->routeIs('admin.announcements*') ? 'active' : '' }}">
                <i class="fa-solid fa-bullhorn"></i>
                Announcements
            </a>
        </li>
        @endif

        <!-- About Page -->
        @if ($can('view about'))
        <li>
            <a href="{{ route('admin.aboutcontent') }}"
                class="{{ request()->routeIs('admin.aboutcontent*') ? 'active' : '' }}">
                <i class="fa-solid fa-info-circle"></i>
                About Page
            </a>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       
        </li>
        @endif

        <!-- Editorial Board -->
        @if ($can('view editorial board'))
        <li>
            <a href="{{ route('admin.editorial-board') }}"
                class="{{ request()->routeIs('admin.editorial-board') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                Editorial Board
            </a>
        </li>
        @endif

        <!-- Editorial Board Role-->
        @if ($can('view editorial board roles'))
        <li>
            <a href="{{ route('admin.editorial-board-roles') }}"
                class="{{ request()->routeIs('admin.editorial-board-roles') ? 'active' : '' }}">
                <i class="fa-solid fa-user-tag"></i>
                Editorial Board Role
            </a>
        </li>
        @endif

        <!-- Contact Page -->
        @if ($can('view contacts'))
        <li>
            <a href="{{ route('admin.contact') }}"
                class="{{ request()->routeIs('admin.contact') ? 'active' : '' }}">
                <i class="fa-solid fa-address-book"></i>
                Contact Page
            </a>
        </li>
        @endif

        <!-- Guidelines Page -->
        @if ($can('view guidelines'))
        <li>
            <a href="{{ route('admin.guidelines') }}"
                class="{{ request()->routeIs('admin.guidelines*') ? 'active' : '' }}">
                <i class="fa-solid fa-bookmark"></i>
                Guidelines Page
            </a>
        </li>
        @endif

        <!-- Guidelines Page -->
        @if ($can('view prp'))
        <li>
            <a href="{{ route('admin.prp') }}"
                class="{{ request()->routeIs('admin.prp*') ? 'active' : '' }}">
                <i class="fa-solid fa-bookmark"></i>
                PRP Page
            </a>
        </li>
        @endif


        <!-- Media -->
        @if ($can('view medias'))
        <li>
            <a href="{{ route('admin.medias') }}"
                class="{{ request()->routeIs('admin.medias') ? 'active' : '' }}">
                <i class="fa-solid fa-images"></i>
                Media
            </a>
        </li>
        @endif

    </ul>

    <!-- User Management -->
    <p>User Management</p>

    <ul class="nav">

        <!-- Users Management -->
        @if ($can('view users'))
        <li>
            <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                Users Management
            </a>
        </li>
        @endif

        <!-- Roles Management -->
        @if ($can('view roles'))
        <li>
            <a href="{{ route('admin.roles') }}" class="{{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                <i class="fa-solid fa-briefcase"></i>
                Roles Management
            </a>
        </li>
        @endif

        <!-- Permissions Management-->
        @if ($can('view permissions'))
        <li>
            <a href="{{ route('admin.permissions') }}"
                class="{{ request()->routeIs('admin.permissions') ? 'active' : '' }}">
                <i class="fa-brands fa-creative-commons-by"></i>
                Permissions Management
            </a>
        </li>
        @endif

    </ul>

    <!-- Settings -->
    <p>Settings</p>

    <ul class="nav">
        <!-- Settings -->
        @if ($can('view settings'))
        <li>
            <a href="{{ route('admin.settings') }}"
                class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <i class="fa-solid fa-gears"></i>
                Settings
            </a>
        </li>
        @endif
    </ul>

</aside>