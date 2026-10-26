@extends('layouts.app')

@section('title', 'أداة مقارنة المنتجات الذكية | متجر النخبة')

@section('content')
<div class="view-section active" style="padding-top:20px;">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-trail" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-light); margin-bottom:24px;">
      <a href="{{ route('store.home') }}" style="color:inherit; text-decoration:none;"><i class="fa-solid fa-house"></i> الرئيسية</a>
      <span>/</span>
      <span style="color:var(--text); font-weight:700;">أداة مقارنة المنتجات الذكية</span>
    </nav>

    <div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:16px; padding:32px; margin-bottom:40px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:14px;">
          <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg, #2563eb, #7c3aed); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
            <i class="fa-solid fa-code-compare"></i>
          </div>
          <div>
            <h1 style="font-size:1.5rem; margin:0;">جدول مقارنة مواصفات الأزياء جنباً إلى جنب</h1>
            <p style="margin:2px 0 0; color:var(--text-light); font-size:0.85rem;">قارن بدقة بين خامات الأقمشة، المقاسات المتوفرة، والأسعار لتختار القطعة الأنسب لك</p>
          </div>
        </div>

        <div style="display:flex; gap:10px;">
          <a href="{{ route('store.products') }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-plus"></i> إضافة قطع أخرى
          </a>
          <button class="btn btn-secondary btn-sm" onclick="app.clearCompare(); window.location.reload();">
            <i class="fa-solid fa-trash-can"></i> تفريغ المقارنة
          </button>
        </div>
      </div>

      <div id="compare-page-content" style="overflow-x:auto;">
        @if($products->isEmpty())
          <div style="text-align:center; padding:60px 20px;">
            <div style="font-size:3.5rem; color:var(--text-light); margin-bottom:16px;"><i class="fa-solid fa-scale-balanced"></i></div>
            <h3>قائمة المقارنة فارغة حالياً</h3>
            <p style="color:var(--text-light); max-width:480px; margin:8px auto 24px; line-height:1.6;">
              تصفح كتالوج الأزياء واضغط على زر المقارنة (<i class="fa-solid fa-code-compare" style="color:var(--primary);"></i>) على أي منتج ترغب بمقارنته هنا جنباً إلى جنب.
            </p>
            <a href="{{ route('store.products') }}" class="btn btn-primary">
              <i class="fa-solid fa-shirt"></i> استعراض منتجات المتجر
            </a>
          </div>
        @else
          <table class="compare-table" style="width:100%; min-width:700px; border-collapse:collapse;">
            <thead>
              <tr style="border-bottom:2px solid var(--border-color);">
                <th style="padding:16px; text-align:right; width:180px; background:var(--input-bg);">المواصفة / القطعة</th>
                @foreach($products as $prod)
                  <th style="padding:16px; text-align:center; vertical-align:top;">
                    <div style="position:relative; display:inline-block;">
                      <img src="{{ $prod->image }}" alt="{{ $prod->name_ar }}" style="width:120px; height:120px; object-fit:contain; border-radius:10px; border:1px solid var(--border-color); background:#fff;">
                      <button onclick="app.removeFromCompare({{ $prod->id }}); window.location.reload();" style="position:absolute; top:-8px; right:-8px; width:24px; height:24px; border-radius:50%; background:var(--danger); color:#fff; border:none; cursor:pointer;" title="حذف">
                        <i class="fa-solid fa-xmark"></i>
                      </button>
                    </div>
                    <h4 style="margin:10px 0 4px; font-size:1rem;">{{ $prod->name_ar }}</h4>
                    <span style="font-size:0.8rem; color:var(--text-light);">{{ $prod->category->name_ar ?? 'عام' }}</span>
                  </th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:14px; font-weight:700; background:var(--input-bg);"><i class="fa-solid fa-tag"></i> السعر الحالي</td>
                @foreach($products as $prod)
                  <td style="padding:14px; text-align:center; font-weight:800; font-size:1.2rem; color:var(--primary);">
                    ${{ number_format($prod->price, 2) }}
                  </td>
                @endforeach
              </tr>

              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:14px; font-weight:700; background:var(--input-bg);"><i class="fa-solid fa-scroll"></i> خامة القماش</td>
                @foreach($products as $prod)
                  <td style="padding:14px; text-align:center; font-size:0.9rem;">
                    {{ $prod->fabric ?? 'صوف وخيوط طبيعية 100%' }}
                  </td>
                @endforeach
              </tr>

              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:14px; font-weight:700; background:var(--input-bg);"><i class="fa-solid fa-scissors"></i> نوع القصة (Fit)</td>
                @foreach($products as $prod)
                  <td style="padding:14px; text-align:center;">
                    <span class="badge" style="background:var(--input-bg); border:1px solid var(--border-color); padding:4px 10px; border-radius:6px; font-size:0.85rem;">
                      {{ $prod->fit_type ?? 'Modern Fit' }}
                    </span>
                  </td>
                @endforeach
              </tr>

              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:14px; font-weight:700; background:var(--input-bg);"><i class="fa-solid fa-ruler"></i> المقاسات المتاحة</td>
                @foreach($products as $prod)
                  <td style="padding:14px; text-align:center; font-size:0.85rem;">
                    @if(!empty($prod->sizes) && is_array($prod->sizes))
                      {{ implode(' • ', $prod->sizes) }}
                    @else
                      S, M, L, XL
                    @endif
                  </td>
                @endforeach
              </tr>

              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:14px; font-weight:700; background:var(--input-bg);"><i class="fa-solid fa-star"></i> تقييم العملاء</td>
                @foreach($products as $prod)
                  <td style="padding:14px; text-align:center;">
                    <div class="stars" style="color:#f59e0b; font-size:0.85rem; display:inline-block;">
                      @for($i = 1; $i <= 5; $i++)
                        <i class="fa-{{ $i <= round($prod->rating) ? 'solid' : 'regular' }} fa-star"></i>
                      @endfor
                    </div>
                    <span style="font-size:0.85rem; font-weight:700; margin-right:4px;">{{ number_format($prod->rating, 1) }}</span>
                  </td>
                @endforeach
              </tr>

              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:14px; font-weight:700; background:var(--input-bg);"><i class="fa-solid fa-boxes-stacked"></i> حالة التوفر</td>
                @foreach($products as $prod)
                  <td style="padding:14px; text-align:center;">
                    <span class="stock-badge {{ $prod->stock > 5 ? 'in-stock' : ($prod->stock > 0 ? 'low-stock' : 'out-stock') }}">
                      {{ $prod->stock > 5 ? 'متوفر' : ($prod->stock > 0 ? 'متبقي ' . $prod->stock : 'نفد') }}
                    </span>
                  </td>
                @endforeach
              </tr>

              <tr>
                <td style="padding:16px; background:var(--input-bg);"></td>
                @foreach($products as $prod)
                  <td style="padding:16px; text-align:center;">
                    <button class="btn btn-primary btn-block" onclick="app.addToCartDirect({{ $prod->id }})" {{ $prod->stock <= 0 ? 'disabled' : '' }}>
                      <i class="fa-solid fa-cart-plus"></i> إضافة للسلة
                    </button>
                  </td>
                @endforeach
              </tr>
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
