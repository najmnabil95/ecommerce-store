@extends('layouts.app')

@section('title', 'كتالوج الأزياء والملابس | متجر النخبة')

@section('content')
<div class="view-section active" style="padding-top:20px;">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-trail" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-light); margin-bottom:20px;">
      <a href="{{ route('store.home') }}" style="color:inherit; text-decoration:none;"><i class="fa-solid fa-house"></i> الرئيسية</a>
      <span>/</span>
      <span style="color:var(--text); font-weight:700;">كتالوج المنتجات</span>
      @if(isset($category))
        <span>/</span>
        <span style="color:var(--primary); font-weight:700;">{{ $category->name_ar }}</span>
      @endif
    </nav>

    <div class="catalog-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:30px; align-items:start;">
      
      <!-- Filter Sidebar Form -->
      <aside class="catalog-sidebar" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:14px; padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
          <h3 style="margin:0; font-size:1.1rem;"><i class="fa-solid fa-sliders" style="color:var(--primary);"></i> تصفية المنتجات</h3>
          <a href="{{ route('store.products') }}" style="font-size:0.8rem; color:var(--danger); text-decoration:none;">إعادة ضبط</a>
        </div>

        <form action="{{ route('store.products') }}" method="GET" id="catalog-filter-form">
          
          <!-- Category Filter -->
          <div class="filter-group" style="margin-bottom:20px;">
            <label style="font-weight:700; font-size:0.9rem; display:block; margin-bottom:8px;"><i class="fa-solid fa-layer-group"></i> التصنيف</label>
            <select name="category" class="filter-select" onchange="this.form.submit()" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit;">
              <option value="all">جميع التصنيفات</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->slug }}" {{ (request('category') == $cat->slug || (isset($category) && $category->id == $cat->id)) ? 'selected' : '' }}>
                  {{ $cat->name_ar }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Size Filter -->
          <div class="filter-group" style="margin-bottom:20px;">
            <label style="font-weight:700; font-size:0.9rem; display:block; margin-bottom:8px;"><i class="fa-solid fa-ruler"></i> المقاس</label>
            <select name="size" class="filter-select" onchange="this.form.submit()" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit;">
              <option value="all">جميع المقاسات</option>
              <optgroup label="مقاسات الحروف">
                <option value="S" {{ request('size') == 'S' ? 'selected' : '' }}>S (سمول)</option>
                <option value="M" {{ request('size') == 'M' ? 'selected' : '' }}>M (وسط)</option>
                <option value="L" {{ request('size') == 'L' ? 'selected' : '' }}>L (كبير)</option>
                <option value="XL" {{ request('size') == 'XL' ? 'selected' : '' }}>XL (كبير جداً)</option>
                <option value="XXL" {{ request('size') == 'XXL' ? 'selected' : '' }}>XXL (دبل إكس)</option>
              </optgroup>
              <optgroup label="مقاسات البدلات (EU)">
                <option value="48" {{ request('size') == '48' ? 'selected' : '' }}>48 EU</option>
                <option value="50" {{ request('size') == '50' ? 'selected' : '' }}>50 EU</option>
                <option value="52" {{ request('size') == '52' ? 'selected' : '' }}>52 EU</option>
                <option value="54" {{ request('size') == '54' ? 'selected' : '' }}>54 EU</option>
              </optgroup>
              <optgroup label="مقاسات الثياب (بوصة)">
                <option value="56" {{ request('size') == '56' ? 'selected' : '' }}>56 بوصة</option>
                <option value="58" {{ request('size') == '58' ? 'selected' : '' }}>58 بوصة</option>
                <option value="60" {{ request('size') == '60' ? 'selected' : '' }}>60 بوصة</option>
              </optgroup>
            </select>
          </div>

          <!-- Fit Type Filter -->
          <div class="filter-group" style="margin-bottom:20px;">
            <label style="font-weight:700; font-size:0.9rem; display:block; margin-bottom:8px;"><i class="fa-solid fa-scissors"></i> نوع القصة (Fit)</label>
            <select name="fit" class="filter-select" onchange="this.form.submit()" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit;">
              <option value="all">كافة القصات</option>
              <option value="Slim Fit" {{ request('fit') == 'Slim Fit' ? 'selected' : '' }}>Slim Fit (ممشوق متناسق)</option>
              <option value="Regular Fit" {{ request('fit') == 'Regular Fit' ? 'selected' : '' }}>Regular Fit (كلاسيكي)</option>
              <option value="Modern Fit" {{ request('fit') == 'Modern Fit' ? 'selected' : '' }}>Modern Fit (عصري متوازن)</option>
              <option value="Classic Cut" {{ request('fit') == 'Classic Cut' ? 'selected' : '' }}>Classic Cut (تفصيل عربي)</option>
              <option value="Oversized" {{ request('fit') == 'Oversized' ? 'selected' : '' }}>Oversized (شبابي واسع)</option>
            </select>
          </div>

          <!-- Max Price Filter -->
          <div class="filter-group" style="margin-bottom:20px;">
            <label style="font-weight:700; font-size:0.9rem; display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span><i class="fa-solid fa-tags"></i> السعر حتى:</span>
              <span id="price-val-indicator" style="color:var(--primary);">${{ request('max_price', 1000) }}</span>
            </label>
            <input type="range" name="max_price" min="20" max="1000" step="10" value="{{ request('max_price', 1000) }}" oninput="document.getElementById('price-val-indicator').innerText = '$' + this.value" onchange="this.form.submit()" style="width:100%; accent-color:var(--primary);">
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-light); margin-top:4px;">
              <span>$20</span>
              <span>$1000</span>
            </div>
          </div>

          <!-- In Stock Only -->
          <div class="filter-group" style="margin-bottom:24px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.9rem; font-weight:600;">
              <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} onchange="this.form.submit()" style="width:16px; height:16px; accent-color:var(--primary);">
              <span>المتوفر في المخزون فقط</span>
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-block" style="padding:10px;">
            <i class="fa-solid fa-filter"></i> تطبيق التصفية
          </button>
        </form>
      </aside>

      <!-- Main Products Listing -->
      <section class="catalog-main">
        
        <!-- Controls Bar -->
        <div class="products-controls" style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; background:var(--card-bg); padding:16px 20px; border-radius:12px; border:1px solid var(--border-color);">
          <div class="results-info">
            <strong>تم العثور على <span style="color:var(--primary);">{{ $products->count() }}</span> قطعة مطابقة</strong>
            @if(request('search'))
              <span style="font-size:0.85rem; color:var(--text-light); margin-right:8px;">للبحث: "{{ request('search') }}"</span>
            @endif
          </div>

          <div style="display:flex; align-items:center; gap:10px;">
            <label for="catalog-sort" style="font-size:0.85rem; color:var(--text-light);"><i class="fa-solid fa-arrow-down-short-wide"></i> ترتيب:</label>
            <select id="catalog-sort" onchange="const url = new URL(window.location); url.searchParams.set('sort', this.value); window.location = url.toString();" style="padding:7px 12px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit; font-size:0.88rem;">
              <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>الأحدث أولاً</option>
              <option value="price-low" {{ request('sort') == 'price-low' ? 'selected' : '' }}>السعر: من الأقل للأعلى</option>
              <option value="price-high" {{ request('sort') == 'price-high' ? 'selected' : '' }}>السعر: من الأعلى للأقل</option>
              <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>الأعلى تقييماً</option>
            </select>
          </div>
        </div>

        @if($products->isEmpty())
          <div class="empty-state-box" style="text-align:center; padding:60px 20px; background:var(--card-bg); border-radius:14px; border:1px solid var(--border-color);">
            <div style="font-size:3rem; color:var(--text-light); margin-bottom:14px;"><i class="fa-solid fa-shirt"></i></div>
            <h3>لم يتم العثور على منتجات مطابقة للفلترة</h3>
            <p style="color:var(--text-light); max-width:400px; margin:0 auto 20px;">جرّب تعديل معايير البحث أو تصفية المقاس والسعر لتظهر لك خيارات أكثر.</p>
            <a href="{{ route('store.products') }}" class="btn btn-primary">عرض جميع المنتجات</a>
          </div>
        @else
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
                    <span class="rating-value">{{ number_format($prod->rating, 1) }} ({{ $prod->reviews->count() }})</span>
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
        @endif

      </section>

    </div>
  </div>
</div>
@endsection
