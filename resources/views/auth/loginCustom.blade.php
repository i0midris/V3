<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - ERP Enough</title>

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- AOS Animation (Optional) --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />

    {{-- App CSS --}}
    <link rel="stylesheet" href="{{ asset('css/login.css?v=' . $asset_v) }}">
    <style>
/* ===== Reset ===== */
*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html, body {
  height: 100%;
  font-family: 'Cairo', sans-serif;
  direction: rtl;
  overflow: hidden;
}

/* ===== Animated Gradient Background ===== */
body {
  background: linear-gradient(
    -45deg,
    #2C4688 30%,
    #E4191F 45%,
    #E8EAF2 60%,
    #2C4688 80%
  );
  background-size: 600% 600%;
  animation: gradientMotion 15s ease-in-out infinite;
  position: relative;
  overflow: hidden;
}

/* ===== Subtle blur to soften edges but retain visibility ===== */
body::before {
  content: '';
  position: absolute;
  inset: 0;
  backdrop-filter: blur(30px);
  z-index: 0;
}

/* ===== Gradient Animation Keyframes =====
@keyframes gradientMotion {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
} */

/* ===== Wrapper ===== */
.login-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  padding: 10% 5%;
  z-index: 1;
}

/* ===== Login Card with floating animation ===== */
.login-card {
  /* background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(12px); */
  /* border-radius: 20px; */
  /* padding: 2.5rem; */
  max-width: 420px;
  max-height: 99%;
  width: 100%;
  text-align: center;
  /* box-shadow: 0 0 30px rgba(44, 70, 136, 0.4); */
  /* animation: floatCard 6s ease-in-out infinite; */
  z-index: 1;
}

