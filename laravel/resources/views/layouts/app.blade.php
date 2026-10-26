<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'متجر النخبة | دار الأزياء والملابس الفاخرة')</title>
  
  <!-- Google Fonts: Cairo & Tajawal -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Core Luxury Styling -->
  <link rel="stylesheet" href="/styles.css">
  @yield('styles')
</head>
<body>

  <!-- Top Announcement Bar -->
  <div class="announcement-bar">
    <div class="container announcement-content">
      <span id="top-announcement-text">
        <i class="fa-solid fa-truck-fast"></i> شحن مجاني للطلبات الأكثر من $150 | كود الخصم: <strong>WELCOME10</strong>
      </span>
      <div class="announcement-links" style="display:flex; gap:16px; align-items:center;">
        <button id="theme-toggle-btn" class="btn-text" onclick="app.toggleTheme()">
          <i class="fa-solid fa-moon"></i> <span id="theme-text">الوضع الداكن</span>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn-text" style="color:inherit; text-decoration:none;">
          <i class="fa-solid fa-user-shield"></i> لوحة الإدارة
        </a>
      </div>
    </div>
  </div>

  <!-- Main Navigation Header -->
  <header class="main-header">
    <div class="container header-content">
      <!-- Store Brand -->
      <a href="{{ route('store.home') }}" class="brand-logo">
        <div class="logo-icon"><i class="fa-solid fa-bag-shopping"></i></div>
        <div class="logo-text">
          <span class="brand-name">متجر النخبة</span>
          <span class="brand-tagline">ELITE STORE</span>
        </div>
      </a>

      <!-- Quick Nav Links -->
      <nav class="main-nav-links" style="display:flex; gap:20px; align-items:center;">
        <a href="{{ route('store.home') }}" class="nav-link {{ request()->routeIs('store.home') ? 'active' : '' }}">
          <i class="fa-solid fa-house"></i> الرئيسية
        </a>
        <a href="{{ route('store.products') }}" class="nav-link {{ request()->routeIs('store.products') ? 'active' : '' }}">
          <i class="fa-solid fa-shirt"></i> الكتالوج
        </a>
        <a href="{{ route('store.compare') }}" class="nav-link {{ request()->routeIs('store.compare') ? 'active' : '' }}">
          <i class="fa-solid fa-code-compare"></i> المقارنة
        </a>
        <a href="{{ route('store.track') }}" class="nav-link {{ request()->routeIs('store.track') ? 'active' : '' }}">
          <i class="fa-solid fa-truck-ramp-box"></i> تتبع الطلب
        </a>
      </nav>

      <!-- Search Bar -->
      <form action="{{ route('store.products') }}" method="GET" class="header-search" style="margin:0;">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" name="search" id="search-input" value="{{ request('search') }}" placeholder="ابحث عن المنتجات، الساعات، العطور..." oninput="app.handleSearch(this.value)">
        <button type="button" class="clear-search-btn" id="clear-search" onclick="app.clearSearch()" style="display:none;">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </form>

      <!-- Action Buttons -->
      <div class="header-actions">
        <!-- User Account Button & Dropdown -->
        <div class="user-menu-wrapper" style="position: relative;">
          <button class="action-btn" id="user-menu-btn" onclick="app.handleUserBtnClick()">
            <i class="fa-solid fa-user"></i>
            <span class="action-label" id="user-label">دخول</span>
          </button>
          <div class="user-menu-dropdown" id="user-dropdown">
            <div class="user-dropdown-header">
              <span id="user-dropdown-role">عميل المتجر</span>
              <strong id="user-dropdown-name">زائر</strong>
            </div>
            <button class="user-dropdown-item" onclick="app.showCustomerOrders()">
              <i class="fa-solid fa-box-archive"></i> طلباتي السابقة
            </button>
            <button class="user-dropdown-item logout" onclick="app.logoutUser()">
              <i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج
            </button>
          </div>
        </div>

        <!-- Notifications Hub Button & Dropdown -->
        <div class="notif-wrapper" style="position: relative;">
          <button class="action-btn" id="notif-btn" onclick="app.toggleNotifications()">
            <i class="fa-solid fa-bell"></i>
            <span class="action-label">التنبيهات</span>
            <span class="badge-count" id="notif-badge">3</span>
          </button>
          <div class="notifications-dropdown" id="notifications-dropdown">
            <div class="notif-header">
              <strong><i class="fa-solid fa-bell"></i> مركز التنبيهات</strong>
              <button class="btn-text" style="font-size:0.75rem; color:var(--primary); cursor:pointer;" onclick="app.markAllNotifsRead()">تحديد كمقروء</button>
            </div>
            <div class="notif-list" id="notif-list">
              <!-- Dynamically populated -->
            </div>
          </div>
        </div>

        <!-- Smart Calculators Dropdown Menu -->
        <div class="user-menu-wrapper" style="position: relative;">
          <button class="action-btn" id="calc-menu-btn" onclick="app.toggleCalcMenu()" title="الحاسبات الذكية" style="color: #8b5cf6;">
            <i class="fa-solid fa-calculator"></i>
            <span class="action-label">الحاسبات</span>
          </button>
          <div class="user-menu-dropdown" id="calc-dropdown" style="right: auto; left: 0; min-width: 220px;">
            <div class="user-dropdown-header">
              <span>أدوات وحاسبات النخبة</span>
              <strong>اختر نوع الحاسبة</strong>
            </div>
            <a href="{{ route('store.home') }}#smart-calculator-section" class="user-dropdown-item" style="text-decoration:none;">
              <i class="fa-solid fa-wand-magic-sparkles" style="color:#8b5cf6;"></i> حاسبة المقاسات الذكية 📏
            </a>
            <a href="{{ route('store.home') }}#cost-calculator-section" class="user-dropdown-item" style="text-decoration:none;">
              <i class="fa-solid fa-coins" style="color:#10b981;"></i> حاسبة التكلفة والوفورات 💰
            </a>
          </div>
        </div>

        <a href="{{ route('store.track') }}" class="action-btn" style="text-decoration:none;">
          <i class="fa-solid fa-truck-ramp-box"></i>
          <span class="action-label">تتبع</span>
        </a>

        <button class="action-btn" onclick="app.showWishlistModal()">
          <i class="fa-solid fa-heart"></i>
          <span class="action-label">المفضلة</span>
          <span class="badge-count" id="wishlist-badge">0</span>
        </button>

        <a href="{{ route('store.cart') }}" class="action-btn" style="text-decoration:none;">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="action-label">السلة</span>
          <span class="badge-count" id="cart-badge">0</span>
        </a>
      </div>
    </div>
  </header>

  <!-- Main View Container -->
  <main id="main-content-wrapper">
    @yield('content')
  </main>

  <!-- STORE FOOTER -->
  <footer class="store-footer" style="background:var(--card-bg); border-top:1px solid var(--border-color); padding: 50px 0 24px; margin-top:60px;">
    <div class="container">
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 32px; margin-bottom: 40px;">
        <div>
          <div class="brand-logo" style="margin-bottom:14px;">
            <div class="logo-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            <div class="logo-text">
              <span class="brand-name">متجر النخبة</span>
              <span class="brand-tagline">ELITE STORE</span>
            </div>
          </div>
          <p style="color:var(--text-light); font-size:0.9rem; line-height:1.7;">
            وجهتك الأولى للأزياء والملابس الرجالية والشبابية الفاخرة، المصممة بأعلى معايير الحياكة والأقمشة العالمية الموثوقة.
          </p>
          <div style="display:flex; gap:12px; margin-top:16px;">
            <a href="#" class="btn-circle" style="width:36px; height:36px; border-radius:50%; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:inherit; text-decoration:none;"><i class="fa-brands fa-x-twitter"></i></a>
            <a href="#" class="btn-circle" style="width:36px; height:36px; border-radius:50%; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:inherit; text-decoration:none;"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="btn-circle" style="width:36px; height:36px; border-radius:50%; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:inherit; text-decoration:none;"><i class="fa-brands fa-snapchat"></i></a>
            <a href="#" class="btn-circle" style="width:36px; height:36px; border-radius:50%; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:inherit; text-decoration:none;"><i class="fa-brands fa-whatsapp"></i></a>
          </div>
        </div>

        <div>
          <h4 style="margin-bottom:16px; font-weight:700;"><i class="fa-solid fa-compass" style="color:var(--primary);"></i> روابط سريعة</h4>
          <ul style="list-style:none; padding:0; margin:0; line-height:2.2; font-size:0.9rem;">
            <li><a href="{{ route('store.home') }}" style="color:var(--text-light); text-decoration:none;">الصفحة الرئيسية</a></li>
            <li><a href="{{ route('store.products') }}" style="color:var(--text-light); text-decoration:none;">كتالوج الأزياء الشامل</a></li>
            <li><a href="{{ route('store.compare') }}" style="color:var(--text-light); text-decoration:none;">أداة مقارنة المنتجات</a></li>
            <li><a href="{{ route('store.track') }}" style="color:var(--text-light); text-decoration:none;">تتبع الشحنات والطلبات</a></li>
            <li><a href="{{ route('admin.dashboard') }}" style="color:var(--text-light); text-decoration:none;">بوابة إدارة المتجر</a></li>
          </ul>
        </div>

        <div>
          <h4 style="margin-bottom:16px; font-weight:700;"><i class="fa-solid fa-shield-halved" style="color:#10b981;"></i> ضمانات النخبة</h4>
          <ul style="list-style:none; padding:0; margin:0; line-height:2.2; font-size:0.9rem; color:var(--text-light);">
            <li><i class="fa-solid fa-check" style="color:#10b981; margin-left:8px;"></i> أقمشة طبيعية أصلية 100%</li>
            <li><i class="fa-solid fa-check" style="color:#10b981; margin-left:8px;"></i> شحن سريع وتوصيل لباب المنزل</li>
            <li><i class="fa-solid fa-check" style="color:#10b981; margin-left:8px;"></i> استبدال واسترجاع مجاني خلال 14 يوماً</li>
            <li><i class="fa-solid fa-check" style="color:#10b981; margin-left:8px;"></i> بوابات دفع آمنة ومشفرة ZATCA</li>
          </ul>
        </div>

        <div>
          <h4 style="margin-bottom:16px; font-weight:700;"><i class="fa-solid fa-envelope-open-text" style="color:var(--accent);"></i> النشرة البريدية</h4>
          <p style="color:var(--text-light); font-size:0.85rem; margin-bottom:12px;">اشترك ليصلك جديد التشكيلات الحصرية وأكواد الخصم الخاصة قبل الجميع.</p>
          <div style="display:flex; gap:8px;">
            <input type="email" placeholder="بريدك الإلكتروني..." style="flex:1; padding:10px 14px; border:1px solid var(--border-color); border-radius:8px; background:var(--input-bg); color:inherit; font-size:0.85rem;">
            <button class="btn btn-primary btn-sm" onclick="app.showToast('تم اشتراكك في النشرة بنجاح!')">اشتراك</button>
          </div>
        </div>
      </div>

      <div style="border-top:1px solid var(--border-color); padding-top:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; font-size:0.85rem; color:var(--text-light);">
        <div>&copy; 2026 متجر النخبة (ELITE STORE) - جميع الحقوق محفوظة. مدعوم بواسطة Laravel & MariaDB.</div>
        <div style="display:flex; gap:16px;">
          <span>طرق الدفع المعتمدة:</span>
          <span><i class="fa-brands fa-cc-visa"></i> Visa</span>
          <span><i class="fa-brands fa-cc-mastercard"></i> Mastercard</span>
          <span><i class="fa-brands fa-apple-pay"></i> Apple Pay</span>
          <span><i class="fa-solid fa-money-bill-wave"></i> الدفع عند الاستلام</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Slide-out Shopping Cart Drawer -->
  <div class="cart-drawer-overlay" id="cart-drawer-overlay" onclick="app.toggleCartDrawer(false)"></div>
  <aside class="cart-drawer" id="cart-drawer">
    <div class="cart-drawer-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <i class="fa-solid fa-cart-shopping" style="color:var(--primary); font-size:1.2rem;"></i>
        <h3 style="margin:0;">سلة المشتريات</h3>
        <span class="badge-count" id="cart-drawer-count">0</span>
      </div>
      <button class="modal-close-btn" onclick="app.toggleCartDrawer(false)"><i class="fa-solid fa-xmark"></i></button>
    </div>
    
    <div class="cart-drawer-body" id="cart-drawer-body">
      <!-- Items dynamically populated -->
    </div>
    
    <div class="cart-drawer-footer">
      <div class="cart-summary-line">
        <span>المجموع الفرعي:</span>
        <strong id="cart-drawer-subtotal">$0.00</strong>
      </div>
      <div class="cart-summary-line" style="font-size:0.82rem; color:var(--text-light);">
        <span>الشحن والضرائب:</span>
        <span>تحسب عند إتمام الطلب</span>
      </div>
      <div style="display:flex; gap:8px; margin-top:14px;">
        <a href="{{ route('store.cart') }}" class="btn btn-primary btn-block" style="text-decoration:none; text-align:center;">
          <i class="fa-solid fa-bag-shopping"></i> معاينة السلة والدفع
        </a>
      </div>
    </div>
  </aside>

  <!-- Floating Compare Bar -->
  <div id="compare-floating-bar" class="compare-floating-bar">
    <div class="compare-bar-content">
      <div class="compare-bar-info">
        <div class="compare-bar-badge"><i class="fa-solid fa-code-compare"></i> مقارنة القطع</div>
        <span id="compare-bar-count">0 من 4 قطع محددة</span>
      </div>
      <div class="compare-bar-thumbs" id="compare-bar-thumbs">
        <!-- Rendered dynamically -->
      </div>
      <div class="compare-bar-actions">
        <button class="btn btn-primary btn-sm" onclick="app.openCompareModal()">
          <i class="fa-solid fa-scale-balanced"></i> قارن الآن
        </button>
        <button class="btn btn-secondary btn-sm" onclick="app.clearCompare()" title="تفريغ قائمة المقارنة">
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Smart Product Comparison Modal -->
  <div id="compare-modal" class="modal">
    <div class="modal-dialog modal-xl" style="max-width: 1100px;">
      <div class="modal-header">
        <div style="display:flex; align-items:center; gap:12px;">
          <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg, #2563eb, #7c3aed); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.2rem;">
            <i class="fa-solid fa-code-compare"></i>
          </div>
          <div>
            <h3 style="margin:0;">مقارنة المنتجات الذكية جنباً إلى جنب</h3>
            <span style="font-size:0.85rem; color:var(--text-light);">مقارنة تفصيلية لمواصفات القماش، المقاسات، الألوان، وحالة المخزون</span>
          </div>
        </div>
        <button class="modal-close-btn" onclick="app.closeModal('compare-modal')"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body" id="compare-modal-body" style="padding: 24px; overflow-x: auto;">
        <!-- Rendered dynamically -->
      </div>
    </div>
  </div>

  <!-- Review Photo Lightbox Modal -->
  <div id="review-photo-modal" class="modal">
    <div class="modal-dialog modal-md" style="max-width: 520px; text-align:center;">
      <div class="modal-header">
        <h3 id="review-photo-title" style="margin:0; font-size:1.1rem;"><i class="fa-solid fa-camera" style="color:var(--primary);"></i> صورة المنتج من تجربة العميل الموثقة</h3>
        <button class="modal-close-btn" onclick="app.closeModal('review-photo-modal')"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <img id="review-photo-img" src="" alt="صورة المراجعة" style="width:100%; max-height:460px; object-fit:contain; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.25);">
        <p id="review-photo-caption" style="margin-top:14px; font-weight:700; color:var(--text); line-height:1.5;"></p>
      </div>
    </div>
  </div>

  <!-- QUICK VIEW PRODUCT MODAL -->
  <div class="modal" id="quick-view-modal">
    <div class="modal-overlay" onclick="app.closeModal('quick-view-modal')"></div>
    <div class="modal-content modal-lg">
      <button class="close-btn modal-close" onclick="app.closeModal('quick-view-modal')"><i class="fa-solid fa-xmark"></i></button>
      <div class="quick-view-grid" id="quick-view-content">
        <!-- Dynamically populated -->
      </div>
    </div>
  </div>

  <!-- SIZE GUIDE MODAL -->
  <div class="modal" id="size-guide-modal">
    <div class="modal-overlay" onclick="app.closeModal('size-guide-modal')"></div>
    <div class="modal-content size-guide-modal-content">
      <button class="close-btn modal-close" onclick="app.closeModal('size-guide-modal')"><i class="fa-solid fa-xmark"></i></button>
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
        <div style="width:44px; height:44px; border-radius:12px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
          <i class="fa-solid fa-ruler-combined"></i>
        </div>
        <div>
          <h2 style="margin:0; font-size:1.3rem;">دليل المقاسات المعتمد للأزياء</h2>
          <p style="margin:2px 0 0; color:var(--text-light); font-size:0.85rem;">جدول القياسات الدقيقة لجميع قطع الملابس وفق المقاييس الإيطالية والخليجية</p>
        </div>
      </div>
      <div class="size-guide-tabs">
        <button class="size-tab-btn active" onclick="app.switchSizeGuideTab('suits', this)"><i class="fa-solid fa-user-tie"></i> البدلات والسترات (EU)</button>
        <button class="size-tab-btn" onclick="app.switchSizeGuideTab('thobes', this)"><i class="fa-solid fa-vest"></i> الثياب العربية (بوصة)</button>
        <button class="size-tab-btn" onclick="app.switchSizeGuideTab('casual', this)"><i class="fa-solid fa-shirt"></i> الكاجوال والقمصان (S-XXL)</button>
        <button class="size-tab-btn" onclick="app.switchSizeGuideTab('shoes', this)"><i class="fa-solid fa-shoe-prints"></i> الأحذية الجلدية</button>
      </div>
      <div id="size-tab-suits" class="size-guide-tab-panel">
        <table class="size-table">
          <thead>
            <tr><th>المقاس الأوروبي</th><th>محيط الصدر (سم)</th><th>محيط الخصر (سم)</th><th>عرض الكتف (سم)</th><th>طول الكم (سم)</th></tr>
          </thead>
          <tbody>
            <tr><td>46 (S)</td><td>92 - 95</td><td>80 - 83</td><td>43.5</td><td>62.5</td></tr>
            <tr><td>48 (M)</td><td>96 - 99</td><td>84 - 87</td><td>44.5</td><td>63.5</td></tr>
            <tr><td>50 (L)</td><td>100 - 103</td><td>88 - 91</td><td>45.5</td><td>64.5</td></tr>
            <tr><td>52 (XL)</td><td>104 - 107</td><td>92 - 95</td><td>46.5</td><td>65.5</td></tr>
            <tr><td>54 (XXL)</td><td>108 - 111</td><td>96 - 99</td><td>47.5</td><td>66.5</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- AUTH MODAL -->
  <div class="modal" id="auth-modal">
    <div class="modal-overlay" onclick="app.closeModal('auth-modal')"></div>
    <div class="modal-content" style="max-width: 440px;">
      <button class="close-btn modal-close" onclick="app.closeModal('auth-modal')"><i class="fa-solid fa-xmark"></i></button>
      <div class="auth-tabs">
        <button class="auth-tab-btn active" id="tab-btn-login" onclick="app.switchAuthTab('login')">تسجيل الدخول</button>
        <button class="auth-tab-btn" id="tab-btn-register" onclick="app.switchAuthTab('register')">إنشاء حساب جديد</button>
      </div>
      <form id="login-form" onsubmit="app.handleLogin(event)">
        <div class="form-group">
          <label>البريد الإلكتروني</label>
          <input type="email" id="login-email" required placeholder="name@domain.com">
        </div>
        <div class="form-group">
          <label>كلمة المرور</label>
          <input type="password" id="login-password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-right-to-bracket"></i> دخول</button>
        <div class="demo-account-box">
          <span>حساب تجريبي:</span>
          <button type="button" onclick="app.fillDemoCustomer()">تعبئة تجريبية</button>
        </div>
      </form>
      <form id="register-form" onsubmit="app.handleRegister(event)" style="display:none;">
        <div class="form-group">
          <label>الاسم الكامل *</label>
          <input type="text" id="reg-name" required placeholder="الاسم الثلاثي">
        </div>
        <div class="form-group">
          <label>البريد الإلكتروني *</label>
          <input type="email" id="reg-email" required placeholder="name@domain.com">
        </div>
        <div class="form-group">
          <label>كلمة المرور *</label>
          <input type="password" id="reg-password" required placeholder="6 أحرف على الأقل">
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-user-plus"></i> إنشاء الحساب</button>
      </form>
    </div>
  </div>

  <!-- WISHLIST MODAL -->
  <div class="modal" id="wishlist-modal">
    <div class="modal-overlay" onclick="app.closeModal('wishlist-modal')"></div>
    <div class="modal-content">
      <button class="close-btn modal-close" onclick="app.closeModal('wishlist-modal')"><i class="fa-solid fa-xmark"></i></button>
      <h2><i class="fa-solid fa-heart" style="color:#ef4444;"></i> قائمة المفضلة</h2>
      <div id="wishlist-items-container" class="wishlist-grid">
        <!-- Dynamically populated -->
      </div>
    </div>
  </div>

  <!-- CUSTOMER ORDERS MODAL -->
  <div class="modal" id="customer-orders-modal">
    <div class="modal-overlay" onclick="app.closeModal('customer-orders-modal')"></div>
    <div class="modal-content" style="max-width: 600px;">
      <button class="close-btn modal-close" onclick="app.closeModal('customer-orders-modal')"><i class="fa-solid fa-xmark"></i></button>
      <h2><i class="fa-solid fa-box-open"></i> سجل طلباتي السابقة</h2>
      <div id="customer-orders-container" class="orders-history-list">
        <!-- Dynamically populated -->
      </div>
    </div>
  </div>

  <!-- Toast Notification System Container -->
  <div class="toast-container" id="toast-container"></div>

  <!-- Scripts -->
  <script src="/app.js"></script>
  @yield('scripts')
</body>
</html>
