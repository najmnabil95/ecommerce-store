@extends('layouts.app')

@section('title', 'سلة المشتريات وإتمام الطلب | متجر النخبة')

@section('content')
<div class="view-section active" style="padding-top:20px;">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav class="breadcrumb-trail" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-light); margin-bottom:24px;">
      <a href="{{ route('store.home') }}" style="color:inherit; text-decoration:none;"><i class="fa-solid fa-house"></i> الرئيسية</a>
      <span>/</span>
      <span style="color:var(--text); font-weight:700;">سلة المشتريات والدفع</span>
    </nav>

    <div class="cart-checkout-layout" style="display:grid; grid-template-columns: 1fr 380px; gap:32px; align-items:start;">
      
      <!-- Cart Items Section -->
      <div class="cart-items-column" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:16px; padding:28px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:14px;">
          <h2 style="margin:0; font-size:1.3rem;"><i class="fa-solid fa-cart-shopping" style="color:var(--primary);"></i> محتويات سلة التسوق</h2>
          <button class="btn-text" onclick="app.cart = []; app.saveCart(); app.updateCartUI(); window.location.reload();" style="color:var(--danger); font-size:0.85rem; cursor:pointer;">
            <i class="fa-solid fa-trash-can"></i> تفريغ السلة
          </button>
        </div>

        <div id="cart-page-items-list">
          <!-- Dynamically populated or rendered -->
        </div>

        <!-- Customer Checkout Info Form -->
        <div class="checkout-form-section" style="margin-top:36px; border-top:1px solid var(--border-color); padding-top:24px;">
          <h3 style="margin-bottom:16px; font-size:1.2rem;"><i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> بيانات التوصيل والشحن</h3>
          
          <form id="cart-checkout-form" onsubmit="app.handlePlaceOrder(event)">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
              <div class="form-group">
                <label style="font-weight:700; font-size:0.85rem;">اسم المستلم الكامل *</label>
                <input type="text" id="order-cust-name" required placeholder="الاسم الثلاثي" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit;">
              </div>
              <div class="form-group">
                <label style="font-weight:700; font-size:0.85rem;">رقم الجوال *</label>
                <input type="tel" id="order-cust-phone" required placeholder="05XXXXXXXX" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit; direction:ltr; text-align:right;">
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:16px; margin-bottom:16px;">
              <div class="form-group">
                <label style="font-weight:700; font-size:0.85rem;">المدينة *</label>
                <select id="order-cust-city" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit;">
                  <option value="الرياض">الرياض</option>
                  <option value="جدة">جدة</option>
                  <option value="الدمام">الدمام</option>
                  <option value="مكة المكرمة">مكة المكرمة</option>
                  <option value="المدينة المنورة">المدينة المنورة</option>
                  <option value="الخبر">الخبر</option>
                  <option value="دبي">دبي</option>
                  <option value="أبوظبي">أبوظبي</option>
                  <option value="الكويت">الكويت</option>
                  <option value="الدوحة">الدوحة</option>
                  <option value="المنامة">المنامة</option>
                </select>
              </div>
              <div class="form-group">
                <label style="font-weight:700; font-size:0.85rem;">العنوان بالتفصيل (الحي، الشارع) *</label>
                <input type="text" id="order-cust-address" required placeholder="الحي، اسم الشارع، رقم المبنى..." style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit;">
              </div>
            </div>

            <!-- Payment Method Selection -->
            <div style="margin-top:24px;">
              <label style="font-weight:700; font-size:0.9rem; display:block; margin-bottom:10px;"><i class="fa-solid fa-credit-card"></i> طريقة السداد والدفع:</label>
              <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap:12px;">
                <label class="payment-method-card active" style="padding:12px; border:1px solid var(--border-color); border-radius:10px; display:flex; flex-direction:column; align-items:center; text-align:center; cursor:pointer;">
                  <input type="radio" name="pay_method" value="mada" checked style="margin-bottom:6px;">
                  <i class="fa-solid fa-credit-card" style="font-size:1.4rem; color:var(--primary); margin-bottom:4px;"></i>
                  <span style="font-size:0.82rem; font-weight:700;">مدى / بطاقة بنكية</span>
                </label>
                <label class="payment-method-card" style="padding:12px; border:1px solid var(--border-color); border-radius:10px; display:flex; flex-direction:column; align-items:center; text-align:center; cursor:pointer;">
                  <input type="radio" name="pay_method" value="apple_pay" style="margin-bottom:6px;">
                  <i class="fa-brands fa-apple-pay" style="font-size:1.4rem; margin-bottom:4px;"></i>
                  <span style="font-size:0.82rem; font-weight:700;">Apple Pay</span>
                </label>
                <label class="payment-method-card" style="padding:12px; border:1px solid var(--border-color); border-radius:10px; display:flex; flex-direction:column; align-items:center; text-align:center; cursor:pointer;">
                  <input type="radio" name="pay_method" value="cod" style="margin-bottom:6px;">
                  <i class="fa-solid fa-money-bill-wave" style="font-size:1.4rem; color:#10b981; margin-bottom:4px;"></i>
                  <span style="font-size:0.82rem; font-weight:700;">الدفع عند الاستلام</span>
                </label>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top:24px; padding:14px; font-size:1.1rem; font-weight:700;">
              <i class="fa-solid fa-shield-check"></i> تأكيد الطلب وإصدار الفاتورة الضريبية
            </button>
          </form>
        </div>
      </div>

      <!-- Order Summary Card Column -->
      <div class="cart-summary-column" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:16px; padding:24px; position:sticky; top:90px;">
        <h3 style="margin-bottom:16px; font-size:1.15rem;"><i class="fa-solid fa-file-invoice-dollar" style="color:var(--primary);"></i> ملخص الحساب</h3>
        
        <!-- Coupon Form -->
        <div style="display:flex; gap:8px; margin-bottom:20px;">
          <input type="text" id="cart-coupon-input" placeholder="كود الخصم (WELCOME10)..." style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:inherit; text-transform:uppercase; font-weight:700; font-size:0.85rem;">
          <button class="btn btn-secondary btn-sm" onclick="app.applyCouponDirect(document.getElementById('cart-coupon-input').value)">تطبيق</button>
        </div>

        <div style="border-top:1px solid var(--border-color); padding-top:14px; font-size:0.9rem; line-height:2.2;">
          <div style="display:flex; justify-content:space-between;">
            <span>مجموع السلة:</span>
            <strong id="cart-page-subtotal">$0.00</strong>
          </div>
          <div style="display:flex; justify-content:space-between; color:#10b981;">
            <span>خصم الكوبون:</span>
            <strong id="cart-page-discount">-$0.00</strong>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>رسوم الشحن:</span>
            <strong id="cart-page-shipping">$0.00</strong>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:0.82rem; color:var(--text-light);">
            <span>ضريبة القيمة المضافة (15% مشمولة):</span>
            <span id="cart-page-vat">$0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:1.3rem; font-weight:800; border-top:2px solid var(--border-color); margin-top:10px; padding-top:10px; color:var(--primary);">
            <span>الإجمالي النهائي:</span>
            <strong id="cart-page-total">$0.00</strong>
          </div>
        </div>

        <div style="margin-top:20px; font-size:0.8rem; color:var(--text-light); line-height:1.6; background:var(--input-bg); padding:12px; border-radius:8px;">
          <i class="fa-solid fa-lock" style="color:#10b981;"></i> جميع المعاملات مشفرة وتخضع لبروتوكول الأمان العالي.
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof app !== 'undefined' && app.renderCartPageItems) {
      app.renderCartPageItems();
    }
  });
</script>
@endsection