/* ===== Floating Animation for Card ===== */
@keyframes floatCard {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

/* ===== Logo ===== */
.loginLogo {
  width: 200px;
  display: none;
  margin-bottom: 15%;
}


/* ===== Headings ===== */
.title {
  font-size: 1.5rem;
  color: #ffffff;
  font-weight: 700;
}
.loginTitle{ color : black; }

.subtitle {
  font-size: 0.95rem;
  color: gray;
  margin-bottom: 2rem;
}

/* ===== Form Group ===== */
.form-group {
  margin-bottom: 1rem;
  text-align: right;
}

.form-group label {
  display: block;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
  color: black;
}

/* ===== Input Fields ===== */
.input-icon {
  position: relative;
}

.input-icon input {
  width: 100%;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  border: 1px solid #ddd;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.15);
  color: #aaa;
  outline: none;
  transition: 0.3s;
}

.input-icon input::placeholder {
  color: #bbb;
}

.input-icon i,
.toggle-password {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #aaa;
  font-size: 1rem;
}

.toggle-password {
  background: none;
  border: none;
  cursor: pointer;
  color: #ffffffdd;
}

/* ===== Checkbox ===== */
.form-group.checkbox {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
  color: #fff;
  gap: 0.4rem;
}

.form-group.checkbox input[type="checkbox"] {
  width: 16px;
  height: 16px;
  margin-left: 0.25rem
}

/* ===== Forgot Password ===== */
.forgot-link {
  font-size: 0.65rem;
  display: inline-block;
  margin-top: 0.1rem;
  color: #aaa;
  text-decoration: none;
  transition: 0.2s;
}

.forgot-link:hover {
  text-decoration: underline;
  color: gray;
}

/* ===== Submit Button ===== */
.btn-submit {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  background: #E4191F;
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.3s ease, transform 0.2s;
  margin-top: 0;
}

.btn-submit:hover {
  background: #b91517;
  transform: translateY(-2px);
}

/* ===== Error Message ===== */
.error-message {
  font-size: 0.65rem;
  color: red;
  margin-top: 0.3rem;
}

/* ===== Register Link ===== */
.register-link {
  margin-bottom: 1rem;
  margin-top: 0;
  color: gray;
  font-size: 0.9rem;
}

.register-link a {
  color: #E4191F;
  font-weight: bold;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}

/* ===== Footer / Social Links ===== */
.social-footer {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(6px);
  border-radius: 50px;
  padding: 0.6rem 1.4rem;
  display: flex;
  gap: 1.2rem;
  z-index: 2;
}

.social-footer a {
  color: #fff;
  font-size: 1.4rem;
  transition: transform 0.3s, color 0.3s;
}

.social-footer a:hover {
  transform: scale(1.2);
  color: #E4191F;
}

/* ===== Responsive ===== */
@media (max-width:600px){
    .login-wrapper{  min-height: 55dvh;}
    .loginLogo{ display : inline-block}
    .loginTitle, .form-group label  { color : #ffffff; }
    .subtitle, .register-link{ color : #f0f0f0; }
    .input-icon input{border: 1px solid #ffffff55;background: rgba(255, 255, 255, 0.15); color: #fff;}
    .input-icon input::placeholder{ color: #ffffff99; }
    .input-icon i, .toggle-password {color: #ffffffaa;}
    .toggle-password {color: #ffffffdd;}
    .forgot-link{color: #ffffffcc;}

}

@media (max-width: 768px) {
    /* .login-wrapper { padding: 1rem; } */
    .login-card { padding: 0rem 1.5rem; }
    .social-footer { flex-wrap: wrap; gap: 0.8rem; font-size: 1.1rem; }
    /* .title { font-size: 1.3rem; } */

}
@media (max-width: 1025px) {
    .title { font-size: 1.3rem; margin-bottom:0; }
    .subtitle{ font-size: 0.75rem; margin-bottom:1rem }
    .form-group label{ margin-bottom : 0.25rem; font-size:0.75rem; }
    .forgot-link { font-size : 0.6rem; margin-top : 0 }
}

    </style>
</head>
<body>

    @inject('request', 'Illuminate\Http\Request')
    @if (session('status') && session('status.success'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
    @endif

    <div class="login-wrapper">
        <div class="background-overlay"></div>

        <div class="login-card" data-aos="zoom-in">
            <!-- <div class="logo"> -->
                <img src="{{ asset('img/new-logo-2.png') }}" class="loginLogo" alt="Logo">
            <!-- </div> -->
            <h2 class="title loginTitle">تسجيل الدخول</h2>
            <p class="subtitle">الوصول إلى لوحة التحكم الخاصة بك</p>

            @php
                $username = old('username');
                $password = null;
                if (config('app.env') == 'demo') {
                    $username = 'admin';
                    $password = '123456';
                    $demo_types = [
                        'all_in_one' => 'admin',
                        'super_market' => 'admin',
                        'pharmacy' => 'admin-pharmacy',
                        'electronics' => 'admin-electronics',
                        'services' => 'admin-services',
                        'restaurant' => 'admin-restaurant',
                        'superadmin' => 'superadmin',
                        'woocommerce' => 'woocommerce_user',
                        'essentials' => 'admin-essentials',
                        'manufacturing' => 'manufacturer-demo',
                    ];
                    if (!empty($_GET['demo_type']) && array_key_exists($_GET['demo_type'], $demo_types)) {
                        $username = $demo_types[$_GET['demo_type']];
                    }
                }
            @endphp

            <form method="POST" action="{{ route('login') }}" id="login-form">
                {{ csrf_field() }}

                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <div class="input-icon">
                        <input type="text" name="username" id="username" class="form-control" value="{{ $username }}" required placeholder="ادخل اسم المستخدم">
                        <i class="fa fa-user"></i>
                    </div>
                    @if ($errors->has('username'))
                        <span class="error-message">***{{ $errors->first('username') }}***</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <div class="input-icon">
                        <input type="password" name="password" id="password" class="form-control" value="{{ $password }}" required placeholder="ادخل كلمة المرور">
                        <button type="button" id="show_hide_icon" class="toggle-password" style="margin-left:-20px"><i class="fa fa-eye"></i></button>
                    </div>
                    @if ($errors->has('password'))
                        <span class="error-message">{{ $errors->first('password') }}</span>
                    @endif
                    @if (config('app.env') != 'demo')
                        <a href="{{ route('password.request') }}" class="forgot-link">نسيت كلمة المرور؟</a>
                    @endif
                </div>

                <div class="form-group checkbox">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> تذكرني
                    </label>
                </div>

                @if(config('constants.enable_recaptcha'))
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="{{ config('constants.google_recaptcha_key') }}"></div>
                        @if ($errors->has('g-recaptcha-response'))
                            <span class="error-message">{{ $errors->first('g-recaptcha-response') }}</span>
                        @endif
                    </div>
                @endif

                <button type="submit" class="btn-submit">تسجيل الدخول</button>
            </form>

            @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                @if (config('constants.allow_registration'))
                    <p class="register-link">
                        ليس لديك حساب؟ <a href="{{ route('business.getRegister') }}">سجل الآن</a>
                    </p>
                @endif
            @endif
        </div>
    </div>

    {{-- JS --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
    <script>
       document.addEventListener("DOMContentLoaded", function () {
  // ===== Password Visibility Toggle =====
  const togglePassword = document.querySelector(".toggle-password");
  const passwordInput = document.getElementById("password");

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", function () {
      const isHidden = passwordInput.type === "password";
      passwordInput.type = isHidden ? "text" : "password";

      togglePassword.innerHTML = isHidden
        ? '<i class="fas fa-eye-slash"></i>'
        : '<i class="fas fa-eye"></i>';
    });
  }

  // ===== Demo Login Auto-Fill (if used in demo mode) =====
  const demoLogins = document.querySelectorAll(".demo-login");
  demoLogins.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const usernameInput = document.getElementById("username");
      const passwordInput = document.getElementById("password");
      const form = document.getElementById("login-form");

      if (usernameInput && passwordInput && form) {
        usernameInput.value = btn.dataset.admin || "admin";
        passwordInput.value = "123456";
        form.submit();
      }
    });
  });

  // ===== reCAPTCHA Check =====
  const form = document.getElementById("login-form");
  if (form) {
    form.addEventListener("submit", function (e) {
      const recaptcha = document.querySelector(".g-recaptcha");
      if (recaptcha && typeof grecaptcha !== "undefined") {
        const response = grecaptcha.getResponse();
        if (!response) {
          e.preventDefault();
          alert("يرجى إكمال reCAPTCHA.");
        }
      }
    });
  }

  // ===== Status Message Alert =====
  const status = document.getElementById("status_span");
  if (status && status.dataset.status === "true") {
    alert(status.dataset.msg || "تم بنجاح");
  }

  // ===== AOS Init (Animate On Scroll) =====
  if (typeof AOS !== "undefined") {
    AOS.init({ once: true });
  }

  // ===== GSAP Entrance Animation =====
  if (typeof gsap !== "undefined") {
    gsap.from(".login-card", {
      opacity: 0,
      y: 30,
      duration: 0.8,
      delay: 0.2,
      ease: "power2.out",
    });
    gsap.from(".social-footer", {
      opacity: 0,
      y: 20,
      duration: 0.6,
      delay: 0.6,
      ease: "power2.out",
    });
  }
});

    </script>
</body>
</html>
