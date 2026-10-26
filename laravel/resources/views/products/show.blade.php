@extends('layouts.app')

@section('title', $product->name_ar . ' | متجر النخبة')

@section('content')
<div class="view-section active" style="padding-top:20px;">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-trail" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-light); margin-bottom:24px;">
      <a href="{{ route('store.home') }}" style="color:inherit; text-decoration:none;"><i class="fa-solid fa-house"></i> الرئيسية</a>
      <span>/</span>
      <a href="{{ route('store.products') }}" style="color:inherit; text-decoration:none;">المنتجات</a>
      <span>/</span>
      <a href="{{ route('store.category', $product->category->slug ?? 'fashion') }}" style="color:inherit; text-decoration:none;">{{ $product->category->name_ar ?? 'عام' }}</a>
      <span>/</span>
      <span style="color:var(--text); font-weight:700;">{{ $product->name_ar }}</span>
    </nav>

    <!-- Product Details Main Layout -->
    <div class="product-detail-layout" style="display:grid; grid-template-columns: 1fr 1fr; gap:40px; background:var(--card-bg); border:1px solid var(--border-color); border-radius:16px; padding:32px; margin-bottom:40px;">
      
      <!-- Gallery Column -->
      <div class="product-detail-gallery">
        <div class="main-image-wrap" style="position:relative; border-radius:12px; overflow:hidden; border:1px solid var(--border-color); background:#fff;">
          <img id="detail-main-img" src="{{ $product->image }}" alt="{{ $product->name_ar }}" style="width:100%; height:480px; object-fit:contain; transition:transform 0.3s ease;">
          @if(!empty($product->badge))
            <span class="product-badge" style="position:absolute; top:16px; right:16px; font-size:0.9rem;">{{ $product->badge }}</span>
          @endif
        </div>
        
        <!-- Thumbnails -->
        <div class="gallery-thumbs" style="display:flex; gap:12px; margin-top:14px;">
          <img src="{{ $product->image }}" alt="صورة 1" onclick="document.getElementById('detail-main-img').src = this.src" style="width:75px; height:75px; object-fit:cover; border-radius:8px; border:2px solid var(--primary); cursor:pointer;">
          @if(!empty($product->colors) && is_array($product->colors))
            @foreach($product->colors as $col)
              @php
                $colName = is_array($col) ? ($col['name'] ?? '') : $col;
                $colHex = is_array($col) ? ($col['hex'] ?? '#0f172a') : '#0f172a';
              @endphp
              <div style="width:75px; height:75px; border-radius:8px; border:1px solid var(--border-color); display:flex; flex-direction:column; align-items:center; justify-content:center; background:var(--input-bg); cursor:pointer; font-size:0.75rem;" title="{{ $colName }}">
                <span style="display:inline-block; width:22px; height:22px; border-radius:50%; border:1px solid #ccc; background:{{ $colHex }};"></span>
                <span style="margin-top:4px; font-weight:600;">{{ $colName }}</span>
              </div>
            @endforeach
          @endif
        </div>
      </div>

      <!-- Info Column -->
      <div class="product-detail-info">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
          <div>
            <span class="product-category" style="font-size:0.9rem; color:var(--primary); font-weight:700;"><i class="fa-solid fa-tag"></i> {{ $product->category->name_ar ?? 'عام' }}</span>
            <h1 style="font-size:1.8rem; margin:8px 0; color:var(--text); line-height:1.3;">{{ $product->name_ar }}</h1>
            <span style="font-size:0.85rem; color:var(--text-light); font-family:monospace;">كود المنتج: ELITE-{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}</span>
          </div>

          <button class="action-icon-btn compare-btn" onclick="app.toggleCompareProduct({{ $product->id }})" title="مقارنة بالمنتجات الأخرى" style="width:44px; height:44px; border-radius:10px; font-size:1.1rem;">
            <i class="fa-solid fa-code-compare"></i>
          </button>
        </div>

        <!-- Rating & Reviews Count -->
        <div style="display:flex; align-items:center; gap:12px; margin:16px 0;">
          <div class="stars" style="color:#f59e0b; font-size:1.1rem;">
            @for($i = 1; $i <= 5; $i++)
              <i class="fa-{{ $i <= round($product->rating) ? 'solid' : 'regular' }} fa-star"></i>
            @endfor
          </div>
          <strong style="font-size:1.1rem;">{{ number_format($product->rating, 1) }}</strong>
          <span style="color:var(--text-light); font-size:0.9rem;">({{ $product->reviews->count() }} تقييم موثق)</span>
          <span class="stock-badge {{ $product->stock > 5 ? 'in-stock' : ($product->stock > 0 ? 'low-stock' : 'out-stock') }}">
            {{ $product->stock > 5 ? 'متوفر بالمخزون' : ($product->stock > 0 ? 'متبقي ' . $product->stock . ' قطع فقط' : 'نفد من المخزون') }}
          </span>
        </div>

        <!-- Price Row -->
        <div class="price-box" style="display:flex; align-items:baseline; gap:14px; margin:18px 0; padding:16px; border-radius:10px; background:var(--input-bg);">
          <span style="font-size:2.2rem; font-weight:800; color:var(--primary);">${{ number_format($product->price, 2) }}</span>
          @if($product->old_price && $product->old_price > $product->price)
            <span style="font-size:1.2rem; text-decoration:line-through; color:var(--text-light);">${{ number_format($product->old_price, 2) }}</span>
            <span style="background:var(--danger); color:#fff; font-size:0.8rem; font-weight:700; padding:3px 8px; border-radius:6px;">
              وفر {{ round((($product->old_price - $product->price) / $product->old_price) * 100) }}%
            </span>
          @endif
          <span style="font-size:0.85rem; color:var(--text-light); margin-right:auto;">(شامل ضريبة القيمة المضافة 15%)</span>
        </div>

        <!-- Description -->
        <p style="color:var(--text-light); line-height:1.7; font-size:0.95rem; margin-bottom:20px;">
          {{ $product->description_ar }}
        </p>

        <!-- Garment Specifications Specs Table -->
        <div style="background:var(--input-bg); border:1px solid var(--border-color); border-radius:10px; padding:14px 18px; margin-bottom:20px;">
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; font-size:0.88rem;">
            <div><strong><i class="fa-solid fa-scissors"></i> نوع القصة:</strong> {{ $product->fit_type ?? 'Modern Fit' }}</div>
            <div><strong><i class="fa-solid fa-scroll"></i> خامة القماش:</strong> {{ $product->fabric ?? 'صوف وألياف إيطالية فاخرة' }}</div>
            <div><strong><i class="fa-solid fa-shield-halved"></i> الضمان:</strong> استرجاع مجاني 14 يوماً</div>
            <div><strong><i class="fa-solid fa-truck-fast"></i> الشحن:</strong> توصيل سريع خلال 2-4 أيام</div>
          </div>
        </div>

        <!-- Size Selection -->
        @if(!empty($product->sizes) && is_array($product->sizes))
          <div style="margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <label style="font-weight:700; font-size:0.9rem;"><i class="fa-solid fa-ruler"></i> اختر المقاس:</label>
              <button class="btn-text" onclick="app.openSizeGuideModal()" style="font-size:0.8rem; color:var(--primary); text-decoration:underline;">
                <i class="fa-solid fa-ruler-combined"></i> دليل المقاسات
              </button>
            </div>
            <div class="size-chips" style="display:flex; gap:8px; flex-wrap:wrap;">
              @foreach($product->sizes as $idx => $s)
                <button type="button" class="btn btn-outline {{ $idx === 0 ? 'active' : '' }}" onclick="document.querySelectorAll('.size-chips button').forEach(b => b.classList.remove('active')); this.classList.add('active');" style="padding:6px 16px; font-weight:700;">
                  {{ $s }}
                </button>
              @endforeach
            </div>
          </div>
        @endif

        <!-- Add to Cart & Actions -->
        <div style="display:flex; gap:14px; margin-top:24px;">
          <div style="display:flex; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; background:var(--input-bg);">
            <button type="button" onclick="const q = document.getElementById('detail-qty'); if(q.value > 1) q.value--;" style="padding:10px 16px; border:none; background:none; cursor:pointer;"><i class="fa-solid fa-minus"></i></button>
            <input type="number" id="detail-qty" value="1" min="1" max="{{ $product->stock }}" style="width:50px; text-align:center; border:none; background:none; font-weight:700;">
            <button type="button" onclick="const q = document.getElementById('detail-qty'); if(q.value < {{ $product->stock }}) q.value++;" style="padding:10px 16px; border:none; background:none; cursor:pointer;"><i class="fa-solid fa-plus"></i></button>
          </div>

          <button class="btn btn-primary" onclick="app.addToCartWithQty({{ $product->id }}, parseInt(document.getElementById('detail-qty').value))" style="flex:1; padding:12px 24px; font-size:1.05rem;" {{ $product->stock <= 0 ? 'disabled' : '' }}>
            <i class="fa-solid fa-cart-plus"></i> إضافة إلى سلة التسوق
          </button>

          <button class="btn btn-outline" onclick="app.toggleWishlist({{ $product->id }})" title="إضافة للمفضلة" style="padding:12px 18px;">
            <i class="fa-solid fa-heart"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- VERIFIED REVIEWS WITH PHOTOS & HELPFUL VOTES SECTION -->
    <section class="product-reviews-section" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:16px; padding:32px; margin-bottom:40px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
        <div>
          <h2 style="margin:0;"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> آراء وتقييمات العملاء الموثقة</h2>
          <span style="font-size:0.88rem; color:var(--text-light);">تجارب حقيقية موثقة بالشراء والصور من عملاء المتجر</span>
        </div>
        <button class="btn btn-outline btn-sm" onclick="app.openAddReviewModal({{ $product->id }})">
          <i class="fa-solid fa-pen-to-square"></i> إضافة تقييمك
        </button>
      </div>

      <!-- Reviews Overview Bar -->
      <div style="display:grid; grid-template-columns: 240px 1fr; gap:30px; margin-bottom:30px; background:var(--input-bg); padding:20px; border-radius:12px;">
        <div style="text-align:center; border-left:1px solid var(--border-color); padding-left:20px;">
          <h1 style="font-size:3rem; margin:0; color:var(--primary); font-weight:800;">{{ number_format($product->rating, 1) }}</h1>
          <div class="stars" style="color:#f59e0b; margin:6px 0;">
            @for($i = 1; $i <= 5; $i++)
              <i class="fa-{{ $i <= round($product->rating) ? 'solid' : 'regular' }} fa-star"></i>
            @endfor
          </div>
          <span style="font-size:0.85rem; color:var(--text-light);">بناءً على {{ $product->reviews->count() }} مراجعة معتمدة</span>
        </div>

        <div>
          <strong style="display:block; margin-bottom:10px; font-size:0.9rem;">الوسوم الأكثر تكراراً في تجارب المشترين:</strong>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <span class="badge" style="background:rgba(37, 99, 235, 0.1); color:var(--primary); padding:6px 12px; border-radius:20px; font-size:0.82rem;"><i class="fa-solid fa-check"></i> خامة ممتازة 100%</span>
            <span class="badge" style="background:rgba(16, 185, 129, 0.1); color:var(--success); padding:6px 12px; border-radius:20px; font-size:0.82rem;"><i class="fa-solid fa-ruler"></i> المقاس مطابق للجدول</span>
            <span class="badge" style="background:rgba(245, 158, 11, 0.1); color:var(--accent); padding:6px 12px; border-radius:20px; font-size:0.82rem;"><i class="fa-solid fa-truck-fast"></i> توصيل سريع جداً</span>
            <span class="badge" style="background:rgba(124, 58, 237, 0.1); color:#7c3aed; padding:6px 12px; border-radius:20px; font-size:0.82rem;"><i class="fa-solid fa-shirt"></i> تفصيل وقصة مريحة</span>
          </div>
        </div>
      </div>

      <!-- Reviews Listing -->
      <div class="reviews-list-container">
        @if($product->reviews->isEmpty())
          <div style="text-align:center; padding:30px; color:var(--text-light);">
            <i class="fa-regular fa-comments" style="font-size:2.5rem; margin-bottom:10px; display:block;"></i>
            كن أول من يقيّم هذا المنتج الرائع!
          </div>
        @else
          @foreach($product->reviews as $rev)
            <div class="review-item-card" style="border-bottom:1px solid var(--border-color); padding:20px 0;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                <div style="display:flex; align-items:center; gap:10px;">
                  <div style="width:40px; height:40px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">
                    {{ mb_substr($rev->user_name, 0, 1) }}
                  </div>
                  <div>
                    <strong style="display:block; font-size:0.95rem;">{{ $rev->user_name }}</strong>
                    <div style="display:flex; align-items:center; gap:8px;">
                      <div class="stars" style="color:#f59e0b; font-size:0.85rem;">
                        @for($i = 1; $i <= 5; $i++)
                          <i class="fa-{{ $i <= $rev->rating ? 'solid' : 'regular' }} fa-star"></i>
                        @endfor
                      </div>
                      @if($rev->verified_purchase ?? true)
                        <span style="font-size:0.75rem; color:#10b981; font-weight:700;"><i class="fa-solid fa-circle-check"></i> عملية شراء موثقة</span>
                      @endif
                    </div>
                  </div>
                </div>
                <span style="font-size:0.8rem; color:var(--text-light);">{{ $rev->created_at ? $rev->created_at->diffForHumans() : 'مؤخراً' }}</span>
              </div>

              <p style="color:var(--text); line-height:1.6; margin:8px 0; font-size:0.95rem;">
                {{ $rev->comment }}
              </p>

              <!-- Photo Attached -->
              @if(!empty($rev->photo_url))
                <div style="margin:12px 0;">
                  <img src="{{ $rev->photo_url }}" alt="صورة من العميل" onclick="app.openReviewPhoto('{{ $rev->photo_url }}', '{{ addslashes($rev->comment) }}')" style="width:85px; height:85px; object-fit:cover; border-radius:10px; border:1px solid var(--border-color); cursor:pointer;">
                </div>
              @endif

              <!-- Helpful Vote Button -->
              <div style="display:flex; align-items:center; gap:12px; margin-top:10px;">
                <button type="button" class="btn-text-sm" onclick="app.voteReviewHelpful({{ $rev->id }}, this)" style="background:none; border:none; cursor:pointer; color:var(--text-light); font-size:0.82rem;">
                  <i class="fa-regular fa-thumbs-up"></i> مفيد (<span class="helpful-count">{{ $rev->helpful_votes ?? 0 }}</span>)
                </button>
              </div>
            </div>
          @endforeach
        @endif
      </div>
    </section>

    <!-- Related Products -->
    @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
      <section class="related-products-section" style="margin-bottom:60px;">
        <div class="section-header" style="margin-bottom:20px;">
          <h3><i class="fa-solid fa-shirt" style="color:var(--primary);"></i> منتجات قد تعجبك أيضاً من نفس التشكيلة</h3>
        </div>
        <div class="products-grid">
          @foreach($relatedProducts as $rel)
            <div class="product-card" data-id="{{ $rel->id }}">
              <div class="product-image-container">
                <a href="{{ route('store.product.show', $rel->id) }}">
                  <img src="{{ $rel->image }}" alt="{{ $rel->name_ar }}" class="product-image">
                </a>
              </div>
              <div class="product-info">
                <h4 class="product-title">
                  <a href="{{ route('store.product.show', $rel->id) }}" style="color:inherit; text-decoration:none;">
                    {{ $rel->name_ar }}
                  </a>
                </h4>
                <div class="product-price-row">
                  <span class="price-current">${{ number_format($rel->price, 2) }}</span>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @endif

  </div>
</div>
@endsection
