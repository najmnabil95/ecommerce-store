@extends('layouts.app')

@section('title', 'تتبع حالة الشحنة والطلب | متجر النخبة')

@section('content')
<div class="view-section active" style="padding-top:20px;">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-trail" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-light); margin-bottom:24px;">
      <a href="{{ route('store.home') }}" style="color:inherit; text-decoration:none;"><i class="fa-solid fa-house"></i> الرئيسية</a>
      <span>/</span>
      <span style="color:var(--text); font-weight:700;">تتبع الشحنة والطلب</span>
    </nav>

    <div style="max-width:760px; margin:0 auto; background:var(--card-bg); border:1px solid var(--border-color); border-radius:16px; padding:36px; margin-bottom:60px;">
      <div style="text-align:center; margin-bottom:30px;">
        <div style="width:56px; height:56px; border-radius:14px; background:linear-gradient(135deg, #2563eb, #3b82f6); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:1.6rem; margin-bottom:12px;">
          <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
        <h1 style="font-size:1.6rem; margin:0 0 6px;">تتبع الشحنة وحالة الطلب اللحظية</h1>
        <p style="color:var(--text-light); font-size:0.9rem; margin:0;">أدخل رقم الطلب الذي استلمته في رسالة التأكيد أو الفاتورة لمشاهدة مسار التوصيل المباشر</p>
      </div>

      <!-- Search Form -->
      <div style="display:flex; gap:10px; margin-bottom:30px;">
        <input type="text" id="track-page-order-num" value="{{ request('order') }}" placeholder="مثال: ELITE-202610-001" style="flex:1; padding:12px 16px; border-radius:10px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit; font-size:1rem; font-family:monospace; font-weight:700;">
        <button class="btn btn-primary" onclick="app.handleTrackOrderCustom(document.getElementById('track-page-order-num').value)" style="padding:12px 24px; font-size:1rem;">
          <i class="fa-solid fa-magnifying-glass"></i> تتبع
        </button>
      </div>

      <!-- Tracking Result Container -->
      <div id="track-page-result" style="{{ request('order') ? '' : 'display:none;' }}">
        
        <!-- Live Status Stepper -->
        <div class="tracking-stepper" style="display:flex; justify-content:space-between; position:relative; margin:40px 0 30px;">
          <div class="step-item active" style="text-align:center; z-index:2; flex:1;">
            <div style="width:38px; height:38px; border-radius:50%; background:#10b981; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:1rem; margin-bottom:8px;">
              <i class="fa-solid fa-check"></i>
            </div>
            <div style="font-size:0.85rem; font-weight:700;">تم الاستلام</div>
            <span style="font-size:0.75rem; color:var(--text-light);">مؤكد في النظام</span>
          </div>

          <div class="step-item active" style="text-align:center; z-index:2; flex:1;">
            <div style="width:38px; height:38px; border-radius:50%; background:#2563eb; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:1rem; margin-bottom:8px;">
              <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div style="font-size:0.85rem; font-weight:700;">قيد التجهيز</div>
            <span style="font-size:0.75rem; color:var(--text-light);">في المشغل والمستودع</span>
          </div>

          <div class="step-item" style="text-align:center; z-index:2; flex:1;">
            <div style="width:38px; height:38px; border-radius:50%; background:var(--input-bg); border:2px solid var(--border-color); color:var(--text-light); display:inline-flex; align-items:center; justify-content:center; font-size:1rem; margin-bottom:8px;">
              <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div style="font-size:0.85rem; font-weight:700;">تم الشحن</div>
            <span style="font-size:0.75rem; color:var(--text-light);">مع شركة الشحن</span>
          </div>

          <div class="step-item" style="text-align:center; z-index:2; flex:1;">
            <div style="width:38px; height:38px; border-radius:50%; background:var(--input-bg); border:2px solid var(--border-color); color:var(--text-light); display:inline-flex; align-items:center; justify-content:center; font-size:1rem; margin-bottom:8px;">
              <i class="fa-solid fa-house-chimney"></i>
            </div>
            <div style="font-size:0.85rem; font-weight:700;">تم التوصيل</div>
            <span style="font-size:0.75rem; color:var(--text-light);">لباب العميل</span>
          </div>
        </div>

        <!-- Order Snapshot Details -->
        <div style="background:var(--input-bg); border:1px solid var(--border-color); border-radius:12px; padding:20px;">
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; font-size:0.9rem; line-height:1.8;">
            <div><strong>رقم الطلب:</strong> <span id="track-res-id" style="font-family:monospace; color:var(--primary); font-weight:700;">ELITE-202610-001</span></div>
            <div><strong>شركة الشحن:</strong> <span>Elite Express (توصيل فائق)</span></div>
            <div><strong>المدينة والوجهة:</strong> <span id="track-res-city">الرياض</span></div>
            <div><strong>الموعد المتوقع:</strong> <span style="color:#10b981; font-weight:700;">خلال 48 ساعة</span></div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
