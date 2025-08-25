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
    #2C4688 100%);
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


    .wrapper {
      background: rgba(44, 70, 136, 0.95);
      border-radius: 20px;
      padding: 2.5rem;
      box-shadow: 0 0 30px rgba(44, 70, 136, 0.5);
      backdrop-filter: blur(6px);
      max-width: 600px;
      width: 90%;
    }

    .logo {
      width: 200px;
      margin-bottom: 1.2rem;
    }

    .title {
      font-size: 2rem;
      font-weight: 900;
      color: var(--white);
      margin-bottom: 0.6rem;
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
      margin-top: 1.0rem;
      font-size: 0.85rem;
      color: #cbd5e1;
    }

    .footer-bar {
      position: fixed;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%);
      text-align: center;
      z-index: 50;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 1.2rem;
      margin-bottom: 0.4rem;
    }

    .social-icons a {
      color: var(--white);
      font-size: 1.5rem;
      transition: transform 0.3s, color 0.3s;
    }

    .social-icons a:hover {
      color: var(--secondary);
      transform: scale(1.2);
    }

    .map-link {
      font-size: 1.1rem;
      color: #d9e0ef;
      text-decoration: none;
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

    @media (max-width: 600px) {
      .title { font-size: 1.6rem; }
      .tagline { font-size: 1rem; }
      .description { font-size: 0.95rem; }
      .btn { font-size: 0.95rem; padding: 0.7rem 1.6rem; }
      .social-icons { flex-wrap: wrap; gap: 0.8rem; }
    }
  </style>
</head>
<body>
<img src="{{ asset('img/logo-small.png') }}" alt="Logo" class="logo" />

  <div class="wrapper">
    <h1 class="title">ERP Enough</h1>
    <div class="tagline">دقة - سرعة - أمان</div>
    <p class="description">
      نظام احترافي لإدارة الأعمال يقدم حلولًا متكاملة لتحسين الأداء، تسريع العمليات، وضمان الأمان الكامل لبياناتك، مع واجهة سلسة وتجربة استخدام مميزة.
    </p>
    <a href="{{ route('login') }}">
      <button class="btn">تسجيل الدخول</button>
    </a>

  </div>

  <!-- Floating Social Icons + Location -->
  <div class="footer-bar">
    <div class="social-icons">
      <a href="https://www.facebook.com/albaseetsoft.ye" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="https://x.com/albaseetsoft" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
      <a href="https://instagram.com/albaseet_soft" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="https://www.youtube.com/@AlbaseetSoft" target="_blank" title="youtube"><i class="fab fa-youtube"></i></a>
      <a href="https://wa.me/967777335118" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
    </div>
    <a href="https://maps.app.goo.gl/YRf8BLNCNkedeeA78" target="_blank" class="map-link">موقعنا على الخريطة</a>
    <div class="footer">
      © 2025 AlbaseetSoft - جميع الحقوق محفوظة
    </div>
  </div>

  <!-- WhatsApp Button -->
  <a href="https://wa.me/967777335118" class="whatsapp-button" target="_blank" title="تواصل معنا على واتساب">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
  </a>

</body>
</html>
