<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/auth/bootstrap.min.css') }}">

    <!-- Style -->
    <link rel="stylesheet" href="{{ asset('assets/css/auth/login.css') }}">

    <title>RNTU Journal Login</title>
</head>

<body>

    <div class="d-lg-flex half">

        <a href="{{ url('/') }}">
            <img src="{{ asset('images/dashboard/logo.png') }}">
        </a>

        @php
            $loginBg = asset('images/dashboard/login.jpeg');
        @endphp
        <div class="bg order-1 order-md-2" style="background-image: url('{{ $loginBg }}');"></div>

        <div class="contents order-2 order-md-1">

            <div class="container">

                <div class="row align-items-center justify-content-center">

                    <div class="col-md-7">

                        <h3>Login to <strong>RNTU Journal CMS</strong></h3>
                        <p class="mb-4">Sign in to access the Journal Management System and manage journals, articles,
                            peer reviews, volumes, issues, and
                            publications securely.</p>

                        {{-- Success Message --}}
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show py-2 small">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        {{-- Error Message --}}
                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show py-2 small">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        {{-- Validation Errors --}}
                        @if($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('login.submit') }}" method="POST">
                            @csrf

                            <div class="form-group first">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="your-email@gmail.com">
                            </div>

                            <div class="form-group last mb-3">
                                <label>Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Your Password">
                            </div>

                            <div class="mb-4 text-center">
                                <a href="#" id="btnOpenForgotPassword" class="forgot-pass">Forgot Password</a>
                            </div>

                            <button type="submit" class="btn btn-block">Login</button>

                        </form>
                    </div>

                    <footer class="footer_login">
                        &copy; {{ date('Y') }} Rabindranath Tagore University (RNTU) Journal Management System. All Rights Reserved.
                    </footer>

                </div>

            </div>

        </div>

    </div>

    {{-- Forgot Password Modal (3-step OTP flow) --}}
    <div class="login-popup-overlay" id="forgotPasswordPopup" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="login-popup" style="background:#fff; border-radius:8px; padding:32px; width:100%; max-width:420px; position:relative;">

            <button type="button" class="close-btn" id="closeForgotPassword" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>

            <h4 class="mb-3">Reset Your Password</h4>

            <div id="otpAlert" class="alert py-2 small d-none"></div>

            {{-- Step 1: enter email, request OTP --}}
            <div id="otpStepEmail">
                <div class="form-group first">
                    <label>Registered Email</label>
                    <input type="email" id="otpEmail" class="form-control" placeholder="your-email@gmail.com">
                </div>
                <button type="button" class="otp-btn mt-3" id="btnSendOtp">Send OTP</button>
            </div>

            {{-- Step 2: enter + verify OTP only --}}
            <div id="otpStepVerify" style="display:none;">
                <div class="form-group first">
                    <label>Enter OTP</label>
                    <input type="text" id="otpCode" class="form-control" inputmode="numeric" maxlength="6" placeholder="6-digit OTP">
                </div>

                <button type="button" class="otp-btn mt-3" id="btnVerifyOtp">Verify OTP</button>

                <p class="text-center mt-3 mb-0">
                    Didn't get the code?
                    <a id="btnResendOtp" style="cursor:pointer; color:#002f6c;">Resend OTP</a>
                </p>
            </div>

            {{-- Step 3: set new password (shown only after OTP verified) --}}
            <div id="otpStepPassword" style="display:none;">
                <div class="form-group first">
                    <label>New Password</label>
                    <input type="password" id="otpNewPassword" class="form-control" placeholder="Enter New Password">
                </div>

                <div class="form-group last mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" id="otpNewPasswordConfirm" class="form-control" placeholder="Confirm New Password">
                </div>

                <button type="button" class="otp-btn" id="btnResetWithOtp">Reset Password</button>
            </div>

        </div>
    </div>

    <style>
        /* Inline fallback so these buttons always look like buttons,
           regardless of how login.css scopes .btn to other parents. */
        .otp-btn {
            display: block;
            width: 100%;
            padding: 10px 16px;
            background-color: #002f6c;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
        }
        .otp-btn:hover {
            background-color: #001f4d;
        }
        .otp-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", () => {

        const forgotPopup = document.getElementById("forgotPasswordPopup");
        const btnOpen = document.getElementById("btnOpenForgotPassword");
        const btnClose = document.getElementById("closeForgotPassword");

        btnOpen?.addEventListener("click", (e) => {
            e.preventDefault();
            forgotPopup.style.display = "flex";
        });

        btnClose?.addEventListener("click", () => {
            forgotPopup.style.display = "none";
        });

        forgotPopup?.addEventListener("click", (e) => {
            if (e.target === forgotPopup) {
                forgotPopup.style.display = "none";
            }
        });

        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const otpEmailInput = document.getElementById("otpEmail");
        const otpCodeInput = document.getElementById("otpCode");
        const otpNewPassword = document.getElementById("otpNewPassword");
        const otpNewPasswordConfirm = document.getElementById("otpNewPasswordConfirm");

        const otpStepEmail = document.getElementById("otpStepEmail");
        const otpStepVerify = document.getElementById("otpStepVerify");
        const otpStepPassword = document.getElementById("otpStepPassword");

        const otpAlert = document.getElementById("otpAlert");
        const btnSendOtp = document.getElementById("btnSendOtp");
        const btnVerifyOtp = document.getElementById("btnVerifyOtp");
        const btnResendOtp = document.getElementById("btnResendOtp");
        const btnResetWithOtp = document.getElementById("btnResetWithOtp");

        let otpEmailForReset = "";
        let verifiedOtp = "";
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

        // ── Step 1: request OTP ─────────────────────────────────────
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
                    body: JSON.stringify({ email }),
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

        // ── Step 2: verify OTP only ─────────────────────────────────
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
                    body: JSON.stringify({ email: otpEmailForReset, otp }),
                });
                const json = await res.json();

                if (!res.ok || !json.status) {
                    showOtpAlert("error", json.message || "Incorrect OTP.");
                    return;
                }

                verifiedOtp = otp;
                showOtpAlert("success", json.message || "OTP verified.");
                otpStepVerify.style.display = "none";
                otpStepPassword.style.display = "block";
            } catch (e) {
                showOtpAlert("error", "Network error. Please try again.");
            } finally {
                btnVerifyOtp.disabled = false;
                btnVerifyOtp.textContent = originalText;
            }
        });

        // ── Step 3: set new password ────────────────────────────────
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
                        otp: verifiedOtp,
                        password,
                        password_confirmation: passwordConfirm,
                    }),
                });
                const json = await res.json();

                if (!res.ok || !json.status) {
                    showOtpAlert("error", json.message || "Failed to reset password.");
                    return;
                }

                showOtpAlert("success", json.message || "Password reset successfully. Redirecting to login…");

                setTimeout(() => {
                    window.location.href = "{{ route('login') }}";
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

</body>

</html>