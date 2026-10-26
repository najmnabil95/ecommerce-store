@extends('layouts.app')

@section('title', 'متجر النخبة | دار الأزياء والملابس الفاخرة')

@section('content')
<div class="view-section active">

  <!-- Hero Banner Card -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-card fashion-hero-card">
        <div class="hero-badge"><i class="fa-solid fa-crown"></i> دار الأزياء الفاخرة | صيف وخريف 2026</div>
        <h1 class="hero-title">أناقة رجالية وشبابية لا تضاهى بأرقى الأقمشة العالمية</h1>
        <p class="hero-desc">تشكيلة استثنائية من بدلات التوكسيدو المصممة من الصوف الإيطالي، الثياب الملكية المفصلة من قماش تويوبو الياباني الأصلي، والأزياء الشبابية الكاجوال العصرية مع ضمان الجودة 100%، مسترجعة ديناميكياً من قاعدة بيانات MariaDB.</p>
        <div class="hero-actions">
          <a href="{{ route('store.products') }}" class="btn btn-primary"><i class="fa-solid fa-shirt"></i> تصفح تشكيلة الملابس</a>
          <button class="btn btn-outline" onclick="app.scrollToSection('smart-calculator-section')" style="border-color:#8b5cf6; color:inherit;">
            <i class="fa-solid fa-wand-magic-sparkles" style="color:#a855f7;"></i> حاسبة المقاس الذكية
          </button>
          <button class="btn btn-outline" onclick="app.scrollToSection('cost-calculator-section')" style="border-color:#10b981; color:inherit;">
            <i class="fa-solid fa-coins" style="color:#10b981;"></i> حاسبة التكلفة والوفورات
          </button>
          <button class="btn btn-outline" onclick="app.openSizeGuideModal()"><i class="fa-solid fa-ruler-combined"></i> دليل المقاسات الشامل</button>
          <span class="hero-feature"><i class="fa-solid fa-truck-fast"></i> شحن سريع وضمان استرجاع 14 يوم</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Category Filter Bar -->
  <section class="categories-section">
    <div class="container">
      <div class="section-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h2><i class="fa-solid fa-layer-group"></i> تشكيلات وتصنيفات المتجر</h2>
        <a href="{{ route('store.products') }}" class="btn-text" style="color:var(--primary); text-decoration:none; font-weight:700;">
          عرض كافة الأقسام ({{ $categories->count() }}) ←
        </a>
      </div>
      <div class="categories-pills" id="categories-container" style="display:flex; gap:12px; flex-wrap:wrap; margin-top:14px;">
        <a href="{{ route('store.products') }}" class="category-pill {{ !request('category') ? 'active' : '' }}">
          <i class="fa-solid fa-border-all"></i> جميع القطع
        </a>
        @foreach($categories as $cat)
          <a href="{{ route('store.category', $cat->slug) }}" class="category-pill {{ request('category') == $cat->slug ? 'active' : '' }}">
            <i class="fa-solid {{ $cat->icon ?: 'fa-tag' }}"></i> {{ $cat->name_ar }} ({{ $cat->products_count ?? 0 }})
          </a>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Main Products Catalog -->
  <section class="products-section" id="products-section" style="margin-top:40px;">
    <div class="container">
      <div class="products-controls">
        <div class="results-info">
          <h3 style="margin:0;"><i class="fa-solid fa-sparkles" style="color:var(--accent);"></i> أحدث التشكيلات المعروضة</h3>
          <span id="products-count" style="font-size:0.85rem; color:var(--text-light);">عرض {{ $products->count() }} قطعة متوفرة حالياً</span>
        </div>
        <div class="sort-controls" style="display:flex; gap:12px; align-items:center;">
          <a href="{{ route('store.products') }}" class="btn btn-secondary" style="padding:7px 14px; font-size:0.88rem; text-decoration:none;">
            <i class="fa-solid fa-sliders"></i> تصفية متقدمة وفلترة
          </a>
          <a href="{{ route('store.compare') }}" class="btn btn-outline" style="padding:7px 14px; font-size:0.88rem; text-decoration:none;">
            <i class="fa-solid fa-code-compare"></i> صفحة المقارنة
          </a>
        </div>
      </div>

      <!-- Server Rendered Dynamic Products Grid -->
      <div class="products-grid" id="products-grid">
        @foreach($products as $prod)
          <div class="product-card" data-id="{{ $prod->id }}">
            <div class="product-image-container">
              <a href="{{ route('store.product.show', $prod->id) }}">
                <img src="{{ $prod->image }}" alt="{{ $prod->name_ar }}" class="product-image" loading="lazy">
              </a>
              @if(!empty($prod->badge))
                <span class="product-badge">{{ $prod->badge }}</span>
              @endif
              
              <!-- Quick Action Overlay -->
              <div class="product-actions-overlay">
                <button class="action-icon-btn" onclick="app.toggleWishlist({{ $prod->id }})" title="إضافة للمفضلة">
                  <i class="fa-solid fa-heart"></i>
                </button>
                <button class="action-icon-btn" onclick="app.openQuickViewModal({{ $prod->id }})" title="معاينة سريعة">
                  <i class="fa-solid fa-eye"></i>
                </button>
                <button class="action-icon-btn compare-btn" onclick="app.toggleCompareProduct({{ $prod->id }})" title="مقارنة المواصفات">
                  <i class="fa-solid fa-code-compare"></i>
                </button>
              </div>
            </div>

            <div class="product-info">
              <div class="product-meta">
                <span class="product-category"><i class="fa-solid fa-tag"></i> {{ $prod->category->name_ar ?? 'عام' }}</span>
                @if($prod->fit_type)
                  <span class="product-fit-tag">{{ $prod->fit_type }}</span>
                @endif
              </div>

              <h4 class="product-title">
                <a href="{{ route('store.product.show', $prod->id) }}" style="color:inherit; text-decoration:none;">
                  {{ $prod->name_ar }}
                </a>
              </h4>

              <div class="product-rating">
                <div class="stars">
                  @for($i = 1; $i <= 5; $i++)
                    <i class="fa-{{ $i <= round($prod->rating) ? 'solid' : 'regular' }} fa-star"></i>
                  @endfor
                </div>
                <span class="rating-value">{{ number_format($prod->rating, 1) }} ({{ $prod->reviews_count ?? $prod->reviews->count() }})</span>
              </div>

              <div class="product-price-row">
                <div class="price-box">
                  <span class="price-current">${{ number_format($prod->price, 2) }}</span>
                  @if($prod->old_price && $prod->old_price > $prod->price)
                    <span class="price-old">${{ number_format($prod->old_price, 2) }}</span>
                  @endif
                </div>
                <span class="stock-badge {{ $prod->stock > 5 ? 'in-stock' : ($prod->stock > 0 ? 'low-stock' : 'out-stock') }}">
                  {{ $prod->stock > 5 ? 'متوفر' : ($prod->stock > 0 ? 'متبقي ' . $prod->stock : 'نفد') }}
                </span>
              </div>

              <div class="product-card-footer">
                <button class="btn btn-primary btn-block" onclick="app.addToCartDirect({{ $prod->id }})" {{ $prod->stock <= 0 ? 'disabled' : '' }}>
                  <i class="fa-solid fa-cart-plus"></i> إضافة للسلة
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>

    </div>
  </section>

  <!-- SMART SIZE RECOMMENDER SECTION (حاسبة المقاسات التفاعلية) -->
  <section class="smart-calculator-section" id="smart-calculator-section" style="margin-top:60px;">
    <div class="container">
      <div class="calculator-card">
        <div class="calculator-header">
          <div class="calc-icon-badge"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
          <div>
            <h2>حاسبة المقاس الذكية والفورية</h2>
            <p>حدد طولك ووزنك وقصتك المفضلة، وسيقترح عليك النظام المقاس المعتمد لجميع الأزياء بدقة 98%</p>
          </div>
        </div>

        <div class="calculator-body">
          <div class="calc-controls-grid">
            <div class="calc-input-group">
              <label><i class="fa-solid fa-arrows-up-down"></i> الطول التقريبي: <span id="home-height-display" class="calc-highlight-val">175</span> سم</label>
              <input type="range" id="home-height" min="150" max="205" value="175" oninput="document.getElementById('home-height-display').innerText = this.value; app.updateHomeSmartCalc();" style="width:100%; accent-color:var(--primary);">
            </div>
            <div class="calc-input-group">
              <label><i class="fa-solid fa-weight-scale"></i> الوزن التقريبي: <span id="home-weight-display" class="calc-highlight-val">75</span> كجم</label>
              <input type="range" id="home-weight" min="45" max="130" value="75" oninput="document.getElementById('home-weight-display').innerText = this.value; app.updateHomeSmartCalc();" style="width:100%; accent-color:var(--primary);">
            </div>
          </div>

          <div class="calc-fit-selector" style="margin-top:20px;">
            <label style="font-weight:700; margin-bottom:8px; display:block;"><i class="fa-solid fa-shirt"></i> تفضيل القصة:</label>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
              <button type="button" class="btn btn-outline active" id="btn-fit-slim" onclick="app.setHomeCalcFit('slim', this)">ممشوق (Slim Fit)</button>
              <button type="button" class="btn btn-outline" id="btn-fit-regular" onclick="app.setHomeCalcFit('regular', this)">كلاسيكي (Regular Fit)</button>
              <button type="button" class="btn btn-outline" id="btn-fit-loose" onclick="app.setHomeCalcFit('loose', this)">واسع ومريح (Loose / Oversized)</button>
            </div>
          </div>

          <!-- Recommendation Result Box -->
          <div class="calc-result-box" style="margin-top:24px; padding:20px; border-radius:14px; background:var(--input-bg); border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
              <span style="font-size:0.85rem; color:var(--text-light);">المقاس الموصى به لك:</span>
              <div style="display:flex; align-items:baseline; gap:10px; margin-top:4px;">
                <h3 id="home-calc-result" style="font-size:2rem; color:var(--primary); margin:0;">L (كبير)</h3>
                <span id="home-calc-suit" style="font-size:1.1rem; color:var(--text-light);">(بدلات: 50 EU | ثياب: 58 بوصة)</span>
              </div>
            </div>
            <button class="btn btn-primary" onclick="app.filterByRecommendedSize()">
              <i class="fa-solid fa-filter"></i> تصفية المنتجات بمقاسي الموصى به
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SMART COST & SAVINGS CALCULATOR SECTION -->
  <section class="cost-calculator-section" id="cost-calculator-section" style="margin-top:60px;">
    <div class="container">
      <div class="calculator-card" style="border-right: 4px solid #10b981;">
        <div class="calculator-header">
          <div class="calc-icon-badge" style="background:linear-gradient(135deg, #059669 0%, #10b981 100%);"><i class="fa-solid fa-coins"></i></div>
          <div>
            <h2>حاسبة التكلفة والوفورات المجمعة الذكية 💰</h2>
            <p>اختر حزمتك المخصصة من البدلات، الثياب، والقمصان وشاهد فورياً توفير الباقات والشحن المجاني التلقائي</p>
          </div>
        </div>

        <div class="calculator-body">
          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div class="cost-item-box" style="padding:16px; border:1px solid var(--border-color); border-radius:12px;">
              <label><strong>بدلات توكسيدو ملكية</strong></label>
              <div style="font-size:0.8rem; color:var(--text-light); margin-bottom:8px;">$340 / قطعة</div>
              <input type="number" id="cost-suit-qty" min="0" max="10" value="1" onchange="app.calculateCostTotal()" style="width:100%; padding:8px; border-radius:8px; border:1px solid var(--border-color);">
            </div>
            <div class="cost-item-box" style="padding:16px; border:1px solid var(--border-color); border-radius:12px;">
              <label><strong>ثياب تويوبو مفصلة</strong></label>
              <div style="font-size:0.8rem; color:var(--text-light); margin-bottom:8px;">$145 / قطعة</div>
              <input type="number" id="cost-thobe-qty" min="0" max="10" value="2" onchange="app.calculateCostTotal()" style="width:100%; padding:8px; border-radius:8px; border:1px solid var(--border-color);">
            </div>
            <div class="cost-item-box" style="padding:16px; border:1px solid var(--border-color); border-radius:12px;">
              <label><strong>قمصان قطنية فاخرة</strong></label>
              <div style="font-size:0.8rem; color:var(--text-light); margin-bottom:8px;">$65 / قطعة</div>
              <input type="number" id="cost-shirt-qty" min="0" max="10" value="2" onchange="app.calculateCostTotal()" style="width:100%; padding:8px; border-radius:8px; border:1px solid var(--border-color);">
            </div>
          </div>

          <div class="cost-result-summary" style="margin-top:20px; padding:20px; border-radius:14px; background:var(--card-bg); border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
              <div style="font-size:0.9rem; color:var(--text-light);">الإجمالي بعد خصم الباقة الحصرية:</div>
              <h3 id="cost-total-display" style="font-size:2.2rem; color:#10b981; margin:4px 0 0;">$684.00</h3>
              <span id="cost-savings-badge" class="badge-discount" style="background:#dcfce7; color:#15803d; padding:3px 8px; border-radius:6px; font-weight:700; font-size:0.82rem;">وفرت $76.00 (10% خصم باقة) + شحن مجاني!</span>
            </div>
            <button class="btn btn-primary" onclick="app.addCostCalculatedBundleToCart()" style="background:#059669; border-color:#059669;">
              <i class="fa-solid fa-cart-shopping"></i> إضافة هذه الحزمة مباشرة للسلة
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>
@endsection
