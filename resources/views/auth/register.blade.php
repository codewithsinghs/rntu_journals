<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register · RNTU Journals</title>

    <link rel="stylesheet" href="{{ asset('assets/css/frontend/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    {{-- Reuses the exact .login-popup-overlay / .login-popup markup from style.css
         (same one powering the homepage register popup). JS below forces it
         open on page load, and the "×" now navigates back to the homepage
         instead of just hiding the overlay. --}}

    <div class="login-popup-overlay" id="registerPageOverlay">
        <div class="login-popup">

            <!-- Image -->
            <div class="popup-left">
                <img src="{{ asset('storage/popup_img.png') }}" alt="Register" />
            </div>

            <!-- Form -->
            <div class="popup-right">

                <!-- Close: goes back to the homepage since there's no popup to close here -->
                <a href="{{ url('/') }}" class="close-btn">&times;</a>

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

                <!-- Tabs -->
                <div class="tab-buttons">
                    <button type="button" class="login_tab_btn active" data-tab="register">Register Here</button>
                </div>

                <div class="random_wrapper_form-container">
                    <div class="random_wrapper_form" id="registerFormWrapper">

                        <div class="xfw-form-panel random_staff_form">
                            <form action="{{ route('register.submit') }}" method="POST">
                                @csrf

                               

                                <label>Full Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter Name" />

                                <label>Mobile No.</label>
                                <input type="text" name="mobile" value="{{ old('mobile') }}" placeholder="Enter Number" />

                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter Email" />

                                <label>Create Password</label>
                                <input type="password" name="password" placeholder="Enter Password" />

                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" placeholder="Repeat Password" />

                                <button class="btn-login-submit" type="submit">Register Now</button>

                                <p class="register-link">
                                    Already have an account?
                                    <a href="{{ route('login') }}">Sign in</a>
                                </p>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Force the overlay open since this is now its own page, not a popup
        // triggered by a button click.
        document.addEventListener("DOMContentLoaded", () => {
            const overlay = document.getElementById("registerPageOverlay");
            overlay.style.display = "flex";

            // .login-popup has no height cap in style.css, so on a full page
            // (with more form fields than the original popup) it can grow
            // taller than the screen. Cap it here at runtime and let the
            // form column scroll internally instead of pushing the image
            // (and the whole card) past the viewport.
            const card = overlay.querySelector(".login-popup");
            const formSide = overlay.querySelector(".popup-right");

            card.style.maxHeight = "90vh";
            formSide.style.overflowY = "auto";
            formSide.style.maxHeight = "90vh";
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>