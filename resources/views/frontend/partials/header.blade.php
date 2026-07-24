<header class="header">

    {{-- Logo --}}
    <div class="logo">
        @if ($logoIcon)
            <img src="{{ $logoIcon->url }}" alt="{{ $settings->website_name ?? 'Logo' }}">
        @endif
    </div>

    {{-- Hamburger --}}
    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    {{-- Navbar --}}
    <div class="navbar_custom" id="navbar">

        <div id="header-menu" class="nav-links">
            @php
                $currentPath = trim(request()->path(), '/'); // e.g. "about", "" for home
            @endphp

            @foreach ($menuItems as $item)
                @php
                    $itemPath = trim(parse_url($item->url, PHP_URL_PATH) ?? $item->url, '/');
                @endphp

                @if ($item->children->isEmpty())
                    <a href="{{ $item->url }}" target="{{ $item->target ?? '_self' }}"
                        class="{{ $currentPath === $itemPath ? 'active' : '' }}">
                        {{ $item->label }}
                    </a>
                @else
                    @php
                        $hasActiveChild = $item->children->contains(function ($child) use ($currentPath) {
                            $childPath = trim(parse_url($child->url, PHP_URL_PATH) ?? $child->url, '/');
                            return $childPath === $currentPath;
                        });
                    @endphp
                    <div class="nav-dropdown">
                        <a href="{{ $item->url }}" target="{{ $item->target ?? '_self' }}"
                            class="{{ $currentPath === $itemPath || $hasActiveChild ? 'active' : '' }}">
                            {{ $item->label }} ▾
                        </a>
                        <div class="nav-dropdown-menu">
                            @foreach ($item->children as $child)
                                @php
                                    $childPath = trim(parse_url($child->url, PHP_URL_PATH) ?? $child->url, '/');
                                @endphp
                                <a href="{{ $child->url }}" target="{{ $child->target ?? '_self' }}"
                                    class="{{ $currentPath === $childPath ? 'active' : '' }}">
                                    {{ $child->label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Search --}}
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search.....">
            <button class="search-btn" id="searchBtn">
                <img src="{{ asset('/storage/home_page/search_icon.png') }}" alt="Search">
            </button>
        </div>

        {{-- Login / Register --}}
        <a href="#" class="login-btn" id="btn-login" data-has-login-errors="{{ $errors->any() ? '1' : '0' }}">
            <img src="{{ asset('/storage/home_page/login_icon.png') }}" alt="Login">
            Login / Register
        </a>

    </div>

