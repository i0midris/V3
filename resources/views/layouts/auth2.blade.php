<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ERP Enough - الصفحة الرئيسية</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    :root {
      --primary: #2C4688;
      --secondary: #E4191F;
      --white: #FFFFFF;
      --text-light: #D1D5DB;
      --dark-blue: #1a2c53;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
  font-family: 'Cairo', sans-serif;
  background: linear-gradient(135deg,
    #2C4688 0%,
    #2C4688 20%,
    #E4191F 40%,
    #2C4688 60%,
    #E8EAF2 80%,
    #2C4688 100%) !important;
  background-size: 400% 400%;
  animation: moveBackground 25s ease infinite;
  color: var(--text-light);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding-bottom: 130px;
  overflow-x: hidden;
}

@keyframes moveBackground {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
    .logo {
      width: 220px;
      /* margin-bottom: 1.2rem; */
    }

    .title {
      font-size: 2rem;
      font-weight: 900;
      color: var(--white);
    }

    .tagline {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--white);
      margin-bottom: 1rem;
    }

    .description {
      font-size: 1rem;
      color: #e2e8f0;
      line-height: 1.8;
      margin-bottom: 2rem;
    }

    .btn {
      display:none;
      background-color: var(--secondary);
      color: white;
      padding: 0.75rem 2rem;
      font-size: 1rem;
      border: none;
      border-radius: 30px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 0 15px rgba(228, 25, 31, 0.6);
    }

    .btn:hover {
      background-color: #b11216;
      transform: translateY(-2px);
      box-shadow: 0 0 30px rgba(228, 25, 31, 0.7);
    }

    .footer {
      /* margin-top: 0.5rem; */
      font-size: 0.75rem;
      color: #ffffffaa;
    }

    .footer-bar {
      width: 100%;
      position: absolute;
      bottom: 10px;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin-bottom: -0.8rem;
    }

    .social-icons a {
      color: var(--white);
      font-size: 1rem;
      transition: transform 0.3s, color 0.3s;
    }

    .social-icons a:hover {
      color: var(--secondary);
      transform: scale(1.2);
    }

    .map-link {
      font-size: 0.8rem;
      color: #d9e0ef;
      text-decoration: none;
      font-weight : bolder
    }

    .map-link:hover {
      color: var(--secondary);
      text-decoration: underline;
    }

    .whatsapp-button {
      position: fixed;
      bottom: 20px;
      left: 20px;
      background-color: #25D366;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      transition: transform 0.3s;
    }

    .whatsapp-button:hover {
      transform: scale(1.1);
    }

    .whatsapp-button img {
      width: 28px;
    }

    
    .main-wrapper{
      display: flex;
      align-items: center;
      margin-top : 8rem;
      height : 95dvh;
      background: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      box-shadow: 0 0 30px rgba(44, 70, 136, 0.5);
      width: 80%;
    }
    .auth-wrapper{
      display:flex;
      align-items:center;
      height: 100%;
      width: 70%;
      padding: 1rem 2rem;
      position: relative;
    }
    .loginWrapper{
      width: 45%;
      height: 100%;
      background: white;
      border-radius: 0 20px 20px 0;
    }
    .footer-social{
      margin-bottom : 0.5rem;
    }
    .content{
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: -2rem;
    }
    /* @media(max-width:426px){
    } */

    @media (max-width:600px){
        .description{ margin-bottom:1rem; }
        .btn{ display : inline-block; }
        .main-wrapper{ min-height: 90dvh}
        /* .footer-bar{ transform:none; position:relative; left:0; bottom:0; } */
        .auth-wrapper{
          width:100%;
        }
        .loginWrapper{
          width: 100% !important;
          background: none;
          display:none;
          border-radius : 20px;
        }
    }
    
    @media (max-width: 768px) {
      .main-wrapper{ width:97%; }
      .title { font-size: 1.5rem; margin-top:-0.5rem }
      .tagline { font-size: 0.9rem; margin-bottom : 0.75rem; }
      /* .description { font-size: 0.75rem; } */
      .btn { font-size: 0.95rem; padding: 0.7rem 1.6rem; }
      .social-icons { flex-wrap: wrap; gap: 0.8rem; }
      .social-icons a{ font-size: 1rem; }
      .social-icons{ gap:1rem}
    }
    
    /* for tablet */
    @media (max-width: 1025px) {
      .main-wrapper{ width : 90%; }
      .logo{ width: 200px; }
      .title { font-size: 1.5rem; }
      .description { font-size: 0.75rem; }
      .footer{ font-size: 0.6rem; }
      /* .social-icons a{ font-size: 1.2rem; } */
      /* .footer-social{ margin-bottom : 0.5rem; } */
      .loginWrapper { width: 60%;}

    }
    /* @media (max-width: 1024px) {
      
    } */
  </style>
</head>
<body>

  <div class="main-wrapper">
    <div class="loginWrapper" id="login-wrapper">
      @include('auth.login')
    </div>
    <div class="auth-wrapper" id="auth-wrapper">
      <div class="content">
          <img src="{{ asset('img/logo-small.png') }}" alt="Logo" class="logo" />
        <h1 class="title" style="margin-bottom: -0.8rem !important;">ERP Enough</h1>
        <div class="tagline">دقة - سرعة - أمان</div>
        <p class="description">
          نظام احترافي لإدارة الأعمال يقدم حلولًا متكاملة لتحسين الأداء، تسريع العمليات، وضمان الأمان الكامل لبياناتك، مع واجهة سلسة وتجربة استخدام مميزة.
        </p>
        <button id="login-button" class="btn">تسجيل الدخول</button>
      </div>
      <div class="footer-bar">
        <div class="footer-social">
          <div class="social-icons">
            <a href="https://www.facebook.com/albaseetsoft.ye" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://x.com/albaseetsoft" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="https://instagram.com/albaseet_soft" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://www.youtube.com/@AlbaseetSoft" target="_blank" title="youtube"><i class="fab fa-youtube"></i></a>
            <a href="https://wa.me/967777335118" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          </div>
          <!-- <a href="https://maps.app.goo.gl/YRf8BLNCNkedeeA78" target="_blank" class="map-link">موقعنا على الخريطة</a> -->
        </div>
        <div class="footer">
          © 2025 AlbaseetSoft - جميع الحقوق محفوظة
        </div>    
      </div>
      
      <!-- <a href="{{ route('login') }}">
        <button class="btn">تسجيل الدخول</button>
      </a> -->
    </div>

  </div>

  <!-- Floating Social Icons + Location -->
  

  <!-- WhatsApp Button -->
  <a href="https://wa.me/967777335118" class="whatsapp-button" target="_blank" title="تواصل معنا على واتساب">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
  </a>

  <script>
        // Get references to the elements
        const authWrapper = document.getElementById('auth-wrapper');
        const loginWrapper = document.getElementById('login-wrapper');
        const loginButton = document.getElementById('login-button');

        // Function to toggle the display of the divs
        function toggleDivs() {
          // Get the computed style of loginWrapper
          const isLoginHidden = window.getComputedStyle(loginWrapper).display === "none";

          if (isLoginHidden) {
              // If loginWrapper is hidden, show it and hide authWrapper
              loginWrapper.style.display = "block";
              authWrapper.style.display = "none";
          } else {
              // Otherwise, hide loginWrapper and show authWrapper
              loginWrapper.style.display = "none";
              authWrapper.style.display = "block";
          }
        }

        // Add a click event listener to the button
        loginButton.addEventListener('click', toggleDivs);

        // Function to handle screen size changes
        // function handleResize() {
        //     // If the screen width is 768px or greater (the md breakpoint in Tailwind)
        //     if (window.innerWidth >= 768) {
        //         // Make sure both divs are visible
        //         authWrapper.classList.remove('hidden');
        //         loginWrapper.classList.remove('hidden');
        //     } else {
        //         // On smaller screens, only the auth-wrapper should be visible initially
        //         // and the login-wrapper should be hidden
        //         authWrapper.classList.remove('hidden');
        //         loginWrapper.classList.add('hidden');
        //     }
        // }

        // // Add an event listener for when the window is resized
        // window.addEventListener('resize', handleResize);

        // // Call the function once on load to set the initial state
        // window.onload = handleResize;

    </script>
</body>
</html>