    <!-- Nav Section -->
    <div style="background-color: #fff; padding: 10px 15px;">
        <div class="hero_section border_R10">

            <div class="hero_container nav_padding">

                <header class="header">

<<<<<<< HEAD:resources/views/frontend/partials/AllPageHeader.blade.php
                    <!-- Logo -->
                    <a href="/" class="logo">
                        @if ($logoIcon)
                            <img src="{{ $logoIcon->url }}" alt="{{ $settings->website_name ?? 'Logo' }}">
                        @endif
                    </a>

                    <!-- Hamburger -->
                    <div class="hamburger" id="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
=======
                    <div class="logo" id="siteLogo">
                    </div>

                    <div class="hamburger" id="hamburger">
                        <span></span><span></span><span></span>
>>>>>>> main:resources/views/frontend/partials/allpageheader.blade.php
                    </div>

                    <div class="navbar_custom" id="navbar">

                        <div id="header-menu" class="nav-links">
                        </div>

                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Search.....">
                            <button class="search-btn" id="searchBtn">
                                <img src="{{ asset('/storage/home_page/search_icon.png') }}" alt="Search">
                            </button>
                        </div>

                        <a href="#" class="login-btn" id="btn-login" data-has-login-errors="{{ $errors->any() ? '1' : '0' }}">
                            <img src="{{ asset('/storage/home_page/login_icon.png') }}" alt="Login">
                            Login / Register
                        </a>

                    </div>

                </header>
            </div>
        </div>
    </div>



    <div class="login-popup-overlay" id="loginPopup">
        <div class="login-popup">

            <div class="popup-left">
                <img src="{{ asset('storage/popup_img.png') }}" alt="Login" />
            </div>

            <div class="popup-right">
                <button class="close-btn popup-close">&times;</button>

                <div class="tab-buttons">
                    <button class="login_tab_btn active" data-tab="student">Login Here</button>
                    <button class="login_tab_btn" data-tab="staff">Forget Password</button>
                </div>

                <div class="random_wrapper_form-container">
                    <div class="random_wrapper_form">

                        {{-- LOGIN FORM --}}
                        <div class="xfw-form-panel random_staff_form random_active_form">
                            <form action="{{ route('login.submit') }}" method="POST">
                                @csrf

                                @if ($errors->any())
                                <div class="alert alert-danger py-2 small">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    placeholder="Enter Email" />

                                <label>Password</label>
                                <input type="password" name="password" placeholder="Enter Password" />

                                <button type="submit" class="btn-login-submit">Login Now</button>

                                <p class="register-link">
                                    Don't have an account?
                                    <a id="btn-register" style="cursor:pointer; color:#002f6c;">Register Here</a>
                                </p>
                            </form>
                        </div>

                        {{-- FORGOT PASSWORD FORM --}}
                        <div class="xfw-form-panel random_student_form">
                            <form action="{{ Route::has('password.email') ? route('password.email') : '#' }}"
                                method="POST">
                                @csrf
                                <label>Registered Email</label>
                                <input type="email" name="email" placeholder="Enter Email" />
                                <button type="submit" class="btn-login-submit">Send Reset Link</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="login-popup-overlay" id="registerPopup">
        <div class="login-popup">

            <div class="popup-left">
                <img src="{{ asset('/storage/popup_img.png') }}" alt="Register" />
            </div>

            <div class="popup-right">
                <button class="close-btn popup-close">&times;</button>

                <div class="tab-buttons">
                    <button class="login_tab_btn active" data-tab="staff">Register Here</button>
                </div>

                <div class="random_wrapper_form-container">
                    <div class="random_wrapper_form">
                        <div class="xfw-form-panel random_staff_form random_active_form">
                            <form action="{{ route('register.submit') }}" method="POST">
                                @csrf

                                <label>Full Name</label>
                                <input type="text" name="name" placeholder="Enter Name" />

                                <label>Mobile No.</label>
                                <input type="text" name="mobile" placeholder="Enter Number" />

                                <label>Email</label>
                                <input type="email" name="email" placeholder="Enter Email" />

                                <label>Create Password</label>
                                <input type="password" name="password" id="registerPassword"
                                    placeholder="Enter Password" />

                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" id="registerPasswordConfirm"
                                    placeholder="Confirm Password" />
                                <small id="passwordMatchMsg" style="display:none; color:#d62828;">Passwords do not
                                    match</small>

                                <button type="submit" class="btn-login-submit">Register Now</button>

                                <p class="register-link">
                                    Already have an account?
                                    <a id="btn-signin" style="cursor:pointer; color:#002f6c;">Sign In</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const loginBtn = document.getElementById("btn-login");
            const registerBtn = document.getElementById("btn-register");
            const signinBtn = document.getElementById("btn-signin");

            const loginPopup = document.getElementById("loginPopup");
            const registerPopup = document.getElementById("registerPopup");

            // Auto-open login popup if server redirected back with validation errors
            const hasLoginErrors = loginBtn?.dataset.hasLoginErrors === "1";
            if (hasLoginErrors) {
                loginPopup.style.display = "flex";
            }

            // Open Login popup
            loginBtn?.addEventListener("click", (e) => {
                e.preventDefault();
                loginPopup.style.display = "flex";
            });

            // Switch from Login popup -> Register popup
            registerBtn?.addEventListener("click", (e) => {
                e.preventDefault();
                loginPopup.style.display = "none";
                registerPopup.style.display = "flex";
            });

            // Switch from Register popup -> Login popup
            signinBtn?.addEventListener("click", (e) => {
                e.preventDefault();
                registerPopup.style.display = "none";
                loginPopup.style.display = "flex";
            });

            // Close buttons
            document.querySelectorAll(".popup-close").forEach(btn => {
                btn.addEventListener("click", () => {
                    btn.closest(".login-popup-overlay").style.display = "none";
                });
            });

            // Click outside popup box to close
            document.querySelectorAll(".login-popup-overlay").forEach(popup => {
                popup.addEventListener("click", (e) => {
                    if (e.target === popup) {
                        popup.style.display = "none";
                    }
                });
            });

            // Tab switching inside login popup (Login <-> Forgot Password)
            const tabBtns = loginPopup.querySelectorAll(".login_tab_btn");
            const formWrapper = loginPopup.querySelector(".random_wrapper_form");

            tabBtns.forEach(btn => {
                btn.addEventListener("click", () => {
                    tabBtns.forEach(tab => tab.classList.remove("active"));
                    btn.classList.add("active");

                    formWrapper.style.transform = btn.dataset.tab === "student" ?
                        "translateX(0)" :
                        "translateX(-50%)";
                });
            });

            // Confirm password match check (register form)
            const pass = document.getElementById("registerPassword");
            const confirmPass = document.getElementById("registerPasswordConfirm");
            const matchMsg = document.getElementById("passwordMatchMsg");
            const registerForm = registerPopup.querySelector("form");

            function checkPasswordsMatch() {
                if (confirmPass.value && pass.value !== confirmPass.value) {
                    matchMsg.style.display = "block";
                    return false;
                }
                matchMsg.style.display = "none";
                return true;
            }

            pass?.addEventListener("input", checkPasswordsMatch);
            confirmPass?.addEventListener("input", checkPasswordsMatch);

            registerForm?.addEventListener("submit", (e) => {
                if (!checkPasswordsMatch()) {
                    e.preventDefault();
                }
            });

        });
    </script>