</header>

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

                    {{-- FORGOT PASSWORD FORM (OTP based, 3-step, AJAX) --}}
                    <div class="xfw-form-panel random_student_form">

                        <div id="otpAlert" class="alert py-2 small d-none"></div>

                        {{-- Step 1: enter email, request OTP --}}
                        <div id="otpStepEmail">
                            <label>Registered Email</label>
                            <input type="email" id="otpEmail" placeholder="Enter Email" />
                            <button type="button" class="btn-login-submit" id="btnSendOtp">Send OTP</button>
                        </div>

                        {{-- Step 2: enter + verify OTP only --}}
                        <div id="otpStepVerify" style="display:none;">
                            <label>Enter OTP</label>
                            <input type="text" id="otpCode" inputmode="numeric" maxlength="6"
                                placeholder="6-digit OTP" />

                            <button type="button" class="btn-login-submit" id="btnVerifyOtp">Verify OTP</button>

                            <p class="register-link">
                                Didn't get the code?
                                <a id="btnResendOtp" style="cursor:pointer; color:#002f6c;">Resend OTP</a>
                            </p>
                        </div>

                        {{-- Step 3: OTP verified, now set new password --}}
                        <div id="otpStepReset" style="display:none;">
                            <label>New Password</label>
                            <input type="password" id="otpNewPassword" placeholder="Enter New Password" />

                            <label>Confirm New Password</label>
                            <input type="password" id="otpNewPasswordConfirm" placeholder="Confirm New Password" />

                            <button type="button" class="btn-login-submit" id="btnResetWithOtp">Reset
                                Password</button>
                        </div>

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

        // ── Forgot Password via OTP (3-step) ────────────────────────────
        // NOTE: requires <meta name="csrf-token" content="{{ csrf_token() }}">
        // in the page <head> (main layout), since this form uses fetch()
        // instead of a normal POST form submission.
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const otpEmailInput = document.getElementById("otpEmail");
        const otpCodeInput = document.getElementById("otpCode");
        const otpNewPassword = document.getElementById("otpNewPassword");
        const otpNewPasswordConfirm = document.getElementById("otpNewPasswordConfirm");

        const otpStepEmail = document.getElementById("otpStepEmail");
        const otpStepVerify = document.getElementById("otpStepVerify");
        const otpStepReset = document.getElementById("otpStepReset");

        const otpAlert = document.getElementById("otpAlert");
        const btnSendOtp = document.getElementById("btnSendOtp");
        const btnResendOtp = document.getElementById("btnResendOtp");
        const btnVerifyOtp = document.getElementById("btnVerifyOtp");
        const btnResetWithOtp = document.getElementById("btnResetWithOtp");

        let otpEmailForReset = "";
        let otpVerifiedCode = ""; // holds the OTP once verified, needed again by resetWithOtp
        let resendCooldown = 0;
        let resendTimer = null;

        function showOtpAlert(type, msg) {
            otpAlert.textContent = msg;
            otpAlert.className = "alert py-2 small " + (type === "success" ? "alert-success" : "alert-danger");
            otpAlert.classList.remove("d-none");
        }

        function hideOtpAlert() {
            otpAlert.classList.add("d-none");
        }

        function resetOtpFlow() {
            otpStepReset.style.display = "none";
            otpStepVerify.style.display = "none";
            otpStepEmail.style.display = "block";
            otpEmailInput.value = "";
            otpCodeInput.value = "";
            otpNewPassword.value = "";
            otpNewPasswordConfirm.value = "";
            otpVerifiedCode = "";
            hideOtpAlert();
        }

        function startResendCooldown() {
            resendCooldown = 60;
            btnResendOtp.style.pointerEvents = "none";
            btnResendOtp.style.opacity = "0.5";
            clearInterval(resendTimer);
            btnResendOtp.textContent = `Resend OTP (${resendCooldown}s)`;

            resendTimer = setInterval(() => {
                resendCooldown--;
                if (resendCooldown <= 0) {
                    clearInterval(resendTimer);
                    btnResendOtp.textContent = "Resend OTP";
                    btnResendOtp.style.pointerEvents = "auto";
                    btnResendOtp.style.opacity = "1";
                } else {
                    btnResendOtp.textContent = `Resend OTP (${resendCooldown}s)`;
                }
            }, 1000);
        }

        // Step 1: request an OTP
        async function requestOtp(email, btn) {
            if (!email) {
                showOtpAlert("error", "Please enter your registered email.");
                return;
            }

            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = "Sending…";

            try {
                const res = await fetch("{{ route('password.send-otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": CSRF_TOKEN,
                    },
                    body: JSON.stringify({
                        email
                    }),
                });
                const json = await res.json();

                if (!res.ok || !json.status) {
                    showOtpAlert("error", json.message || "Failed to send OTP.");
                    return;
                }

                otpEmailForReset = email;
                showOtpAlert("success", json.message || "OTP sent to your email.");

                otpStepEmail.style.display = "none";
                otpStepVerify.style.display = "block";
                startResendCooldown();
            } catch (e) {
                showOtpAlert("error", "Network error. Please try again.");
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }

        btnSendOtp?.addEventListener("click", () => {
            hideOtpAlert();
            requestOtp(otpEmailInput.value.trim(), btnSendOtp);
        });

        btnResendOtp?.addEventListener("click", () => {
            if (resendCooldown > 0) return;
            hideOtpAlert();
            requestOtp(otpEmailForReset, btnResendOtp);
        });

        // Step 2: verify the OTP before showing password fields
        btnVerifyOtp?.addEventListener("click", async () => {
            hideOtpAlert();

            const otp = otpCodeInput.value.trim();

            if (!otp || otp.length !== 6) {
                showOtpAlert("error", "Please enter the 6-digit OTP.");
                return;
            }

            btnVerifyOtp.disabled = true;
            const originalText = btnVerifyOtp.textContent;
            btnVerifyOtp.textContent = "Verifying…";

            try {
                const res = await fetch("{{ route('password.verify-otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": CSRF_TOKEN,
                    },
                    body: JSON.stringify({
                        email: otpEmailForReset,
                        otp,
                    }),
                });
                const json = await res.json();

                if (!res.ok || !json.status) {
                    showOtpAlert("error", json.message || "Incorrect OTP. Please try again.");
                    return;
                }

                otpVerifiedCode = otp;
                showOtpAlert("success", json.message || "OTP verified. Please set a new password.");

                otpStepVerify.style.display = "none";
                otpStepReset.style.display = "block";
            } catch (e) {
                showOtpAlert("error", "Network error. Please try again.");
            } finally {
                btnVerifyOtp.disabled = false;
                btnVerifyOtp.textContent = originalText;
            }
        });

        // Step 3: set the new password (OTP already verified)
        btnResetWithOtp?.addEventListener("click", async () => {
            hideOtpAlert();

            const password = otpNewPassword.value;
            const passwordConfirm = otpNewPasswordConfirm.value;

            if (!password || password.length < 8) {
                showOtpAlert("error", "Password must be at least 8 characters.");
                return;
            }
            if (password !== passwordConfirm) {
                showOtpAlert("error", "Passwords do not match.");
                return;
            }

            btnResetWithOtp.disabled = true;
            const originalText = btnResetWithOtp.textContent;
            btnResetWithOtp.textContent = "Resetting…";

            try {
                const res = await fetch("{{ route('password.reset-otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": CSRF_TOKEN,
                    },
                    body: JSON.stringify({
                        email: otpEmailForReset,
                        otp: otpVerifiedCode,
                        password,
                        password_confirmation: passwordConfirm,
                    }),
                });
                const json = await res.json();

                if (!res.ok || !json.status) {
                    showOtpAlert("error", json.message || "Failed to reset password.");
                    return;
                }

                showOtpAlert("success", json.message ||
                    "Password reset successfully. Please login.");

                setTimeout(() => {
                    resetOtpFlow();

                    // switch back to the Login tab
                    const loginTabBtn = loginPopup.querySelector(
                        '.login_tab_btn[data-tab="student"]');
                    loginTabBtn?.click();
                }, 1500);
            } catch (e) {
                showOtpAlert("error", "Network error. Please try again.");
            } finally {
                btnResetWithOtp.disabled = false;
                btnResetWithOtp.textContent = originalText;
            }
        });

    });
</script>
