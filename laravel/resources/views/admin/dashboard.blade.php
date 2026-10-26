<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>لوحة التحكم والإدارة المتقدمة | متجر النخبة</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="stylesheet" href="/styles.css">
</head>
<body class="admin-portal-body">

  <div class="admin-portal-view" style="display:block; min-height:100vh;">
    <div class="admin-portal-layout">
      
      <!-- Sleek Collapsible Admin Sidebar -->
      <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar-header">
          <div class="admin-brand-icon">
            <i class="fa-solid fa-layer-group"></i>
          </div>
          <div class="admin-brand-meta">
            <div class="admin-brand-title">متجر النخبة</div>
            <div class="admin-brand-badge">ADMIN PORTAL PRO <span class="live-dot"></span></div>
          </div>
          <button class="admin-sidebar-close-btn" onclick="app.toggleAdminSidebar(false)">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- MariaDB Health Chip -->
        <div class="admin-db-status-chip">
          <i class="fa-solid fa-server"></i>
          <span>{{ $dbVersion ?? 'MariaDB 10.4' }} • Port 3306</span>
          <span class="db-pulse-dot" title="متصل ونشط"></span>
        </div>

        <!-- Sidebar Navigation Groups -->
        <div class="admin-sidebar-nav">
          <div class="admin-nav-group-title">الرئيسية والتحليلات</div>
          <button class="admin-nav-item active" onclick="app.switchAdminTab('overview-tab', this)">
            <i class="fa-solid fa-chart-pie"></i>
            <span>لوحة المؤشرات العامة</span>
            <span class="nav-badge-pill" id="nav-badge-overview">مباشر</span>
          </button>

          <div class="admin-nav-group-title">التجارة والمبيعات</div>
          <button class="admin-nav-item" onclick="app.switchAdminTab('products-tab', this)">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>إدارة المنتجات والمخزون</span>
            <span class="nav-badge-pill" id="nav-badge-products">{{ $productsCount ?? 15 }}</span>
          </button>
          <button class="admin-nav-item" onclick="app.switchAdminTab('orders-tab', this)">
            <i class="fa-solid fa-receipt"></i>
            <span>الطلبات الواردة والشحن</span>
            <span class="nav-badge-pill bg-amber" id="nav-badge-pending">{{ $ordersCount ?? 0 }}</span>
          </button>
          <button class="admin-nav-item" onclick="app.switchAdminTab('coupons-tab', this)">
            <i class="fa-solid fa-tags"></i>
            <span>الكوبونات والخصومات</span>
            <span class="nav-badge-pill">{{ $couponsCount ?? 0 }}</span>
          </button>
          <button class="admin-nav-item" onclick="app.switchAdminTab('categories-tab', this)">
            <i class="fa-solid fa-shirt"></i>
            <span>أقسام وتصنيفات المتجر</span>
          </button>

          <div class="admin-nav-group-title">علاقات العملاء والتقييمات</div>
          <button class="admin-nav-item" onclick="app.switchAdminTab('users-tab', this)">
            <i class="fa-solid fa-users"></i>
            <span>العملاء والمستخدمين</span>
          </button>
          <button class="admin-nav-item" onclick="app.switchAdminTab('reviews-tab', this)">
            <i class="fa-solid fa-star"></i>
            <span>التقييمات والمراجعات</span>
            <span class="nav-badge-pill">{{ $reviewsCount ?? 0 }}</span>
          </button>

          <div class="admin-nav-group-title">النظام والتدقيق</div>
          <button class="admin-nav-item" onclick="app.switchAdminTab('database-tab', this)">
            <i class="fa-solid fa-database"></i>
            <span>محرك MariaDB والنظام</span>
          </button>
          <button class="admin-nav-item" onclick="app.switchAdminTab('audit-tab', this)">
            <i class="fa-solid fa-clipboard-list"></i>
            <span>سجل العمليات والتدقيق (Audit)</span>
          </button>
          <button class="admin-nav-item" onclick="app.switchAdminTab('settings-tab', this)">
            <i class="fa-solid fa-sliders"></i>
            <span>إعدادات المتجر العامة</span>
          </button>
        </div>

        <!-- Sidebar User Footer -->
        <div class="admin-sidebar-footer">
          <div class="admin-user-profile-card">
            <div class="admin-user-avatar">
              <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="admin-user-info">
              <strong id="admin-sidebar-user-name">مدير النظام العام</strong>
              <span id="admin-sidebar-user-email">admin@elite.com</span>
            </div>
          </div>
          <div class="admin-footer-actions">
            <a href="{{ route('store.home') }}" class="btn btn-secondary btn-sm" title="العودة إلى واجهة المتجر" style="text-decoration:none;">
              <i class="fa-solid fa-store"></i> المتجر
            </a>
            <button class="btn btn-secondary btn-sm" onclick="app.logoutUser()" title="تسجيل الخروج">
              <i class="fa-solid fa-right-from-bracket"></i> خروج
            </button>
          </div>
        </div>
      </aside>

      <!-- Overlay for mobile sidebar -->
      <div class="admin-sidebar-overlay" onclick="app.toggleAdminSidebar(false)"></div>

      <!-- Admin Main Wrapper -->
      <div class="admin-main-wrapper">
        
        <!-- Modern Topbar -->
        <header class="admin-topbar">
          <div class="admin-topbar-left">
            <button class="admin-toggle-sidebar-btn" onclick="app.toggleAdminSidebar(true)" title="فتح القائمة">
              <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <div class="admin-breadcrumbs">
              <span class="bc-root"><i class="fa-solid fa-gauge-high"></i> لوحة الإدارة</span>
              <span class="bc-divider">/</span>
              <span class="bc-current" id="admin-current-breadcrumb">نظرة عامة حية</span>
            </div>
          </div>

          <div class="admin-topbar-search" onclick="app.openCommandPalette()" style="cursor:pointer;" title="بحث فوري أو اضغط Ctrl + K">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span style="font-size:0.85rem; color:var(--text-light); flex:1;">بحث أو تنفيذ أمر سريع في لوحة الإدارة...</span>
            <span class="command-kbd-badge">Ctrl K</span>
          </div>

          <div class="admin-topbar-right">
            <div class="admin-live-clock" id="admin-live-clock">
              <i class="fa-regular fa-clock"></i> <span id="admin-clock-text">--:--</span>
            </div>

            <!-- Live Alerts Hub -->
            <div class="admin-notifs-wrapper">
              <button class="admin-top-action-btn admin-bell-btn" onclick="app.toggleAdminAlertsMenu()" title="مركز التنبيهات الفورية والمخزون">
                <i class="fa-solid fa-bell"></i>
                <span class="admin-bell-badge" id="admin-alerts-badge">3</span>
              </button>
              <div class="admin-alerts-dropdown" id="admin-alerts-dropdown">
                <div class="alerts-dropdown-header">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-bell" style="color:var(--primary);"></i>
                    <strong>مركز التنبيهات الفورية</strong>
                  </div>
                  <button type="button" class="btn-text-sm" onclick="app.markAllAlertsRead()">تحديد الكل كمقروء</button>
                </div>
                <div class="alerts-dropdown-body" id="admin-alerts-list">
                  <!-- Rendered dynamically -->
                </div>
                <div class="alerts-dropdown-footer">
                  <button type="button" class="btn-text-sm" onclick="app.switchAdminTab('audit-tab'); app.toggleAdminAlertsMenu(false);">عرض سجل العمليات والتدقيق الكامل ←</button>
                </div>
              </div>
            </div>

            <button class="admin-top-action-btn" onclick="app.refreshAdminDashboard()" title="تحديث حي للبيانات">
              <i class="fa-solid fa-arrows-rotate" id="admin-refresh-icon"></i>
            </button>
            <button class="admin-top-action-btn" onclick="app.toggleTheme()" title="تبديل المظهر">
              <i class="fa-solid fa-moon"></i>
            </button>
            <button class="btn btn-primary btn-sm" onclick="app.openProductModal()">
              <i class="fa-solid fa-plus"></i> قطعة جديدة
            </button>
            <a href="{{ route('store.home') }}" class="btn btn-secondary btn-sm" style="text-decoration:none;">
              <i class="fa-solid fa-arrow-up-right-from-square"></i> المتجر
            </a>
          </div>
        </header>

        <!-- Admin Content Body -->
        <div class="admin-content-body">
          
          <!-- TAB 1: EXECUTIVE DASHBOARD OVERVIEW -->
          <div id="overview-tab" class="tab-content active">
            <div class="tab-actions">
              <div>
                <h3><i class="fa-solid fa-chart-pie" style="color:var(--primary);"></i> لوحة المؤشرات التنفيذية ونظرة عامة حية</h3>
                <span style="font-size:0.85rem; color:var(--text-light);">مؤشرات الأداء اللحظية من MariaDB، حركة الإيرادات، وتوزيع المخزون</span>
              </div>
              <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <span class="analytics-period-badge" id="admin-db-heartbeat-badge">
                  <span class="pulse-dot"></span> MariaDB متصل وفعال
                </span>
              </div>
            </div>

            <!-- KPI Stats Cards Row -->
            <div class="stats-grid" id="stats-container" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:24px;">
              <div class="stat-card" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:20px; display:flex; align-items:center; gap:16px;">
                <div class="stat-icon bg-green" style="width:52px; height:52px; border-radius:12px; background:rgba(16, 185, 129, 0.1); color:#10b981; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
                  <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-label" style="font-size:0.85rem; color:var(--text-light);">إجمالي المبيعات</span>
                  <h3 class="stat-value" id="stat-sales" style="font-size:1.5rem; margin:4px 0 0; color:var(--text);">${{ number_format($totalSales ?? 1845.50, 2) }}</h3>
                </div>
              </div>

              <div class="stat-card" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:20px; display:flex; align-items:center; gap:16px;">
                <div class="stat-icon bg-blue" style="width:52px; height:52px; border-radius:12px; background:rgba(37, 99, 235, 0.1); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
                  <i class="fa-solid fa-boxes-packing"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-label" style="font-size:0.85rem; color:var(--text-light);">إجمالي الطلبات</span>
                  <h3 class="stat-value" id="stat-orders" style="font-size:1.5rem; margin:4px 0 0; color:var(--text);">{{ $ordersCount ?? 12 }}</h3>
                </div>
              </div>

              <div class="stat-card" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:20px; display:flex; align-items:center; gap:16px;">
                <div class="stat-icon bg-purple" style="width:52px; height:52px; border-radius:12px; background:rgba(124, 58, 237, 0.1); color:#7c3aed; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
                  <i class="fa-solid fa-shirt"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-label" style="font-size:0.85rem; color:var(--text-light);">إجمالي المنتجات</span>
                  <h3 class="stat-value" style="font-size:1.5rem; margin:4px 0 0; color:var(--text);">{{ $productsCount ?? 15 }}</h3>
                </div>
              </div>

              <div class="stat-card" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:20px; display:flex; align-items:center; gap:16px;">
                <div class="stat-icon bg-amber" style="width:52px; height:52px; border-radius:12px; background:rgba(245, 158, 11, 0.1); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
                  <i class="fa-solid fa-star"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-label" style="font-size:0.85rem; color:var(--text-light);">التقييمات الموثقة</span>
                  <h3 class="stat-value" style="font-size:1.5rem; margin:4px 0 0; color:var(--text);">{{ $reviewsCount ?? 6 }}</h3>
                </div>
              </div>
            </div>

            <!-- Recent Orders Snapshot Table -->
            <div class="analytics-card" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:24px; margin-bottom:24px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h4 style="margin:0;"><i class="fa-solid fa-clock-rotate-left" style="color:var(--primary);"></i> أحدث الطلبات المسجلة في MariaDB</h4>
                <button class="btn btn-secondary btn-sm" onclick="app.switchAdminTab('orders-tab')">عرض كافة الطلبات ←</button>
              </div>

              <div class="table-responsive" style="overflow-x:auto;">
                <table class="admin-table" style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr style="border-bottom:2px solid var(--border-color); text-align:right;">
                      <th style="padding:10px;">رقم الطلب</th>
                      <th style="padding:10px;">العميل</th>
                      <th style="padding:10px;">المدينة</th>
                      <th style="padding:10px;">المبلغ</th>
                      <th style="padding:10px;">طريقة الدفع</th>
                      <th style="padding:10px;">الحالة</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($recentOrders as $ord)
                      <tr style="border-bottom:1px solid var(--border-color);">
                        <td style="padding:12px; font-family:monospace; font-weight:700; color:var(--primary);">{{ $ord->order_number ?? 'ELITE-' . $ord->id }}</td>
                        <td style="padding:12px;">{{ $ord->customer_name }}</td>
                        <td style="padding:12px;">{{ $ord->city }}</td>
                        <td style="padding:12px; font-weight:700;">${{ number_format($ord->total, 2) }}</td>
                        <td style="padding:12px;">{{ strtoupper($ord->payment_method) }}</td>
                        <td style="padding:12px;">
                          <span class="badge" style="background:rgba(16, 185, 129, 0.1); color:#10b981; padding:4px 8px; border-radius:6px; font-size:0.8rem;">
                            {{ $ord->status }}
                          </span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="6" style="padding:24px; text-align:center; color:var(--text-light);">لا توجد طلبات مسجلة بعد</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

          </div>

          <!-- TAB 2: PRODUCTS MANAGEMENT -->
          <div id="products-tab" class="tab-content" style="display: none;">
            <div class="tab-actions">
              <div>
                <h3><i class="fa-solid fa-boxes-stacked"></i> قائمة المنتجات وإدارة المخزون الفوري</h3>
                <span style="font-size:0.85rem; color:var(--text-light);">تعديل كميات المخزون والأسعار مباشرة أو إضافة أزياء جديدة</span>
              </div>
              <div style="display:flex; gap:10px;">
                <button class="btn btn-secondary btn-sm" onclick="app.exportProductsCSV()"><i class="fa-solid fa-file-csv"></i> تصدير CSV</button>
                <button class="btn btn-primary btn-sm" onclick="app.openProductModal()"><i class="fa-solid fa-plus"></i> إضافة قطعة جديدة</button>
              </div>
            </div>

            <div class="table-responsive" style="overflow-x:auto; margin-top:16px;">
              <table class="admin-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr style="border-bottom:2px solid var(--border-color); text-align:right;">
                    <th style="padding:10px;">#</th>
                    <th style="padding:10px;">الصورة</th>
                    <th style="padding:10px;">اسم القطعة</th>
                    <th style="padding:10px;">التصنيف</th>
                    <th style="padding:10px;">السعر</th>
                    <th style="padding:10px;">المخزون</th>
                    <th style="padding:10px;">القصة</th>
                    <th style="padding:10px;">التقييم</th>
                    <th style="padding:10px;">الإجراء</th>
                  </tr>
                </thead>
                <tbody id="admin-products-table-body">
                  @foreach($products as $p)
                    <tr style="border-bottom:1px solid var(--border-color);">
                      <td style="padding:10px;">{{ $p->id }}</td>
                      <td style="padding:10px;">
                        <img src="{{ $p->image }}" alt="{{ $p->name_ar }}" style="width:40px; height:40px; object-fit:contain; border-radius:6px; border:1px solid var(--border-color);">
                      </td>
                      <td style="padding:10px; font-weight:700;">{{ $p->name_ar }}</td>
                      <td style="padding:10px;">{{ $p->category->name_ar ?? 'عام' }}</td>
                      <td style="padding:10px; color:var(--primary); font-weight:700;">${{ number_format($p->price, 2) }}</td>
                      <td style="padding:10px;">
                        <span class="stock-badge {{ $p->stock > 5 ? 'in-stock' : ($p->stock > 0 ? 'low-stock' : 'out-stock') }}">
                          {{ $p->stock }} قطعة
                        </span>
                      </td>
                      <td style="padding:10px;">{{ $p->fit_type ?? 'Modern' }}</td>
                      <td style="padding:10px;"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> {{ number_format($p->rating, 1) }}</td>
                      <td style="padding:10px;">
                        <button class="btn btn-secondary btn-sm" onclick="app.editProduct({{ $p->id }})" title="تعديل"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-danger btn-sm" onclick="app.deleteProduct({{ $p->id }})" title="حذف"><i class="fa-solid fa-trash"></i></button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 3: ORDERS MANAGEMENT -->
          <div id="orders-tab" class="tab-content" style="display: none;">
            <div class="tab-actions">
              <div>
                <h3><i class="fa-solid fa-receipt"></i> إدارة طلبات العملاء والشحن</h3>
                <span style="font-size:0.85rem; color:var(--text-light);">متابعة وتحديث حالات الشحن وتوليد الفواتير الضريبية</span>
              </div>
              <button class="btn btn-success btn-sm" onclick="app.exportOrdersCSV()"><i class="fa-solid fa-file-excel"></i> تصدير الطلبات</button>
            </div>
            <div class="table-responsive" style="overflow-x:auto; margin-top:16px;">
              <table class="admin-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr style="border-bottom:2px solid var(--border-color); text-align:right;">
                    <th style="padding:10px;">رقم الطلب</th>
                    <th style="padding:10px;">العميل</th>
                    <th style="padding:10px;">الهاتف</th>
                    <th style="padding:10px;">المدينة</th>
                    <th style="padding:10px;">الإجمالي</th>
                    <th style="padding:10px;">الحالة</th>
                    <th style="padding:10px;">إجراءات</th>
                  </tr>
                </thead>
                <tbody id="admin-orders-table-body">
                  @foreach($recentOrders as $ord)
                    <tr style="border-bottom:1px solid var(--border-color);">
                      <td style="padding:12px; font-family:monospace; color:var(--primary); font-weight:700;">{{ $ord->order_number ?? 'ELITE-' . $ord->id }}</td>
                      <td style="padding:12px;">{{ $ord->customer_name }}</td>
                      <td style="padding:12px; direction:ltr; text-align:right;">{{ $ord->customer_phone }}</td>
                      <td style="padding:12px;">{{ $ord->city }}</td>
                      <td style="padding:12px; font-weight:700;">${{ number_format($ord->total, 2) }}</td>
                      <td style="padding:12px;">
                        <select onchange="app.updateOrderStatus({{ $ord->id }}, this.value)" style="padding:4px 8px; border-radius:6px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit; font-size:0.82rem;">
                          <option value="pending" {{ $ord->status == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                          <option value="processing" {{ $ord->status == 'processing' ? 'selected' : '' }}>قيد التجهيز</option>
                          <option value="shipped" {{ $ord->status == 'shipped' ? 'selected' : '' }}>تم الشحن</option>
                          <option value="delivered" {{ $ord->status == 'delivered' ? 'selected' : '' }}>تم التوصيل</option>
                        </select>
                      </td>
                      <td style="padding:12px;">
                        <button class="btn btn-secondary btn-sm" onclick="app.showInvoiceModal({{ $ord->id }})"><i class="fa-solid fa-print"></i> فاتورة</button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 4: COUPONS -->
          <div id="coupons-tab" class="tab-content" style="display: none;">
            <div class="tab-actions">
              <div>
                <h3><i class="fa-solid fa-tags"></i> أكواد الخصم والكوبونات الترويجية</h3>
                <span style="font-size:0.85rem; color:var(--text-light);">إنشاء وإدارة حملات الخصم والوفورات التلقائية</span>
              </div>
              <button class="btn btn-primary btn-sm" onclick="app.openCouponModal()"><i class="fa-solid fa-plus"></i> كوبون جديد</button>
            </div>
            <div class="table-responsive" style="overflow-x:auto; margin-top:16px;">
              <table class="admin-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr style="border-bottom:2px solid var(--border-color); text-align:right;">
                    <th style="padding:10px;">كود الكوبون</th>
                    <th style="padding:10px;">نسبة الخصم</th>
                    <th style="padding:10px;">الحد الأدنى</th>
                    <th style="padding:10px;">أقصى خصم</th>
                    <th style="padding:10px;">الحالة</th>
                    <th style="padding:10px;">الإجراء</th>
                  </tr>
                </thead>
                <tbody id="admin-coupons-table-body">
                  @foreach($coupons as $c)
                    <tr style="border-bottom:1px solid var(--border-color);">
                      <td style="padding:12px; font-weight:700; color:var(--primary); font-family:monospace;">{{ $c->code }}</td>
                      <td style="padding:12px;">{{ $c->discount_percent }}%</td>
                      <td style="padding:12px;">${{ number_format($c->min_order ?? 0, 2) }}</td>
                      <td style="padding:12px;">${{ number_format($c->max_discount ?? 0, 2) }}</td>
                      <td style="padding:12px;">
                        <span class="badge" style="background:rgba(16, 185, 129, 0.1); color:#10b981; padding:4px 8px; border-radius:6px; font-size:0.8rem;">نشط</span>
                      </td>
                      <td style="padding:12px;">
                        <button class="btn btn-danger btn-sm" onclick="app.deleteCoupon({{ $c->id }})"><i class="fa-solid fa-trash"></i></button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 5: REVIEWS -->
          <div id="reviews-tab" class="tab-content" style="display: none;">
            <div class="tab-actions">
              <div>
                <h3><i class="fa-solid fa-star" style="color:#f59e0b;"></i> مراجعات وتقييمات العملاء الموثقة</h3>
                <span style="font-size:0.85rem; color:var(--text-light);">مراجعة التقييمات، الصور المرفقة، وعدد الأصوات المفيدة</span>
              </div>
            </div>
            <div class="table-responsive" style="overflow-x:auto; margin-top:16px;">
              <table class="admin-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr style="border-bottom:2px solid var(--border-color); text-align:right;">
                    <th style="padding:10px;">المنتج</th>
                    <th style="padding:10px;">العميل</th>
                    <th style="padding:10px;">التقييم</th>
                    <th style="padding:10px;">التعليق</th>
                    <th style="padding:10px;">الصورة</th>
                    <th style="padding:10px;">أصوات مفيدة</th>
                    <th style="padding:10px;">الإجراء</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentReviews as $rev)
                    <tr style="border-bottom:1px solid var(--border-color);">
                      <td style="padding:12px;">{{ $rev->product->name_ar ?? 'منتج #' . $rev->product_id }}</td>
                      <td style="padding:12px; font-weight:700;">{{ $rev->user_name }}</td>
                      <td style="padding:12px; color:#f59e0b;"><i class="fa-solid fa-star"></i> {{ $rev->rating }}/5</td>
                      <td style="padding:12px; max-width:280px;">{{ $rev->comment }}</td>
                      <td style="padding:12px;">
                        @if(!empty($rev->photo_url))
                          <img src="{{ $rev->photo_url }}" alt="مراجعة" style="width:36px; height:36px; border-radius:6px; object-fit:cover; cursor:pointer;" onclick="app.openReviewPhoto('{{ $rev->photo_url }}', '{{ addslashes($rev->comment) }}')">
                        @else
                          <span style="color:var(--text-light); font-size:0.8rem;">بدون صورة</span>
                        @endif
                      </td>
                      <td style="padding:12px;"><i class="fa-regular fa-thumbs-up"></i> {{ $rev->helpful_votes ?? 0 }}</td>
                      <td style="padding:12px;">
                        <button class="btn btn-danger btn-sm" onclick="app.deleteReview({{ $rev->id }})"><i class="fa-solid fa-trash"></i></button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 6: AUDIT TRAIL LOG -->
          <div id="audit-tab" class="tab-content" style="display: none;">
            <div class="tab-actions">
              <div>
                <h3><i class="fa-solid fa-clipboard-list" style="color:var(--primary);"></i> سجل العمليات والتدقيق الإداري (Audit Trail)</h3>
                <span style="font-size:0.85rem; color:var(--text-light);">سجل غير قابل للتعديل يوثق كافة إجراءات المشرفين، تعديل المخزون، والأسعار</span>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="app.exportAuditLogCSV()"><i class="fa-solid fa-download"></i> تصدير سجل التدقيق CSV</button>
            </div>
            <div class="table-responsive" style="overflow-x:auto; margin-top:16px;">
              <table class="admin-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr style="border-bottom:2px solid var(--border-color); text-align:right;">
                    <th style="padding:10px;">التوقيت</th>
                    <th style="padding:10px;">الفئة</th>
                    <th style="padding:10px;">المستخدم / المشرف</th>
                    <th style="padding:10px;">تفاصيل الإجراء</th>
                    <th style="padding:10px;">الحالة</th>
                  </tr>
                </thead>
                <tbody id="admin-audit-log-body">
                  <!-- Rendered dynamically -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 7: DATABASE TOOLS -->
          <div id="database-tab" class="tab-content" style="display: none;">
            <div class="tab-actions">
              <div>
                <h3><i class="fa-solid fa-database" style="color:var(--accent);"></i> إدارة محرك قاعدة البيانات MariaDB</h3>
                <span style="font-size:0.85rem; color:var(--text-light);">حالة الجداول، النسخ الاحتياطي، وإعادة زرع البيانات التجريبية</span>
              </div>
            </div>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-top:20px;">
              <div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:24px;">
                <h4><i class="fa-solid fa-rotate" style="color:var(--primary);"></i> إعادة زرع البيانات الافتراضية (Seed)</h4>
                <p style="font-size:0.85rem; color:var(--text-light); line-height:1.6; margin:8px 0 16px;">
                  إعادة إنشاء المنتجات، التصنيفات، والمراجعات التجريبية في MariaDB كما كانت عند التثبيت.
                </p>
                <button class="btn btn-secondary" onclick="app.resetMockDatabase()">
                  <i class="fa-solid fa-arrows-rotate"></i> إعادة زرع البيانات
                </button>
              </div>

              <div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:24px;">
                <h4><i class="fa-solid fa-server" style="color:#10b981;"></i> معلومات الخادم</h4>
                <div style="font-size:0.88rem; line-height:2; margin-top:12px;">
                  <div><strong>المحرك:</strong> MariaDB (MySQL Protocol)</div>
                  <div><strong>المنفذ:</strong> 3306</div>
                  <div><strong>قاعدة البيانات:</strong> ecommerce_store</div>
                  <div><strong>إصدار PHP:</strong> {{ PHP_VERSION }}</div>
                  <div><strong>إصدار Laravel:</strong> {{ app()->version() }}</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Common Modals for Admin -->
  <div class="modal" id="admin-product-modal">
    <div class="modal-overlay" onclick="app.closeModal('admin-product-modal')"></div>
    <div class="modal-content" style="max-width: 600px;">
      <button class="close-btn modal-close" onclick="app.closeModal('admin-product-modal')"><i class="fa-solid fa-xmark"></i></button>
      <h2 id="admin-product-modal-title"><i class="fa-solid fa-shirt"></i> إضافة قطعة أزياء جديدة</h2>
      <form onsubmit="app.handleSaveProduct(event)" class="admin-form" style="margin-top:16px;">
        <input type="hidden" id="edit-prod-id">
        <div class="form-group">
          <label>اسم المنتج بالعربية *</label>
          <input type="text" id="prod-name-ar" required placeholder="مثال: بدلة توكسيدو ملكية صوف">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>القسم / التصنيف *</label>
            <select id="prod-category-id" required>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>نوع القصة (Fit)</label>
            <select id="prod-fit">
              <option value="Slim Fit">Slim Fit (ممشوق)</option>
              <option value="Regular Fit">Regular Fit (كلاسيكي)</option>
              <option value="Modern Fit">Modern Fit (عصري)</option>
              <option value="Classic Cut">Classic Cut (تفصيل عربي)</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>السعر ($) *</label>
            <input type="number" step="0.01" id="prod-price" required placeholder="199.99">
          </div>
          <div class="form-group">
            <label>المخزون المتوفر *</label>
            <input type="number" id="prod-stock" required placeholder="10">
          </div>
        </div>
        <div class="form-group">
          <label>رابط الصورة المباشر</label>
          <input type="url" id="prod-image" placeholder="https://images.unsplash.com/...">
        </div>
        <div class="form-group">
          <label>الوصف بالعربية</label>
          <textarea id="prod-desc-ar" rows="3" placeholder="مواصفات القماش، الحياكة، والمناسبات..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-save"></i> حفظ القطعة في MariaDB</button>
      </form>
    </div>
  </div>

  <!-- ADMIN ADD COUPON MODAL -->
  <div class="modal" id="admin-coupon-modal">
    <div class="modal-overlay" onclick="app.closeModal('admin-coupon-modal')"></div>
    <div class="modal-content" style="max-width: 460px;">
      <button class="close-btn modal-close" onclick="app.closeModal('admin-coupon-modal')"><i class="fa-solid fa-xmark"></i></button>
      <h2><i class="fa-solid fa-tag"></i> إضافة كود خصم جديد</h2>
      <form onsubmit="app.handleSaveCoupon(event)" class="admin-form" style="margin-top:16px;">
        <div class="form-group">
          <label>كود الكوبون *</label>
          <input type="text" id="coupon-code" required placeholder="مثال: SUMMER25" style="text-transform:uppercase; font-weight:700;">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>نسبة الخصم (%) *</label>
            <input type="number" min="1" max="90" id="coupon-percent" required placeholder="25">
          </div>
          <div class="form-group">
            <label>الحد الأدنى للطلب ($)</label>
            <input type="number" step="0.01" id="coupon-min" placeholder="50.00" value="0">
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-plus"></i> إنشاء وتفعيل الكوبون</button>
      </form>
    </div>
  </div>

  <!-- INVOICE MODAL (A4 PRINTABLE) -->
  <div class="modal" id="admin-invoice-modal">
    <div class="modal-overlay" onclick="app.closeModal('admin-invoice-modal')"></div>
    <div class="modal-content invoice-modal-content" style="max-width: 850px; background:#fff; color:#0f172a;">
      <div id="invoice-printable-area">
        <!-- Rendered dynamically -->
      </div>
    </div>
  </div>

  <!-- Review Photo Modal -->
  <div id="review-photo-modal" class="modal">
    <div class="modal-dialog modal-md" style="max-width: 520px; text-align:center;">
      <div class="modal-header">
        <h3 id="review-photo-title" style="margin:0; font-size:1.1rem;"><i class="fa-solid fa-camera" style="color:var(--primary);"></i> صورة المراجعة الموثقة</h3>
        <button class="modal-close-btn" onclick="app.closeModal('review-photo-modal')"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <img id="review-photo-img" src="" alt="صورة المراجعة" style="width:100%; max-height:460px; object-fit:contain; border-radius:14px;">
        <p id="review-photo-caption" style="margin-top:14px; font-weight:700; color:#111; line-height:1.5;"></p>
      </div>
    </div>
  </div>

  <!-- Toast Notification System Container -->
  <div class="toast-container" id="toast-container"></div>

  <script src="/app.js"></script>
</body>
</html>
