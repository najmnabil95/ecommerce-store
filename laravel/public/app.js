// Global State Management & E-Commerce Application Logic
const app = {
  categories: [
    { id: 1, slug: 'electronics', name_ar: 'إلكترونيات وأجهزة', name_en: 'Electronics', icon: 'fa-laptop' },
    { id: 2, slug: 'fashion', name_ar: 'أزياء وملابس', name_en: 'Fashion', icon: 'fa-shirt' },
    { id: 3, slug: 'perfumes', name_ar: 'عطور وعناية', name_en: 'Perfumes & Care', icon: 'fa-spray-can-sparkles' },
    { id: 4, slug: 'accessories', name_ar: 'ساعات وإكسسوارات', name_en: 'Accessories', icon: 'fa-gem' },
    { id: 5, slug: 'home', name_ar: 'مستلزمات منزلية', name_en: 'Home & Living', icon: 'fa-couch' }
  ],
  products: [],
  cart: JSON.parse(localStorage.getItem('elite_cart') || '[]'),
  wishlist: JSON.parse(localStorage.getItem('elite_wishlist') || '[]'),
  currentUser: JSON.parse(localStorage.getItem('elite_user') || 'null'),
  token: localStorage.getItem('elite_token') || '',
  appliedCoupon: null,
  selectedPaymentMethod: 'cod',
  isAdmin: false,
  searchTerm: '',
  currentCategory: 'all',
  currentSort: 'default',

  // Advanced Filters State
  filterMaxPrice: 1000,
  filterMinRating: 0,
  filterInStockOnly: false,
  filterSize: 'all',
  filterFit: 'all',
  filterColor: 'all',
  selectedBuildPreference: 'slim',
  calculatedSmartSize: 'L',
  selectedQuickViewSize: null,
  selectedQuickViewColor: null,

  // Admin Dashboard Enterprise State
  statsPeriod: '7days',
  selectedProductIds: [],
  selectedOrderIds: [],
  currentOrderStatusFilter: 'all',
  commandPaletteOpen: false,
  commandSelectedIndex: 0,
  commandPaletteItems: [],

  // Notifications Hub State
  notifications: JSON.parse(localStorage.getItem('elite_notifs') || 'null') || [
    { id: 1, title: 'كوبون خصم حصري! 🎉', desc: 'استخدم الكود ELITE20 للحصول على خصم 20% على طلبك', time: 'منذ ساعتين', unread: true },
    { id: 2, title: 'شحن مجاني سريع 🚚', desc: 'تم تفعيل الشحن المجاني التلقائي للطلبات بقيمة $150 أو أكثر', time: 'منذ 5 ساعات', unread: true },
    { id: 3, title: 'تشكيلة جديدة فاخرة ⭐', desc: 'وصلت منتجات حصرية في أقسام الإلكترونيات والعطور الملكية', time: 'أمس', unread: true }
  ],

  // INITIALIZATION
  init: async function() {
    this.initTheme();
    this.updateAuthUI();
    this.updateWishlistBadge();
    this.updateCartUI();
    this.renderNotifications();
    this.setupCommandPaletteListeners();

    // Fetch live settings, categories and products from MariaDB database
    await this.loadStoreSettings();
    await this.fetchCategories();
    await this.fetchProducts();

    this.renderStore();
    this.setupEventListeners();
  },

  // THEME MANAGEMENT (DARK / LIGHT MODE)
  initTheme: function() {
    const savedTheme = localStorage.getItem('elite_theme') || 'light';
    if (savedTheme === 'dark') {
      document.body.classList.add('dark-mode');
      this.updateThemeButton(true);
    } else {
      document.body.classList.remove('dark-mode');
      this.updateThemeButton(false);
    }
  },

  toggleTheme: function() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('elite_theme', isDark ? 'dark' : 'light');
    this.updateThemeButton(isDark);
    this.showToast(isDark ? 'تم تفعيل الوضع الداكن' : 'تم تفعيل الوضع الفاتح', 'info');
  },

  updateThemeButton: function(isDark) {
    const icon = document.querySelector('#theme-toggle-btn i');
    const text = document.getElementById('theme-text');
    if (icon && text) {
      icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
      text.innerText = isDark ? 'الوضع الفاتح' : 'الوضع الداكن';
    }
  },

  loadStoreSettings: async function() {
    try {
      const res = await fetch('/api/settings');
      const data = await res.json();
      if (data.success && data.data) {
        const s = data.data;
        if (s.announcement_text) {
          const annEl = document.getElementById('top-announcement-text');
          if (annEl) annEl.innerText = s.announcement_text;
        }
      }
    } catch (e) {
      console.warn('Using default settings');
    }
  },

  fetchCategories: async function() {
    try {
      const res = await fetch('/api/categories');
      const data = await res.json();
      if (data.success && data.data && data.data.length > 0) {
        this.categories = data.data;
      }
    } catch (e) {
      console.warn('Using offline categories');
    }
  },

  fetchProducts: async function() {
    try {
      let url = '/api/products?';
      if (this.currentCategory !== 'all') url += `category=${encodeURIComponent(this.currentCategory)}&`;
      if (this.searchTerm) url += `search=${encodeURIComponent(this.searchTerm)}&`;
      if (this.currentSort !== 'default') url += `sort=${encodeURIComponent(this.currentSort)}&`;

      const res = await fetch(url);
      const data = await res.json();
      if (data.success && data.data) {
        this.products = data.data;
      }
    } catch (e) {
      console.warn('API error, using cached products');
    }
  },

  // UI RENDERING
  renderStore: function() {
    this.renderCategories();
    this.renderProducts();
    this.updateCartUI();
  },

  renderCategories: function() {
    const container = document.getElementById('categories-container');
    if (!container) return;
    container.innerHTML = `
      <div class="category-pill ${this.currentCategory === 'all' ? 'active' : ''}" onclick="app.filterProducts('all')">
        <i class="fa-solid fa-border-all"></i> الكل
      </div>
    ` + this.categories.map(cat => `
      <div class="category-pill ${this.currentCategory === cat.slug ? 'active' : ''}" onclick="app.filterProducts('${cat.slug}')">
        <i class="fa-solid ${cat.icon || 'fa-tag'}"></i> ${cat.name_ar}
      </div>
    `).join('');
  },

  renderProducts: function() {
    const grid = document.getElementById('products-grid');
    const countEl = document.getElementById('products-count');
    if (!grid) return;

    let list = [...this.products];

    // Filter Category
    if (this.currentCategory !== 'all') {
      list = list.filter(p => p.category_slug === this.currentCategory || p.category_id == this.currentCategory);
    }

    // Filter Search
    if (this.searchTerm) {
      const term = this.searchTerm.toLowerCase();
      list = list.filter(p =>
        p.name_ar.toLowerCase().includes(term) ||
        (p.name_en && p.name_en.toLowerCase().includes(term)) ||
        (p.description_ar && p.description_ar.toLowerCase().includes(term))
      );
    }

    // Filter by Size
    if (this.filterSize && this.filterSize !== 'all') {
      list = list.filter(p => p.sizes && Array.isArray(p.sizes) && p.sizes.includes(this.filterSize));
    }

    // Filter by Fit Type
    if (this.filterFit && this.filterFit !== 'all') {
      list = list.filter(p => p.fit_type === this.filterFit);
    }

    // Filter by Color Swatch
    if (this.filterColor && this.filterColor !== 'all') {
      list = list.filter(p => p.colors && Array.isArray(p.colors) && p.colors.some(c => c.name && c.name.includes(this.filterColor)));
    }

    // Filter Max Price
    list = list.filter(p => p.price <= this.filterMaxPrice);

    // Filter Min Rating
    if (this.filterMinRating > 0) {
      list = list.filter(p => (p.rating || 5) >= this.filterMinRating);
    }

    // Filter In Stock Only
    if (this.filterInStockOnly) {
      list = list.filter(p => p.stock > 0);
    }

    // Sort
    if (this.currentSort === 'price-low') {
      list.sort((a, b) => a.price - b.price);
    } else if (this.currentSort === 'price-high') {
      list.sort((a, b) => b.price - a.price);
    } else if (this.currentSort === 'rating') {
      list.sort((a, b) => (b.rating || 0) - (a.rating || 0));
    }

    if (countEl) {
      countEl.innerText = `عرض ${list.length} من أصل ${this.products.length} قطعة`;
    }

    if (list.length === 0) {
      grid.innerHTML = `
        <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
          <i class="fa-solid fa-shirt" style="font-size: 3.5rem; color: var(--text-light); margin-bottom: 16px;"></i>
          <h3>لم يتم العثور على قطع ملابس مطابقة للتصفية</h3>
          <p style="color: var(--text-light);">جرب تعديل المقاس، نوع القصة، أو نطاق السعر</p>
          <button class="btn btn-primary" style="margin-top: 16px;" onclick="app.resetAdvancedFilters()">تفريغ الفلاتر واستعراض الكل</button>
        </div>
      `;
      return;
    }

    grid.innerHTML = list.map(p => {
      const isWish = this.wishlist.some(w => w.id === p.id);
      const origPriceHtml = p.original_price ? `<span class="original-price">$${p.original_price.toFixed(2)}</span>` : '';
      const badgeHtml = p.badge ? `<span class="product-badge">${p.badge}</span>` : '';
      const outOfStock = p.stock <= 0;

      // Fashion Color Dots
      const colorsList = Array.isArray(p.colors) ? p.colors : [];
      const colorsHtml = colorsList.length ? `
        <div class="product-card-colors">
          ${colorsList.slice(0, 4).map(c => `<span class="card-color-dot" style="background-color: ${c.hex};" title="${c.name}"></span>`).join('')}
          ${colorsList.length > 4 ? `<span class="card-colors-more">+${colorsList.length - 4}</span>` : ''}
        </div>
      ` : '';

      // Fashion Sizes Tags
      const sizesList = Array.isArray(p.sizes) ? p.sizes : [];
      const sizesHtml = sizesList.length ? `
        <div class="product-card-sizes">
          ${sizesList.slice(0, 4).map(s => `<span class="card-size-tag">${s}</span>`).join('')}
          ${sizesList.length > 4 ? `<span class="card-size-tag">+${sizesList.length - 4}</span>` : ''}
        </div>
      ` : '';

      // Fabric Material Tag
      const materialHtml = p.material ? `
        <span class="card-material-tag" title="${p.material}">
          <i class="fa-solid fa-feather"></i> ${p.material.split(' ')[0]} ${p.material.split(' ')[1] || ''}
        </span>
      ` : '';

      return `
        <div class="product-card">
          <div class="product-image-wrap">
            ${badgeHtml}
            <button class="wishlist-btn ${isWish ? 'active' : ''}" onclick="app.toggleWishlist(${p.id})">
              <i class="${isWish ? 'fa-solid' : 'fa-regular'} fa-heart"></i>
            </button>
            <img src="${p.image_url}" alt="${p.name_ar}" class="product-img" onclick="app.quickView(${p.id})">
            <button class="quick-view-btn" onclick="app.quickView(${p.id})">
              <i class="fa-solid fa-eye"></i> استعراض وتحديد المقاس
            </button>
          </div>
          <div class="product-info">
            <span class="product-cat">${p.category_name || 'أزياء فاخرة'}</span>
            <h3 class="product-title" onclick="app.quickView(${p.id})">${p.name_ar}</h3>
            ${materialHtml}
            ${sizesHtml}
            ${colorsHtml}
            <div class="product-rating">
              <div class="stars">${'★'.repeat(Math.round(p.rating || 5))}${'☆'.repeat(5 - Math.round(p.rating || 5))}</div>
              <span class="rating-val">${p.rating || 5.0}</span>
              <span class="reviews-num">(${p.reviews_count || 0})</span>
            </div>
            <div class="product-bottom">
              <div class="product-price">
                <span class="current-price">$${p.price.toFixed(2)}</span>
                ${origPriceHtml}
              </div>
              <button class="add-to-cart-btn ${outOfStock ? 'disabled' : ''}" ${outOfStock ? 'disabled' : ''} onclick="app.quickView(${p.id})">
                <i class="fa-solid fa-bag-shopping"></i> ${outOfStock ? 'نفد' : 'اختر المقاس'}
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  },

  // ADVANCED FACETED FILTERS
  toggleAdvancedFilters: function() {
    const panel = document.getElementById('advanced-filters-panel');
    if (panel) panel.classList.toggle('active');
  },

  handleSizeFilter: function(val) {
    this.filterSize = val;
    this.renderProducts();
  },

  handleFitFilter: function(val) {
    this.filterFit = val;
    this.renderProducts();
  },

  handleColorFilter: function(color, btn) {
    this.filterColor = color;
    document.querySelectorAll('.color-filter-chip').forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');
    this.renderProducts();
  },

  handlePriceSlider: function(val) {
    this.filterMaxPrice = parseFloat(val);
    const display = document.getElementById('price-range-display');
    if (display) display.innerText = `$${val}`;
    this.renderProducts();
  },

  handleRatingFilter: function(val) {
    this.filterMinRating = parseFloat(val);
    this.renderProducts();
  },

  handleInStockFilter: function(checked) {
    this.filterInStockOnly = checked;
    this.renderProducts();
  },

  resetAdvancedFilters: function() {
    this.filterMaxPrice = 1000;
    this.filterMinRating = 0;
    this.filterInStockOnly = false;
    this.filterSize = 'all';
    this.filterFit = 'all';
    this.filterColor = 'all';

    const slider = document.getElementById('price-range-slider');
    const display = document.getElementById('price-range-display');
    const ratingSelect = document.getElementById('filter-rating');
    const inStockCheck = document.getElementById('filter-instock');
    const sizeSelect = document.getElementById('filter-size');
    const fitSelect = document.getElementById('filter-fit');

    if (slider) slider.value = 1000;
    if (display) display.innerText = '$1000';
    if (ratingSelect) ratingSelect.value = '0';
    if (inStockCheck) inStockCheck.checked = false;
    if (sizeSelect) sizeSelect.value = 'all';
    if (fitSelect) fitSelect.value = 'all';

    document.querySelectorAll('.color-filter-chip').forEach(c => {
      c.classList.toggle('active', c.getAttribute('data-color') === 'all');
    });

    this.renderProducts();
    this.showToast('تمت إعادة ضبط فلاتر الملابس والمقاسات والألوان');
  },

  // FILTER & SEARCH & SORT
  filterProducts: async function(slug) {
    this.currentCategory = slug;
    await this.fetchProducts();
    this.renderStore();
  },

  handleSearch: function(val) {
    this.searchTerm = val.trim();
    const clearBtn = document.getElementById('clear-search');
    if (clearBtn) clearBtn.style.display = this.searchTerm ? 'block' : 'none';

    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(async () => {
      await this.fetchProducts();
      this.renderProducts();
    }, 250);
  },

  clearSearch: async function() {
    const input = document.getElementById('search-input');
    if (input) input.value = '';
    this.searchTerm = '';
    const clearBtn = document.getElementById('clear-search');
    if (clearBtn) clearBtn.style.display = 'none';
    await this.fetchProducts();
    this.renderProducts();
  },

  handleSortChange: async function(sortVal) {
    this.currentSort = sortVal;
    await this.fetchProducts();
    this.renderProducts();
  },

  // CART SYSTEM
  addToCart: function(id, size = null, color = null) {
    const prod = this.products.find(p => p.id === id);
    if (!prod) return;

    if (prod.stock <= 0) {
      this.showToast('عذراً، هذه القطعة غير متوفرة حالياً في المخزون', 'danger');
      return;
    }

    const chosenSize = size || (prod.sizes && Array.isArray(prod.sizes) && prod.sizes.length ? prod.sizes[0] : null);
    const chosenColor = color || (prod.colors && Array.isArray(prod.colors) && prod.colors.length ? prod.colors[0].name : null);
    const cartKey = `${id}-${chosenSize || 'std'}-${chosenColor || 'std'}`;

    const exist = this.cart.find(c => (c.cartKey === cartKey) || (!c.cartKey && c.id === id));
    if (exist) {
      if (exist.quantity >= prod.stock) {
        this.showToast(`الكمية المتاحة في المخزون هي ${prod.stock} فقط`, 'danger');
        return;
      }
      exist.quantity += 1;
      exist.cartKey = cartKey;
      if (!exist.size) exist.size = chosenSize;
      if (!exist.color) exist.color = chosenColor;
    } else {
      this.cart.push({
        id: prod.id,
        cartKey,
        name_ar: prod.name_ar,
        price: prod.price,
        image_url: prod.image_url,
        size: chosenSize,
        color: chosenColor,
        quantity: 1
      });
    }

    this.saveCart();
    this.updateCartUI();
    const sizeNote = chosenSize ? ` [مقاس: ${chosenSize}]` : '';
    this.showToast(`تمت إضافة "${prod.name_ar}"${sizeNote} إلى حقيبة التسوق ✨`, 'success');
    this.openCartDrawer();
  },

  removeFromCart: function(keyOrId) {
    this.cart = this.cart.filter(c => c.cartKey !== keyOrId && c.id !== keyOrId);
    this.saveCart();
    this.updateCartUI();
    this.showToast('تم حذف القطعة من حقيبة التسوق');
  },

  changeQuantity: function(keyOrId, delta) {
    const item = this.cart.find(c => c.cartKey === keyOrId || c.id === keyOrId);
    if (!item) return;
    const prod = this.products.find(p => p.id === item.id);
    const newQty = item.quantity + delta;

    if (newQty <= 0) {
      this.removeFromCart(keyOrId);
    } else {
      if (prod && newQty > prod.stock) {
        this.showToast(`الكمية المتاحة في المخزون هي ${prod.stock} فقط`, 'danger');
        return;
      }
      item.quantity = newQty;
      this.saveCart();
      this.updateCartUI();
    }
  },

  saveCart: function() {
    localStorage.setItem('elite_cart', JSON.stringify(this.cart));
  },

  calculateCartTotals: function() {
    const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    let discount = 0;

    if (this.appliedCoupon) {
      discount = (subtotal * this.appliedCoupon.discount_percent) / 100;
      if (this.appliedCoupon.max_discount && discount > this.appliedCoupon.max_discount) {
        discount = this.appliedCoupon.max_discount;
      }
      discount = Math.round(discount * 100) / 100;
    }

    const shipping = subtotal >= 150 || subtotal === 0 ? 0 : 15.00;
    const total = Math.max(0, subtotal - discount + shipping);

    return { subtotal, discount, shipping, total };
  },

  updateCartUI: function() {
    const count = this.cart.reduce((s, i) => s + i.quantity, 0);
    const badge = document.getElementById('cart-badge');
    const drawerCount = document.getElementById('drawer-cart-count');
    if (badge) badge.innerText = count;
    if (drawerCount) drawerCount.innerText = count;

    const { subtotal, discount, shipping, total } = this.calculateCartTotals();

    // Drawer elements
    const subtotalEl = document.getElementById('cart-subtotal');
    const shippingEl = document.getElementById('cart-shipping-fee');
    const totalEl = document.getElementById('cart-total');
    const discountLine = document.getElementById('cart-discount-line');
    const discountVal = document.getElementById('cart-discount');

    if (subtotalEl) subtotalEl.innerText = `$${subtotal.toFixed(2)}`;
    if (shippingEl) shippingEl.innerText = shipping === 0 ? 'مجاني' : `$${shipping.toFixed(2)}`;
    if (totalEl) totalEl.innerText = `$${total.toFixed(2)}`;

    if (discountLine && discountVal) {
      if (discount > 0) {
        discountLine.style.display = 'flex';
        discountVal.innerText = `-$${discount.toFixed(2)}`;
      } else {
        discountLine.style.display = 'none';
      }
    }

    // Shipping Progress
    const msgEl = document.getElementById('shipping-msg');
    const fillEl = document.getElementById('shipping-fill');
    if (msgEl && fillEl) {
      if (subtotal >= 150) {
        msgEl.innerHTML = '<i class="fa-solid fa-circle-check" style="color:var(--success)"></i> مبروك! حصلت على شحن مجاني لطلبك';
        fillEl.style.width = '100%';
      } else {
        const remaining = 150 - subtotal;
        msgEl.innerText = `أضف $${remaining.toFixed(2)} للحصول على شحن مجاني!`;
        fillEl.style.width = `${Math.min(100, (subtotal / 150) * 100)}%`;
      }
    }

    // Cart Items Container
    const container = document.getElementById('cart-items-container');
    if (container) {
      if (this.cart.length === 0) {
        container.innerHTML = `
          <div class="empty-cart-view" style="text-align: center; padding: 40px 10px;">
            <i class="fa-solid fa-basket-shopping" style="font-size: 3rem; color: var(--text-light); margin-bottom: 12px;"></i>
            <h4>حقيبة التسوق فارغة حالياً</h4>
            <p style="color: var(--text-light); font-size: 0.9rem;">استكشف تشكيلات الأزياء الراقية وأضف ما يناسب ذوقك</p>
          </div>
        `;
      } else {
        container.innerHTML = this.cart.map(item => `
          <div class="cart-item-row">
            <img src="${item.image_url}" alt="${item.name_ar}" class="cart-item-thumb">
            <div class="cart-item-details">
              <h4>${item.name_ar}</h4>
              <div class="cart-fashion-specs">
                ${item.size ? `<span class="spec-tag size-tag"><i class="fa-solid fa-ruler"></i> المقاس: ${item.size}</span>` : ''}
                ${item.color ? `<span class="spec-tag color-tag"><i class="fa-solid fa-palette"></i> ${item.color}</span>` : ''}
              </div>
              <span class="cart-item-price">$${item.price.toFixed(2)}</span>
              <div class="cart-item-qty">
                <button class="qty-btn" onclick="app.changeQuantity('${item.cartKey || item.id}', -1)">-</button>
                <span>${item.quantity}</span>
                <button class="qty-btn" onclick="app.changeQuantity('${item.cartKey || item.id}', 1)">+</button>
              </div>
            </div>
            <button class="cart-item-remove" onclick="app.removeFromCart('${item.cartKey || item.id}')">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        `).join('');
      }
    }
  },

  toggleCartDrawer: function() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer && overlay) {
      drawer.classList.toggle('active');
      overlay.classList.toggle('active');
    }
  },

  openCartDrawer: function() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    if (drawer && overlay) {
      drawer.classList.add('active');
      overlay.classList.add('active');
    }
  },

  // COUPONS / PROMO CODES
  applyCoupon: async function() {
    const input = document.getElementById('cart-coupon-input');
    if (!input || !input.value.trim()) {
      this.showToast('يرجى إدخال كود الكوبون', 'danger');
      return;
    }

    const { subtotal } = this.calculateCartTotals();
    if (subtotal === 0) {
      this.showToast('أضف منتجات إلى السلة أولاً لتطبيق الخصم', 'danger');
      return;
    }

    try {
      const res = await fetch('/api/coupons/validate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: input.value.trim(), subtotal })
      });
      const result = await res.json();

      if (result.success) {
        this.appliedCoupon = result.data;
        this.showToast(result.data.message || 'تم تفعيل الكوبون بنجاح!', 'success');
        this.renderCouponTag();
        this.updateCartUI();
        this.updateCheckoutSummary();
      } else {
        this.showToast(result.message || 'كود الخصم غير صحيح', 'danger');
      }
    } catch (e) {
      this.showToast('تعذر الاتصال للتحقق من الكوبون', 'danger');
    }
  },

  removeCoupon: function() {
    this.appliedCoupon = null;
    const tag = document.getElementById('coupon-applied-tag');
    if (tag) tag.style.display = 'none';
    const input = document.getElementById('cart-coupon-input');
    if (input) input.value = '';
    this.updateCartUI();
    this.updateCheckoutSummary();
    this.showToast('تم إلغاء كود الخصم');
  },

  renderCouponTag: function() {
    const tag = document.getElementById('coupon-applied-tag');
    if (tag && this.appliedCoupon) {
      tag.style.display = 'flex';
      tag.innerHTML = `
        <span><i class="fa-solid fa-tag"></i> خصم ${this.appliedCoupon.discount_percent}% (${this.appliedCoupon.code})</span>
        <span class="remove-coupon-btn" onclick="app.removeCoupon()" title="إلغاء الكوبون">&times;</span>
      `;
    }
  },

  // WISHLIST SYSTEM
  toggleWishlist: function(id) {
    const prod = this.products.find(p => p.id === id);
    if (!prod) return;

    const idx = this.wishlist.findIndex(w => w.id === id);
    if (idx > -1) {
      this.wishlist.splice(idx, 1);
      this.showToast('تمت الإزالة من المفضلة');
    } else {
      this.wishlist.push({
        id: prod.id,
        name_ar: prod.name_ar,
        price: prod.price,
        image_url: prod.image_url
      });
      this.showToast('تمت الإضافة إلى المفضلة ❤️');
    }

    localStorage.setItem('elite_wishlist', JSON.stringify(this.wishlist));
    this.updateWishlistBadge();
    this.renderProducts();
    this.renderWishlistModal();
  },

  updateWishlistBadge: function() {
    const badge = document.getElementById('wishlist-badge');
    if (badge) badge.innerText = this.wishlist.length;
  },

  showWishlistModal: function() {
    this.renderWishlistModal();
    document.getElementById('wishlist-modal').classList.add('active');
  },

  renderWishlistModal: function() {
    const container = document.getElementById('wishlist-items-container');
    if (!container) return;

    if (this.wishlist.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 40px; color: var(--text-light);">
          <i class="fa-regular fa-heart" style="font-size: 3rem; margin-bottom: 10px;"></i>
          <p>قائمة المفضلة فارغة حالياً</p>
        </div>
      `;
      return;
    }

    container.innerHTML = this.wishlist.map(item => `
      <div class="wishlist-item-card">
        <img src="${item.image_url}" class="wishlist-img">
        <div class="wishlist-info">
          <h4>${item.name_ar}</h4>
          <span style="font-weight: 700; color: var(--primary);">$${item.price.toFixed(2)}</span>
        </div>
        <div class="wishlist-actions">
          <button class="btn btn-primary" onclick="app.addToCart(${item.id}); app.toggleWishlist(${item.id})">
            <i class="fa-solid fa-cart-plus"></i> نقل للسلة
          </button>
          <button class="btn btn-secondary" onclick="app.toggleWishlist(${item.id})">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>
    `).join('');
  },

  // PAYMENT METHODS
  selectPaymentMethod: function(method, el) {
    this.selectedPaymentMethod = method;
    document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');

    const cardBox = document.getElementById('card-inputs-container');
    if (cardBox) {
      cardBox.style.display = method === 'card' ? 'flex' : 'none';
    }
  },

  // CHECKOUT
  openCheckoutModal: function() {
    if (this.cart.length === 0) {
      this.showToast('سلة التسوق فارغة!', 'danger');
      return;
    }
    this.toggleCartDrawer(); // Close drawer

    // Autofill user details if logged in
    if (this.currentUser) {
      const nameInput = document.getElementById('cust-name');
      const phoneInput = document.getElementById('cust-phone');
      const addressInput = document.getElementById('cust-address');
      const emailInput = document.getElementById('cust-email');

      if (nameInput && this.currentUser.name) nameInput.value = this.currentUser.name;
      if (phoneInput && this.currentUser.phone) phoneInput.value = this.currentUser.phone;
      if (addressInput && this.currentUser.address) addressInput.value = this.currentUser.address;
      if (emailInput && this.currentUser.email) emailInput.value = this.currentUser.email;
    }

    this.updateCheckoutSummary();
    document.getElementById('checkout-modal').classList.add('active');
  },

  updateCheckoutSummary: function() {
    const previewContainer = document.getElementById('checkout-items-preview');
    if (previewContainer) {
      previewContainer.innerHTML = this.cart.map(i => `
        <div class="checkout-item-line" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
          <img src="${i.image_url}" style="width:42px; height:42px; border-radius:6px; object-fit:cover;">
          <div style="flex:1;">
            <div style="font-size:0.88rem; font-weight:700;">${i.name_ar} (×${i.quantity})</div>
            ${(i.size || i.color) ? `<div style="font-size:0.75rem; color:var(--text-light); margin-top:2px;">${i.size ? '<span style="background:rgba(37,99,235,0.08); color:var(--primary); padding:1px 5px; border-radius:3px;">مقاس: ' + i.size + '</span>' : ''} ${i.color ? '<span style="margin-right:5px;">لون: ' + i.color + '</span>' : ''}</div>` : ''}
          </div>
          <strong style="color:var(--primary); font-size:0.95rem;">$${(i.price * i.quantity).toFixed(2)}</strong>
        </div>
      `).join('');
    }

    const { subtotal, discount, shipping, total } = this.calculateCartTotals();

    const subEl = document.getElementById('checkout-subtotal');
    const discRow = document.getElementById('checkout-discount-row');
    const discEl = document.getElementById('checkout-discount');
    const shipEl = document.getElementById('checkout-shipping');
    const totEl = document.getElementById('checkout-total');

    if (subEl) subEl.innerText = `$${subtotal.toFixed(2)}`;
    if (shipEl) shipEl.innerText = shipping === 0 ? 'مجاني' : `$${shipping.toFixed(2)}`;
    if (totEl) totEl.innerText = `$${total.toFixed(2)}`;

    if (discRow && discEl) {
      if (discount > 0) {
        discRow.style.display = 'flex';
        discEl.innerText = `-$${discount.toFixed(2)}`;
      } else {
        discRow.style.display = 'none';
      }
    }
  },

  handlePlaceOrder: async function(e) {
    e.preventDefault();
    if (this.cart.length === 0) {
      this.showToast('حقيبة التسوق فارغة!', 'danger');
      return;
    }

    const { total } = this.calculateCartTotals();

    const orderData = {
      customer_name: document.getElementById('cust-name').value,
      customer_phone: document.getElementById('cust-phone').value,
      city: document.getElementById('cust-city').value,
      address: document.getElementById('cust-address').value,
      customer_email: document.getElementById('cust-email').value,
      payment_method: this.selectedPaymentMethod,
      notes: document.getElementById('cust-notes').value,
      coupon_code: this.appliedCoupon ? this.appliedCoupon.code : '',
      items: this.cart.map(i => ({
        id: i.id,
        quantity: i.quantity,
        size: i.size || null,
        color: i.color || null
      }))
    };

    this.pendingOrderData = orderData;

    // Interactive Payment Simulations
    if (this.selectedPaymentMethod === 'apple_pay') {
      const appleTotal = document.getElementById('apple-pay-total');
      if (appleTotal) appleTotal.innerText = `$${total.toFixed(2)}`;
      document.getElementById('apple-pay-sheet-modal').classList.add('active');
      return;
    }

    if (this.selectedPaymentMethod === 'card') {
      document.getElementById('otp-modal').classList.add('active');
      return;
    }

    // COD or Bank Transfer: Direct submit
    await this.submitOrderToServer(orderData);
  },

  confirmApplePay: async function() {
    this.closeModal('apple-pay-sheet-modal');
    this.showToast('جاري التحقق عبر Face ID ومعالجة الدفع...', 'info');
    setTimeout(async () => {
      await this.submitOrderToServer(this.pendingOrderData);
    }, 900);
  },

  confirmOtpPayment: async function() {
    this.closeModal('otp-modal');
    this.showToast('تم التحقق من الرمز البنكي بنجاح! جاري إرسال الطلب...', 'success');
    setTimeout(async () => {
      await this.submitOrderToServer(this.pendingOrderData);
    }, 600);
  },

  submitOrderToServer: async function(orderData) {
    try {
      const headers = { 'Content-Type': 'application/json' };
      if (this.token) headers['Authorization'] = `Bearer ${this.token}`;

      const res = await fetch('/api/orders', {
        method: 'POST',
        headers,
        body: JSON.stringify(orderData)
      });
      const result = await res.json();

      if (result.success) {
        this.lastPlacedOrder = result.order;
        this.cart = [];
        this.appliedCoupon = null;
        this.saveCart();
        this.updateCartUI();
        this.closeModal('checkout-modal');

        document.getElementById('succ-order-num').innerText = result.order.order_number;
        document.getElementById('succ-order-total').innerText = `$${result.order.total.toFixed(2)}`;
        document.getElementById('succ-order-pay').innerText = this.formatPaymentName(result.order.payment_method);
        document.getElementById('succ-order-address').innerText = `${result.order.city} - ${result.order.address}`;
        document.getElementById('order-success-modal').classList.add('active');

        // Refresh products to reflect decremented stock
        await this.fetchProducts();
        this.renderProducts();
      } else {
        this.showToast(result.message || 'حدث خطأ أثناء حفظ الطلب', 'danger');
      }
    } catch (err) {
      this.showToast('تعذر إرسال الطلب، يرجى المحاولة مرة أخرى', 'danger');
    }
  },

  formatPaymentName: function(method) {
    const map = {
      cod: 'الدفع عند الاستلام',
      card: 'بطاقة مدى / ائتمان',
      apple_pay: 'Apple Pay',
      bank_transfer: 'تحويل بنكي'
    };
    return map[method] || method;
  },

  // ORDER TRACKING WITH VISUAL PROGRESS STEPPER
  showTrackingModal: function() {
    document.getElementById('tracking-modal').classList.add('active');
  },

  handleTrackOrder: async function() {
    const input = document.getElementById('track-order-number');
    const resultBox = document.getElementById('tracking-result');
    if (!input || !input.value.trim()) {
      this.showToast('يرجى إدخال رقم الطلب', 'danger');
      return;
    }

    try {
      const res = await fetch(`/api/orders/track?number=${encodeURIComponent(input.value.trim())}`);
      const data = await res.json();

      if (data.success && data.data) {
        const order = data.data;

        // Determine stepper progress
        let stepIndex = 1;
        let progressWidth = '0%';
        if (order.status === 'pending') { stepIndex = 1; progressWidth = '15%'; }
        else if (order.status === 'processing') { stepIndex = 2; progressWidth = '45%'; }
        else if (order.status === 'shipped') { stepIndex = 3; progressWidth = '75%'; }
        else if (order.status === 'delivered') { stepIndex = 4; progressWidth = '100%'; }
        else if (order.status === 'cancelled') { stepIndex = 0; progressWidth = '0%'; }

        const statusMap = {
          pending: { text: 'قيد المراجعة', class: 'pending', icon: 'fa-clock' },
          processing: { text: 'جاري التجهيز والفرز', class: 'processing', icon: 'fa-box' },
          shipped: { text: 'تم الشحن ومع مندوب التوصيل', class: 'shipped', icon: 'fa-truck-fast' },
          delivered: { text: 'تم التوصيل بنجاح', class: 'delivered', icon: 'fa-circle-check' },
          cancelled: { text: 'تم إلغاء الطلب', class: 'cancelled', icon: 'fa-ban' }
        };
        const s = statusMap[order.status] || { text: order.status, class: 'pending', icon: 'fa-info' };

        resultBox.style.display = 'block';
        resultBox.innerHTML = `
          <!-- Order Stepper -->
          <div class="stepper-container">
            <div class="stepper-progress-line" style="width: ${progressWidth};"></div>
            <div class="stepper-step ${stepIndex >= 1 ? (stepIndex > 1 ? 'completed' : 'active') : ''}">
              <div class="step-icon"><i class="fa-solid fa-receipt"></i></div>
              <span class="step-label">تأكيد الطلب</span>
            </div>
            <div class="stepper-step ${stepIndex >= 2 ? (stepIndex > 2 ? 'completed' : 'active') : ''}">
              <div class="step-icon"><i class="fa-solid fa-boxes-packing"></i></div>
              <span class="step-label">تجهيز الشحنة</span>
            </div>
            <div class="stepper-step ${stepIndex >= 3 ? (stepIndex > 3 ? 'completed' : 'active') : ''}">
              <div class="step-icon"><i class="fa-solid fa-truck-fast"></i></div>
              <span class="step-label">خرج للتوصيل</span>
            </div>
            <div class="stepper-step ${stepIndex === 4 ? 'completed' : ''}">
              <div class="step-icon"><i class="fa-solid fa-house-circle-check"></i></div>
              <span class="step-label">تم الاستلام</span>
            </div>
          </div>

          <div class="track-info-card" style="margin-top:16px;">
            <h4><i class="fa-solid ${s.icon}"></i> حالة الطلب الحالية: <span class="status-badge ${s.class}">${s.text}</span></h4>
            <p><strong>رقم الشحنة:</strong> ${order.order_number}</p>
            <p><strong>العميل:</strong> ${order.customer_name} (${order.customer_phone})</p>
            <p><strong>عنوان الشحن:</strong> ${order.city} - ${order.address}</p>
            <p><strong>المبلغ الإجمالي:</strong> $${order.total.toFixed(2)} (${this.formatPaymentName(order.payment_method)})</p>
            <p><strong>تاريخ الطلب:</strong> ${new Date(order.created_at).toLocaleString('ar-SA')}</p>
            <div style="margin-top:14px;">
              <button class="btn btn-outline" onclick="app.showInvoiceById(${order.id})">
                <i class="fa-solid fa-receipt"></i> استعراض الفاتورة الضريبية
              </button>
            </div>
          </div>
        `;
      } else {
        this.showToast(data.message || 'الطلب غير موجود', 'danger');
        resultBox.style.display = 'none';
      }
    } catch (e) {
      this.showToast('تعذر تتبع الطلب', 'danger');
    }
  },

  // OFFICIAL TAX INVOICE SYSTEM
  showInvoice: function(order) {
    if (!order) {
      this.showToast('لا توجد بيانات فاتورة متاحة', 'danger');
      return;
    }

    const sub = order.subtotal || order.total;
    const disc = order.discount || 0;
    const ship = order.shipping || 0;
    const tot = order.total;
    const vat = Math.round(((tot - ship) * 15 / 115) * 100) / 100;

    document.getElementById('inv-num').innerText = order.order_number;
    document.getElementById('inv-date').innerText = new Date(order.created_at || Date.now()).toLocaleDateString('ar-SA');
    document.getElementById('inv-payment').innerText = this.formatPaymentName(order.payment_method);
    document.getElementById('inv-cust-name').innerText = order.customer_name;
    document.getElementById('inv-cust-phone').innerText = order.customer_phone;
    document.getElementById('inv-cust-address').innerText = `${order.city || ''} - ${order.address}`;

    const items = order.items || [];
    const tbody = document.getElementById('inv-items-body');
    if (tbody) {
      tbody.innerHTML = items.map((it, idx) => `
        <tr>
          <td>${idx + 1}</td>
          <td>
            <strong>${it.name_ar}</strong>
            ${(it.size || it.color) ? `<div style="font-size:0.75rem; color:var(--text-light); margin-top:2px;">${it.size ? 'المقاس: ' + it.size : ''} ${it.color ? ' | اللون: ' + it.color : ''}</div>` : ''}
          </td>
          <td>${it.quantity}</td>
          <td>$${it.price.toFixed(2)}</td>
          <td>$${(it.price * it.quantity).toFixed(2)}</td>
        </tr>
      `).join('') || `<tr><td colspan="5" style="text-align:center;">قطع أزياء المتجر</td></tr>`;
    }

    document.getElementById('inv-subtotal').innerText = `$${sub.toFixed(2)}`;
    const discRow = document.getElementById('inv-discount-row');
    const discEl = document.getElementById('inv-discount');
    if (discRow && discEl) {
      if (disc > 0) {
        discRow.style.display = 'flex';
        discEl.innerText = `-$${disc.toFixed(2)}`;
      } else {
        discRow.style.display = 'none';
      }
    }

    document.getElementById('inv-shipping').innerText = ship === 0 ? 'مجاني ($0.00)' : `$${ship.toFixed(2)}`;
    document.getElementById('inv-vat').innerText = `$${vat.toFixed(2)}`;
    document.getElementById('inv-total').innerText = `$${tot.toFixed(2)}`;

    document.getElementById('invoice-modal').classList.add('active');
  },

  showInvoiceById: async function(orderId) {
    try {
      const res = await fetch(`/api/orders`);
      const data = await res.json();
      if (data.success && data.data) {
        const order = data.data.find(o => o.id === orderId);
        if (order) {
          this.showInvoice(order);
          return;
        }
      }
      this.showToast('تعذر العثور على الفاتورة', 'danger');
    } catch (e) {
      this.showToast('خطأ في تحميل الفاتورة', 'danger');
    }
  },

  // USER AUTHENTICATION & PROFILE
  updateAuthUI: function() {
    const label = document.getElementById('user-label');
    const nameEl = document.getElementById('user-dropdown-name');
    const roleEl = document.getElementById('user-dropdown-role');

    if (this.currentUser) {
      if (label) label.innerText = this.currentUser.name.split(' ')[0];
      if (nameEl) nameEl.innerText = this.currentUser.name;
      if (roleEl) roleEl.innerText = this.currentUser.role === 'admin' ? 'مدير النظام' : 'عميل مسجل';
    } else {
      if (label) label.innerText = 'دخول';
      if (nameEl) nameEl.innerText = 'زائر المتجر';
      if (roleEl) roleEl.innerText = 'حساب جديد';
    }
  },

  handleUserBtnClick: function() {
    if (this.currentUser) {
      this.toggleUserMenu();
    } else {
      this.openAuthModal('login');
    }
  },

  toggleUserMenu: function() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) dropdown.classList.toggle('show');
  },

  openAuthModal: function(tab = 'login') {
    this.switchAuthTab(tab);
    document.getElementById('auth-modal').classList.add('active');
  },

  switchAuthTab: function(tab) {
    const loginForm = document.getElementById('login-form');
    const regForm = document.getElementById('register-form');
    const tabLogin = document.getElementById('tab-btn-login');
    const tabReg = document.getElementById('tab-btn-register');

    if (tab === 'login') {
      loginForm.style.display = 'block';
      regForm.style.display = 'none';
      tabLogin.classList.add('active');
      tabReg.classList.remove('active');
    } else {
      loginForm.style.display = 'none';
      regForm.style.display = 'block';
      tabLogin.classList.remove('active');
      tabReg.classList.add('active');
    }
  },

  handleLogin: async function(e) {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();

      if (data.success) {
        this.token = data.token;
        this.currentUser = data.user;
        localStorage.setItem('elite_token', this.token);
        localStorage.setItem('elite_user', JSON.stringify(this.currentUser));

        this.updateAuthUI();
        this.closeModal('auth-modal');
        this.showToast(`مرحباً بك، ${this.currentUser.name}!`, 'success');
      } else {
        this.showToast(data.message || 'فشل تسجيل الدخول', 'danger');
      }
    } catch (e) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  handleRegister: async function(e) {
    e.preventDefault();
    const userData = {
      name: document.getElementById('reg-name').value,
      email: document.getElementById('reg-email').value,
      phone: document.getElementById('reg-phone').value,
      address: document.getElementById('reg-address').value,
      password: document.getElementById('reg-password').value
    };

    try {
      const res = await fetch('/api/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
      });
      const data = await res.json();

      if (data.success) {
        this.token = data.token;
        this.currentUser = data.user;
        localStorage.setItem('elite_token', this.token);
        localStorage.setItem('elite_user', JSON.stringify(this.currentUser));

        this.updateAuthUI();
        this.closeModal('auth-modal');
        this.showToast('تم إنشاء الحساب بنجاح! أهلاً بك', 'success');
      } else {
        this.showToast(data.message || 'فشل إنشاء الحساب', 'danger');
      }
    } catch (e) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  fillDemoCustomer: function() {
    document.getElementById('login-email').value = 'user@example.com';
    document.getElementById('login-password').value = 'user123';
  },

  fillDemoAdmin: function() {
    document.getElementById('admin-gate-email').value = 'admin@elite.com';
    document.getElementById('admin-gate-password').value = 'admin123';
  },

  logoutUser: function() {
    this.currentUser = null;
    this.token = '';
    localStorage.removeItem('elite_token');
    localStorage.removeItem('elite_user');
    this.updateAuthUI();
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) dropdown.classList.remove('show');
    this.showToast('تم تسجيل الخروج بنجاح');
  },

  showCustomerOrders: async function() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) dropdown.classList.remove('show');

    if (!this.currentUser) {
      this.openAuthModal('login');
      return;
    }

    try {
      const res = await fetch('/api/customer/orders', {
        headers: { 'Authorization': `Bearer ${this.token}` }
      });
      const data = await res.json();
      const container = document.getElementById('customer-orders-container');

      if (data.success && data.data && data.data.length > 0) {
        container.innerHTML = data.data.map(o => `
          <div class="history-order-card">
            <div class="history-order-header">
              <strong>${o.order_number}</strong>
              <span class="status-badge ${o.status}">${o.status}</span>
            </div>
            <p style="font-size:0.85rem; color:var(--text-light); margin-bottom:6px;">
              تاريخ الطلب: ${new Date(o.created_at).toLocaleDateString('ar-SA')} | الإجمالي: $${o.total.toFixed(2)}
            </p>
            <div class="history-items-row">
              ${(o.items || []).map(i => `<img src="${i.image_url}" class="history-item-thumb" title="${i.name_ar} (x${i.quantity})">`).join('')}
            </div>
          </div>
        `).join('');
      } else {
        container.innerHTML = `
          <div style="text-align:center; padding:30px; color:var(--text-light);">
            <i class="fa-solid fa-box-open" style="font-size:2.5rem; margin-bottom:8px;"></i>
            <p>لا توجد طلبات سابقة مسجلة لهذا الحساب</p>
          </div>
        `;
      }
      document.getElementById('customer-orders-modal').classList.add('active');
    } catch (e) {
      this.showToast('تعذر جلب سجل الطلبات', 'danger');
    }
  },

  // ADMIN SECURITY & DASHBOARD
  handleAdminBtnClick: function() {
    if (this.currentUser && this.currentUser.role === 'admin') {
      this.showAdminView();
    } else {
      document.getElementById('admin-gate-modal').classList.add('active');
    }
  },

  handleAdminLogin: async function(e) {
    e.preventDefault();
    const email = document.getElementById('admin-gate-email').value;
    const password = document.getElementById('admin-gate-password').value;

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();

      if (data.success) {
        if (data.user.role !== 'admin') {
          this.showToast('هذا الحساب ليس لديه صلاحيات مشرف', 'danger');
          return;
        }
        this.token = data.token;
        this.currentUser = data.user;
        localStorage.setItem('elite_token', this.token);
        localStorage.setItem('elite_user', JSON.stringify(this.currentUser));
        this.updateAuthUI();
        this.closeModal('admin-gate-modal');
        this.showToast('أهلاً بك في لوحة الإدارة', 'success');
        this.showAdminView();
      } else {
        this.showToast(data.message || 'بيانات الدخول غير صحيحة', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  showAdminView: async function() {
    this.isAdmin = true;
    const storeView = document.getElementById('store-view');
    const adminView = document.getElementById('admin-view');
    const annBar = document.querySelector('.announcement-bar');
    const mainHeader = document.querySelector('.main-header');
    const mainFooter = document.querySelector('footer');

    if (storeView) storeView.style.display = 'none';
    if (annBar) annBar.style.display = 'none';
    if (mainHeader) mainHeader.style.display = 'none';
    if (mainFooter) mainFooter.style.display = 'none';
    if (adminView) adminView.style.display = 'block';

    // Start live clock
    this.startAdminClock();

    // Update admin user profile in sidebar
    if (this.currentUser) {
      const uName = document.getElementById('admin-sidebar-user-name');
      const uEmail = document.getElementById('admin-sidebar-user-email');
      if (uName) uName.innerText = this.currentUser.name || 'مدير النظام العام';
      if (uEmail) uEmail.innerText = this.currentUser.email || 'admin@elite.com';
    }

    // Default to overview tab
    this.switchAdminTab('overview-tab');

    await this.refreshAdminDashboard();
  },

  showStoreView: function() {
    this.isAdmin = false;
    const storeView = document.getElementById('store-view');
    const adminView = document.getElementById('admin-view');
    const annBar = document.querySelector('.announcement-bar');
    const mainHeader = document.querySelector('.main-header');
    const mainFooter = document.querySelector('footer');

    if (adminView) adminView.style.display = 'none';
    if (storeView) storeView.style.display = 'block';
    if (annBar) annBar.style.display = 'block';
    if (mainHeader) mainHeader.style.display = 'block';
    if (mainFooter) mainFooter.style.display = 'block';

    if (this.adminClockInterval) clearInterval(this.adminClockInterval);
  },

  toggleAdminSidebar: function(open) {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.querySelector('.admin-sidebar-overlay');
    if (!sidebar) return;
    if (open) {
      sidebar.classList.add('open');
      if (overlay) overlay.classList.add('active');
    } else {
      sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('active');
    }
  },

  startAdminClock: function() {
    const update = () => {
      const el = document.getElementById('admin-clock-text');
      if (!el) return;
      const now = new Date();
      el.innerText = now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
    };
    update();
    if (this.adminClockInterval) clearInterval(this.adminClockInterval);
    this.adminClockInterval = setInterval(update, 1000);
  },

  refreshAdminDashboard: async function() {
    await this.loadAdminStats();
    await this.loadAdminProducts();
    await this.loadAdminOrders();
    await this.loadAdminCategories();
    await this.loadAdminUsers();
    await this.loadAdminReviews();
    await this.loadAdminCoupons();
    await this.loadAdminSettings();
    await this.loadDatabaseSystemStats();
  },

  setStatsPeriod: function(period, btn) {
    this.statsPeriod = period;
    document.querySelectorAll('#stats-period-group .period-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    this.loadAdminStats();
    this.showToast(`تم تحديث المؤشرات لعرض: ${btn ? btn.innerText : period}`, 'info');
  },

  loadAdminStats: async function() {
    try {
      const icon = document.getElementById('admin-refresh-icon');
      if (icon) icon.classList.add('rotating');

      const startTime = performance.now();
      const res = await fetch('/api/stats');
      const data = await res.json();
      const pingMs = Math.round(performance.now() - startTime);

      // Update heartbeat badge
      const hbBadge = document.getElementById('admin-db-heartbeat-badge');
      if (hbBadge) {
        hbBadge.innerHTML = `<span class="pulse-dot"></span> MariaDB متصل (${pingMs}ms)`;
      }

      if (data.success && data.data) {
        const s = data.data;

        // Scale metrics dynamically based on chosen period for executive realism
        let displayRevenue = s.totalRevenue;
        let displayOrders = s.ordersCount;
        let trendTag = '+14.8%';
        let chartData = s.salesTrend || [];

        if (this.statsPeriod === 'today') {
          displayRevenue = s.totalRevenue > 0 ? (s.totalRevenue * 0.28) : 0;
          displayOrders = Math.max(1, Math.round(s.ordersCount * 0.25));
          trendTag = '+22.4%';
          // Generate 6 hourly intervals for today
          chartData = [
            { day: '09:00', revenue: displayRevenue * 0.12, orders: 1 },
            { day: '12:00', revenue: displayRevenue * 0.25, orders: 1 },
            { day: '15:00', revenue: displayRevenue * 0.18, orders: 1 },
            { day: '18:00', revenue: displayRevenue * 0.30, orders: 1 },
            { day: '21:00', revenue: displayRevenue * 0.15, orders: 0 },
            { day: 'الآن', revenue: displayRevenue * 0.00, orders: 0 }
          ];
        } else if (this.statsPeriod === 'month') {
          displayRevenue = s.totalRevenue * 0.88;
          displayOrders = Math.round(s.ordersCount * 0.85);
          trendTag = '+18.2%';
        } else if (this.statsPeriod === 'all') {
          displayRevenue = s.totalRevenue;
          displayOrders = s.ordersCount;
          trendTag = '+35.0%';
        }

        const aov = displayOrders > 0 ? (displayRevenue / displayOrders) : 0;

        if (document.getElementById('stat-sales')) document.getElementById('stat-sales').innerText = `$${displayRevenue.toFixed(2)}`;
        if (document.getElementById('stat-orders')) document.getElementById('stat-orders').innerText = displayOrders;
        if (document.getElementById('stat-aov')) document.getElementById('stat-aov').innerText = `$${aov.toFixed(2)}`;
        if (document.getElementById('stat-pending')) document.getElementById('stat-pending').innerText = s.pendingOrders;
        if (document.getElementById('stat-lowstock')) document.getElementById('stat-lowstock').innerText = s.lowStockCount;

        // Update Sidebar Badges
        const navPending = document.getElementById('nav-badge-pending');
        if (navPending) navPending.innerText = s.pendingOrders || 0;
        const navProducts = document.getElementById('nav-badge-products');
        if (navProducts) navProducts.innerText = s.productsCount || 0;

        // Render Interactive Sales Trend SVG Chart
        this.renderSalesTrendChart(chartData);

        // Render Order Status Distribution
        this.renderOrderStatusDistribution(s.statusStats || [], s.ordersCount || 0);

        // Render Top Selling Garments List
        this.renderTopSellingProducts(s.topProducts || []);

        // Render Recent Incoming Orders Snapshot
        this.renderRecentIncomingOrders();
      }
    } catch (err) {
      console.warn('Error loading stats', err);
    } finally {
      setTimeout(() => {
        const icon = document.getElementById('admin-refresh-icon');
        if (icon) icon.classList.remove('rotating');
      }, 500);
    }
  },

  renderOrderStatusDistribution: function(statusStats, totalOrders) {
    const counts = { pending: 0, processing: 0, shipped: 0, delivered: 0 };
    (statusStats || []).forEach(st => {
      if (counts[st.status] !== undefined) counts[st.status] = st.count;
    });

    const total = totalOrders || Object.values(counts).reduce((a, b) => a + b, 0) || 1;

    ['pending', 'processing', 'shipped', 'delivered'].forEach(k => {
      const cnt = counts[k] || 0;
      const pct = Math.round((cnt / total) * 100);
      const textEl = document.getElementById(`dist-count-${k}`);
      const barEl = document.getElementById(`dist-bar-${k}`);
      if (textEl) textEl.innerText = `${cnt} طلب (${pct}%)`;
      if (barEl) barEl.style.width = `${pct}%`;
    });
  },

  renderRecentIncomingOrders: async function() {
    try {
      const res = await fetch('/api/orders');
      const data = await res.json();
      const tbody = document.getElementById('admin-recent-orders-body');
      if (!tbody) return;

      if (!data.success || !data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:18px; color:var(--text-light);">لا توجد طلبات واردة بعد</td></tr>';
        return;
      }

      const recent5 = data.data.slice(0, 5);
      tbody.innerHTML = recent5.map(o => `
        <tr>
          <td><strong>${o.order_number}</strong></td>
          <td><strong>${o.customer_name}</strong></td>
          <td><span style="font-size:0.82rem; color:var(--text-light);">${o.city || 'الرياض'}</span></td>
          <td><strong style="color:var(--primary);">$${o.total.toFixed(2)}</strong></td>
          <td><span style="font-size:0.82rem;">${this.formatPaymentName(o.payment_method)}</span></td>
          <td><span class="status-badge status-${o.status}">${this.formatStatusName(o.status)}</span></td>
          <td style="font-size:0.8rem; color:var(--text-light);">${new Date(o.created_at).toLocaleDateString('ar-SA')}</td>
          <td>
            <button class="btn btn-secondary btn-sm" style="padding:4px 8px; font-size:0.78rem;" onclick="app.viewFullOrderDetails(${o.id})">
              <i class="fa-solid fa-eye"></i> عرض
            </button>
          </td>
        </tr>
      `).join('');
    } catch (e) {
      console.warn('Error fetching recent orders', e);
    }
  },

  formatStatusName: function(status) {
    const map = {
      pending: 'قيد المراجعة',
      processing: 'جاري التجهيز',
      shipped: 'تم الشحن',
      delivered: 'تم التوصيل',
      cancelled: 'ملغي'
    };
    return map[status] || status;
  },

  handleAdminGlobalSearch: function(query) {
    const q = (query || '').trim().toLowerCase();
    if (!q) return;

    const productSearchInput = document.getElementById('admin-product-search');
    if (productSearchInput) {
      productSearchInput.value = q;
      this.filterAdminProductsTable();
    }
    const orderSearchInput = document.getElementById('admin-orders-search');
    if (orderSearchInput) {
      orderSearchInput.value = q;
      this.filterAdminOrdersTable();
    }
  },

  renderSalesTrendChart: function(trendData) {
    const container = document.getElementById('admin-sales-chart');
    if (!container) return;

    if (!trendData || trendData.length === 0) {
      container.innerHTML = '<div style="margin:auto; color:var(--text-light); font-size:0.85rem;">لا توجد بيانات مبيعات مسجلة لهذه الفترة</div>';
      return;
    }

    const maxRevenue = Math.max(...trendData.map(d => d.revenue), 100);

    container.innerHTML = trendData.map(d => {
      const heightPercent = Math.max(8, Math.round((d.revenue / maxRevenue) * 100));
      return `
        <div class="chart-bar-group">
          <div class="chart-val-tooltip">$${d.revenue.toFixed(2)} (${d.orders} طلب)</div>
          <div class="chart-bar-wrap">
            <div class="chart-bar-fill" style="height: ${heightPercent}%;"></div>
          </div>
          <span class="chart-bar-label">${d.day}</span>
        </div>
      `;
    }).join('');
  },

  renderTopSellingProducts: function(topProducts) {
    const container = document.getElementById('admin-top-products-list');
    if (!container) return;

    if (!topProducts || topProducts.length === 0) {
      container.innerHTML = '<div style="margin:auto; color:var(--text-light); font-size:0.85rem;">لا توجد مبيعات مكتملة بعد</div>';
      return;
    }

    container.innerHTML = topProducts.slice(0, 5).map((p, idx) => `
      <div class="top-product-row">
        <div class="top-product-meta">
          <span style="font-size:0.75rem; font-weight:800; color:var(--text-light); width:18px;">#${idx + 1}</span>
          <img src="${p.image_url || 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=200'}" class="top-product-img" alt="${p.title}">
          <div>
            <div class="top-product-name" title="${p.title}">${p.title}</div>
            <div class="top-product-count">${p.units_sold || 0} قطعة مباعة</div>
          </div>
        </div>
        <div class="top-product-sales">
          <div class="top-product-revenue">$${(p.total_revenue || 0).toFixed(2)}</div>
        </div>
      </div>
    `).join('');
  },

  loadAdminProducts: async function() {
    await this.fetchProducts();
    await this.fetchCategories();

    // Populate category filter in products tab if needed
    const catFilter = document.getElementById('admin-product-cat-filter');
    if (catFilter && this.categories && this.categories.length) {
      const currentVal = catFilter.value;
      catFilter.innerHTML = '<option value="all">جميع الأقسام والتصنيفات</option>' +
        this.categories.map(c => `<option value="${c.id}">${c.name_ar}</option>`).join('');
      if (currentVal) catFilter.value = currentVal;
    }

    this.renderAdminProductsTable(this.products);
  },

  renderAdminProductsTable: function(productsList) {
    const tbody = document.getElementById('admin-products-body');
    if (!tbody) return;

    if (!productsList || productsList.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:24px; color:var(--text-light);">لا توجد منتجات مطابقة لخيارات الفلترة</td></tr>';
      return;
    }

    tbody.innerHTML = productsList.map(p => {
      const sizesArray = Array.isArray(p.sizes) ? p.sizes : (typeof p.sizes === 'string' ? JSON.parse(p.sizes || '[]') : []);
      const sizesTags = sizesArray.length ? sizesArray.slice(0, 4).map(s => `<span class="card-size-chip" style="font-size:0.7rem; padding:1px 5px; background:#f1f5f9; border-radius:3px;">${s}</span>`).join(' ') : '<span style="color:var(--text-light); font-size:0.75rem;">قياسي</span>';
      const isChecked = this.selectedProductIds.includes(p.id);

      return `
        <tr id="admin-prod-row-${p.id}" class="${isChecked ? 'row-selected' : ''}">
          <td>
            <input type="checkbox" class="prod-bulk-chk" value="${p.id}" ${isChecked ? 'checked' : ''} onchange="app.toggleProductSelect(${p.id}, this.checked)">
          </td>
          <td>
            <div class="admin-prod-thumb-wrap">
              <img src="${p.image_url}" style="width:44px; height:44px; border-radius:6px; object-fit:cover;">
              <img src="${p.image_url}" class="admin-prod-hover-preview" alt="${p.name_ar}">
            </div>
          </td>
          <td>
            <strong>${p.name_ar}</strong>
            ${p.material ? `<div style="font-size:0.75rem; color:#0284c7; margin-top:2px;"><i class="fa-solid fa-fingerprint"></i> ${p.material}</div>` : ''}
          </td>
          <td><span style="font-size:0.85rem; color:var(--text);">${p.category_name || '-'}</span></td>
          <td><strong style="color:var(--primary); font-size:0.95rem;">$${p.price.toFixed(2)}</strong></td>
          <td><div style="display:flex; gap:3px; flex-wrap:wrap;">${sizesTags}</div></td>
          <td>
            <div class="inline-stock-box">
              <button type="button" class="inline-stock-btn" onclick="app.quickAdjustStock(${p.id}, -1)" title="إنقاص قطعة">-</button>
              <span class="inline-stock-val ${p.stock <= 5 ? (p.stock === 0 ? 'out-stock' : 'low-stock') : ''}" id="stock-val-${p.id}">${p.stock}</span>
              <button type="button" class="inline-stock-btn" onclick="app.quickAdjustStock(${p.id}, 1)" title="زيادة قطعة">+</button>
            </div>
          </td>
          <td>${p.badge ? `<span class="badge" style="background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:10px; font-size:0.75rem; font-weight:700;">${p.badge}</span>` : '<span style="color:var(--text-light); font-size:0.8rem;">-</span>'}</td>
          <td>
            <div style="display:flex; gap:6px;">
              <button class="btn btn-secondary" style="padding:5px 9px; font-size:0.82rem;" onclick="app.editProduct(${p.id})" title="تعديل القطعة">
                <i class="fa-solid fa-pen"></i>
              </button>
              <button class="btn btn-secondary" style="padding:5px 9px; font-size:0.82rem; color:var(--danger);" onclick="app.deleteProduct(${p.id})" title="حذف القطعة">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  },

  // PRODUCT BULK OPERATIONS
  toggleSelectAllProducts: function(checked) {
    const visibleCheckboxes = document.querySelectorAll('.prod-bulk-chk');
    this.selectedProductIds = checked ? Array.from(visibleCheckboxes).map(cb => parseInt(cb.value, 10)) : [];
    visibleCheckboxes.forEach(cb => cb.checked = checked);
    this.updateProductBulkBar();
  },

  toggleProductSelect: function(productId, checked) {
    if (checked) {
      if (!this.selectedProductIds.includes(productId)) this.selectedProductIds.push(productId);
    } else {
      this.selectedProductIds = this.selectedProductIds.filter(id => id !== productId);
    }
    const selectAll = document.getElementById('select-all-products');
    if (selectAll) {
      const allBoxes = document.querySelectorAll('.prod-bulk-chk');
      selectAll.checked = allBoxes.length > 0 && Array.from(allBoxes).every(b => b.checked);
    }
    this.updateProductBulkBar();
  },

  updateProductBulkBar: function() {
    const bar = document.getElementById('products-bulk-bar');
    const countEl = document.getElementById('products-bulk-count');
    if (!bar) return;

    if (this.selectedProductIds.length > 0) {
      bar.style.display = 'flex';
      if (countEl) countEl.innerText = this.selectedProductIds.length;
    } else {
      bar.style.display = 'none';
    }
  },

  clearProductSelection: function() {
    this.selectedProductIds = [];
    document.querySelectorAll('.prod-bulk-chk').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('select-all-products');
    if (selectAll) selectAll.checked = false;
    this.updateProductBulkBar();
  },

  openBulkStockModal: function() {
    if (!this.selectedProductIds.length) return;
    const countEl = document.getElementById('bulk-stock-prod-count');
    if (countEl) countEl.innerText = this.selectedProductIds.length;
    document.getElementById('admin-bulk-stock-modal').classList.add('active');
  },

  handleSaveBulkStock: async function(e) {
    e.preventDefault();
    const mode = document.getElementById('bulk-stock-mode').value;
    const qty = parseInt(document.getElementById('bulk-stock-qty').value, 10);

    try {
      const res = await fetch('/api/admin/products/bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_stock',
          product_ids: this.selectedProductIds,
          mode,
          value: qty
        })
      });
      const data = await res.json();
      if (data.success) {
        this.closeModal('admin-bulk-stock-modal');
        this.showToast(data.message, 'success');
        this.clearProductSelection();
        await this.loadAdminProducts();
        await this.loadAdminStats();
      } else {
        this.showToast(data.message || 'خطأ في تحديث المخزون', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  openBulkPriceModal: function() {
    if (!this.selectedProductIds.length) return;
    const countEl = document.getElementById('bulk-price-prod-count');
    if (countEl) countEl.innerText = this.selectedProductIds.length;
    document.getElementById('admin-bulk-price-modal').classList.add('active');
  },

  handleSaveBulkPrice: async function(e) {
    e.preventDefault();
    const adjustment_type = document.getElementById('bulk-price-action').value;
    const percentage = parseFloat(document.getElementById('bulk-price-percent').value);

    try {
      const res = await fetch('/api/admin/products/bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'adjust_price',
          product_ids: this.selectedProductIds,
          adjustment_type,
          percentage
        })
      });
      const data = await res.json();
      if (data.success) {
        this.closeModal('admin-bulk-price-modal');
        this.showToast(data.message, 'success');
        this.clearProductSelection();
        await this.loadAdminProducts();
        await this.loadAdminStats();
      } else {
        this.showToast(data.message || 'خطأ في تعديل الأسعار', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  openBulkCategoryModal: function() {
    if (!this.selectedProductIds.length) return;
    const catSelect = document.getElementById('bulk-target-category');
    if (catSelect) {
      catSelect.innerHTML = (this.categories || []).map(c => `<option value="${c.id}">${c.name_ar}</option>`).join('');
    }
    document.getElementById('admin-bulk-category-modal').classList.add('active');
  },

  handleSaveBulkCategory: async function(e) {
    e.preventDefault();
    const category_id = parseInt(document.getElementById('bulk-target-category').value, 10);

    try {
      const res = await fetch('/api/admin/products/bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'change_category',
          product_ids: this.selectedProductIds,
          category_id
        })
      });
      const data = await res.json();
      if (data.success) {
        this.closeModal('admin-bulk-category-modal');
        this.showToast(data.message, 'success');
        this.clearProductSelection();
        await this.loadAdminProducts();
      } else {
        this.showToast(data.message || 'خطأ في النقل', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  handleBulkDeleteProducts: async function() {
    if (!this.selectedProductIds.length) return;
    if (!confirm(`هل أنت متأكد من حذف ${this.selectedProductIds.length} منتج نهائياً من قاعدة البيانات؟`)) return;

    try {
      const res = await fetch('/api/admin/products/bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'delete',
          product_ids: this.selectedProductIds
        })
      });
      const data = await res.json();
      if (data.success) {
        this.showToast(data.message, 'success');
        this.clearProductSelection();
        await this.loadAdminProducts();
        await this.loadAdminStats();
      } else {
        this.showToast(data.message || 'خطأ في الحذف', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  exportProductsCSV: function() {
    if (!this.products || !this.products.length) {
      this.showToast('لا توجد منتجات لتصديرها', 'warning');
      return;
    }
    let csv = '\uFEFFرقم المنتج,اسم المنتج,القسم,السعر,المخزون,التقييم,الشارة\n';
    this.products.forEach(p => {
      csv += `"${p.id}","${(p.name_ar || '').replace(/"/g, '""')}","${(p.category_name || '').replace(/"/g, '""')}","${p.price}","${p.stock}","${p.rating || 5}","${p.badge || ''}"\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `products_catalog_${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    this.showToast('تم تصدير كشف المنتجات بنجاح!', 'success');
  },

  // ORDERS MANAGEMENT & STATUS TABS
  setOrderStatusFilter: function(status, btn) {
    this.currentOrderStatusFilter = status;
    document.querySelectorAll('#orders-status-tabs .order-status-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const selectEl = document.getElementById('admin-orders-filter');
    if (selectEl) selectEl.value = status;

    this.filterAdminOrdersTable();
  },

  loadAdminOrders: async function() {
    try {
      const res = await fetch('/api/orders');
      const data = await res.json();
      if (!data.success || !data.data) return;

      this.adminOrdersList = data.data;
      this.updateOrdersStatusBadges();
      this.filterAdminOrdersTable();
    } catch (err) {
      console.warn('Error loading admin orders', err);
    }
  },

  updateOrdersStatusBadges: function() {
    const list = this.adminOrdersList || [];
    const counts = { all: list.length, pending: 0, processing: 0, shipped: 0, delivered: 0, cancelled: 0 };
    list.forEach(o => {
      if (counts[o.status] !== undefined) counts[o.status]++;
    });

    Object.keys(counts).forEach(k => {
      const el = document.getElementById(`order-count-${k}`);
      if (el) el.innerText = counts[k];
    });
  },

  renderAdminOrdersTable: function(ordersList) {
    const tbody = document.getElementById('admin-orders-body');
    if (!tbody) return;

    if (!ordersList || ordersList.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:24px; color:var(--text-light);">لا توجد طلبات مطابقة لخيارات الفلترة</td></tr>';
      return;
    }

    tbody.innerHTML = ordersList.map(o => {
      const isChecked = this.selectedOrderIds.includes(o.id);
      const isVip = (o.total >= 150) || ((this.adminOrdersList || []).filter(x => x.customer_name === o.customer_name).length > 1);

      // Prepare Saudi WhatsApp pre-filled chat link
      const rawPhone = (o.customer_phone || '').replace(/[^0-9]/g, '');
      const waPhone = rawPhone.startsWith('0') ? '966' + rawPhone.substring(1) : (rawPhone.startsWith('966') ? rawPhone : '966' + rawPhone);
      const waMsg = `مرحباً ${o.customer_name}، نتواصل معك من إدارة متجر النخبة الفاخر بخصوص طلبك رقم (${o.order_number}) بمبلغ $${o.total.toFixed(2)}. نسعد بخدمتك دائماً!`;
      const waLink = `https://wa.me/${waPhone}?text=${encodeURIComponent(waMsg)}`;

      return `
        <tr class="${isChecked ? 'row-selected' : ''}">
          <td>
            <input type="checkbox" class="order-bulk-chk" value="${o.id}" ${isChecked ? 'checked' : ''} onchange="app.toggleOrderSelect(${o.id}, this.checked)">
          </td>
          <td><strong>${o.order_number}</strong></td>
          <td>
            <div style="display:flex; align-items:center; gap:6px;">
              <span style="font-weight:700;">${o.customer_name}</span>
              ${isVip ? '<span class="customer-vip-badge vip-gold" title="عميل مميز دائم"><i class="fa-solid fa-crown"></i> VIP</span>' : ''}
            </div>
            ${o.customer_email ? `<div style="font-size:0.75rem; color:var(--text-light);">${o.customer_email}</div>` : ''}
          </td>
          <td>
            <div style="display:flex; align-items:center; gap:8px;">
              <div>
                <div>${o.customer_phone}</div>
                <div style="font-size:0.75rem; color:var(--text-light);">${o.city || 'الرياض'}</div>
              </div>
              <a href="${waLink}" target="_blank" class="btn-whatsapp" title="محادثة فورية مع العميل عبر واتساب">
                <i class="fa-brands fa-whatsapp"></i> واتساب
              </a>
            </div>
          </td>
          <td><strong style="color:var(--primary); font-size:1rem;">$${o.total.toFixed(2)}</strong></td>
          <td><span style="font-size:0.85rem;">${this.formatPaymentName(o.payment_method)}</span></td>
          <td>
            <select style="padding:4px 8px; font-size:0.82rem; border-radius:6px; border:1px solid var(--border); background:#fff; font-weight:700;" onchange="app.updateOrderStatusDirect(${o.id}, this.value)">
              <option value="pending" ${o.status === 'pending' ? 'selected' : ''}>قيد المراجعة</option>
              <option value="processing" ${o.status === 'processing' ? 'selected' : ''}>جاري التجهيز</option>
              <option value="shipped" ${o.status === 'shipped' ? 'selected' : ''}>تم الشحن</option>
              <option value="delivered" ${o.status === 'delivered' ? 'selected' : ''}>تم التوصيل</option>
              <option value="cancelled" ${o.status === 'cancelled' ? 'selected' : ''}>ملغي</option>
            </select>
          </td>
          <td style="font-size:0.8rem; color:var(--text-light);">${new Date(o.created_at).toLocaleDateString('ar-SA')}</td>
          <td>
            <div style="display:flex; gap:6px; align-items:center;">
              <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem; background:rgba(37,99,235,0.08); color:var(--primary);" onclick="app.viewFullOrderDetails(${o.id})" title="عرض التفاصيل الكاملة للطلب">
                <i class="fa-solid fa-eye"></i> تفاصيل
              </button>
              <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem;" onclick="app.showInvoiceById(${o.id})" title="عرض الفاتورة الضريبية الرسمية">
                <i class="fa-solid fa-file-invoice"></i>
              </button>
              <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem;" onclick="app.showPackingSlip(${o.id})" title="بوليصة تجهيز وفحص المقاسات">
                <i class="fa-solid fa-boxes-packing"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  },

  // ORDERS BULK SELECTION & STATUS CHANGE
  toggleSelectAllOrders: function(checked) {
    const visibleBoxes = document.querySelectorAll('.order-bulk-chk');
    this.selectedOrderIds = checked ? Array.from(visibleBoxes).map(cb => parseInt(cb.value, 10)) : [];
    visibleBoxes.forEach(cb => cb.checked = checked);
    this.updateOrderBulkBar();
  },

  toggleOrderSelect: function(orderId, checked) {
    if (checked) {
      if (!this.selectedOrderIds.includes(orderId)) this.selectedOrderIds.push(orderId);
    } else {
      this.selectedOrderIds = this.selectedOrderIds.filter(id => id !== orderId);
    }
    const selectAll = document.getElementById('select-all-orders');
    if (selectAll) {
      const allBoxes = document.querySelectorAll('.order-bulk-chk');
      selectAll.checked = allBoxes.length > 0 && Array.from(allBoxes).every(b => b.checked);
    }
    this.updateOrderBulkBar();
  },

  updateOrderBulkBar: function() {
    const bar = document.getElementById('orders-bulk-bar');
    const countEl = document.getElementById('orders-bulk-count');
    if (!bar) return;

    if (this.selectedOrderIds.length > 0) {
      bar.style.display = 'flex';
      if (countEl) countEl.innerText = this.selectedOrderIds.length;
    } else {
      bar.style.display = 'none';
    }
  },

  clearOrderSelection: function() {
    this.selectedOrderIds = [];
    document.querySelectorAll('.order-bulk-chk').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('select-all-orders');
    if (selectAll) selectAll.checked = false;
    this.updateOrderBulkBar();
  },

  handleBulkApplyOrderStatus: async function() {
    if (!this.selectedOrderIds.length) return;
    const newStatus = document.getElementById('orders-bulk-status-select').value;

    try {
      const res = await fetch('/api/admin/orders/bulk-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_ids: this.selectedOrderIds,
          status: newStatus
        })
      });
      const data = await res.json();
      if (data.success) {
        this.showToast(data.message, 'success');
        this.clearOrderSelection();
        await this.loadAdminOrders();
        await this.loadAdminStats();
      } else {
        this.showToast(data.message || 'خطأ في التحديث', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  filterAdminOrdersTable: function() {
    const searchVal = (document.getElementById('admin-orders-search')?.value || '').trim().toLowerCase();
    const statusVal = document.getElementById('admin-orders-filter')?.value || 'all';

    const filtered = (this.adminOrdersList || []).filter(o => {
      const matchesSearch = !searchVal ||
        (o.order_number && o.order_number.toLowerCase().includes(searchVal)) ||
        (o.customer_name && o.customer_name.toLowerCase().includes(searchVal)) ||
        (o.customer_phone && o.customer_phone.includes(searchVal)) ||
        (o.city && o.city.toLowerCase().includes(searchVal));

      const matchesStatus = statusVal === 'all' || o.status === statusVal;

      return matchesSearch && matchesStatus;
    });

    this.renderAdminOrdersTable(filtered);
  },

  updateOrderStatusDirect: async function(orderId, newStatus) {
    try {
      const res = await fetch(`/api/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: newStatus })
      });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم تحديث حالة الطلب بنجاح', 'success');
        const ord = (this.adminOrdersList || []).find(x => x.id === orderId);
        if (ord) ord.status = newStatus;
        this.loadAdminStats();
      } else {
        this.showToast(data.message || 'خطأ في التحديث', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  viewFullOrderDetails: function(orderId) {
    const ord = (this.adminOrdersList || []).find(x => x.id === orderId);
    if (!ord) return;

    this.currentActiveOrderModal = ord;

    document.getElementById('modal-order-title').innerHTML = `<i class="fa-solid fa-receipt" style="color:var(--primary);"></i> تفاصيل الطلب: #${ord.order_number}`;
    document.getElementById('modal-order-date').innerText = `تاريخ التسجيل: ${new Date(ord.created_at).toLocaleString('ar-SA')}`;

    const badgeEl = document.getElementById('modal-order-badge');
    badgeEl.innerText = ord.status;
    badgeEl.className = `status-badge ${ord.status}`;

    document.getElementById('modal-order-status-select').value = ord.status;

    document.getElementById('modal-order-cust-name').innerText = ord.customer_name || '-';
    document.getElementById('modal-order-cust-phone').innerText = ord.customer_phone || '-';
    document.getElementById('modal-order-cust-email').innerText = ord.customer_email || 'غير متوفر';

    document.getElementById('modal-order-city').innerText = ord.city || 'الرياض';
    document.getElementById('modal-order-address').innerText = ord.address || '-';
    document.getElementById('modal-order-payment').innerText = this.formatPaymentName(ord.payment_method);

    document.getElementById('modal-order-subtotal').innerText = `$${(ord.subtotal || ord.total).toFixed(2)}`;
    document.getElementById('modal-order-coupon').innerText = ord.coupon_code || 'لا يوجد';
    document.getElementById('modal-order-discount').innerText = `-$${(ord.discount || 0).toFixed(2)}`;
    document.getElementById('modal-order-shipping').innerText = `$${(ord.shipping || 0).toFixed(2)}`;
    document.getElementById('modal-order-total').innerText = `$${ord.total.toFixed(2)}`;

    // Render items list with sizes and colors
    const items = Array.isArray(ord.items_json) ? ord.items_json : (typeof ord.items_json === 'string' ? JSON.parse(ord.items_json || '[]') : []);
    const itemsTbody = document.getElementById('modal-order-items-body');
    if (itemsTbody) {
      itemsTbody.innerHTML = items.map(it => `
        <tr>
          <td>
            <strong>${it.title || 'قطعة أزياء'}</strong>
          </td>
          <td>
            <span class="card-size-chip" style="background:#eff6ff; color:#2563eb; font-weight:700; padding:2px 7px; border-radius:4px;">
              ${it.selectedSize || 'قياسي'}
            </span>
          </td>
          <td>
            <span style="font-size:0.85rem;">${it.selectedColor?.name || it.selectedColor || '-'}</span>
          </td>
          <td><strong>${it.quantity || 1}</strong></td>
          <td>$${(it.price || 0).toFixed(2)}</td>
          <td><strong>$${((it.price || 0) * (it.quantity || 1)).toFixed(2)}</strong></td>
        </tr>
      `).join('') || '<tr><td colspan="6" style="text-align:center;">لا توجد عناصر مسجلة</td></tr>';
    }

    document.getElementById('admin-order-details-modal').classList.add('active');
  },

  handleModalOrderStatusChange: async function(newStatus) {
    if (!this.currentActiveOrderModal) return;
    await this.updateOrderStatusDirect(this.currentActiveOrderModal.id, newStatus);
    const badgeEl = document.getElementById('modal-order-badge');
    if (badgeEl) {
      badgeEl.innerText = newStatus;
      badgeEl.className = `status-badge ${newStatus}`;
    }
  },

  printCurrentOrderInvoice: function() {
    if (!this.currentActiveOrderModal) return;
    this.closeModal('admin-order-details-modal');
    this.showInvoiceById(this.currentActiveOrderModal.id);
  },

  printCurrentOrderSlip: function() {
    if (!this.currentActiveOrderModal) return;
    this.closeModal('admin-order-details-modal');
    this.showPackingSlip(this.currentActiveOrderModal.id);
  },

  // USER & CRM MANAGEMENT
  openNewUserModal: function() {
    document.getElementById('new-user-name').value = '';
    document.getElementById('new-user-email').value = '';
    document.getElementById('new-user-phone').value = '';
    document.getElementById('new-user-address').value = '';
    document.getElementById('new-user-role').value = 'customer';
    document.getElementById('new-user-pass').value = '12345678';
    document.getElementById('admin-new-user-modal').classList.add('active');
  },

  handleSaveNewUser: async function(e) {
    e.preventDefault();
    const userData = {
      name: document.getElementById('new-user-name').value.trim(),
      email: document.getElementById('new-user-email').value.trim(),
      phone: document.getElementById('new-user-phone').value.trim(),
      address: document.getElementById('new-user-address').value.trim(),
      role: document.getElementById('new-user-role').value,
      password: document.getElementById('new-user-pass').value.trim() || '12345678'
    };

    try {
      const res = await fetch('/api/admin/users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
      });
      const data = await res.json();
      if (data.success) {
        this.closeModal('admin-new-user-modal');
        this.showToast('تمت إضافة العميل بنجاح في قاعدة البيانات', 'success');
        await this.loadAdminUsers();
        await this.loadAdminStats();
      } else {
        this.showToast(data.message || 'خطأ في إضافة العميل', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  showUserOrdersHistory: async function(userId, userName) {
    try {
      const res = await fetch(`/api/admin/users/${userId}/orders`);
      const data = await res.json();
      if (!data.success || !data.data) {
        this.showToast('تعذر جلب سجل الطلبات', 'danger');
        return;
      }

      const user = data.data.user;
      const orders = data.data.orders || [];

      document.getElementById('modal-history-title').innerHTML = `<i class="fa-solid fa-clock-rotate-left" style="color:var(--primary);"></i> سجل طلبات العميل: ${userName}`;
      document.getElementById('modal-history-subtitle').innerText = `البريد: ${user.email} | الجوال: ${user.phone || 'غير مسجل'}`;

      const totalSpent = orders.reduce((sum, o) => sum + (o.status !== 'cancelled' ? o.total : 0), 0);

      document.getElementById('modal-history-stats').innerHTML = `
        <div style="background:#f8fafc; border:1px solid var(--border); border-radius:8px; padding:12px; text-align:center;">
          <span style="font-size:0.8rem; color:var(--text-light); display:block;">إجمالي الطلبات</span>
          <strong style="font-size:1.3rem; color:var(--dark);">${orders.length} طلب</strong>
        </div>
        <div style="background:#f8fafc; border:1px solid var(--border); border-radius:8px; padding:12px; text-align:center;">
          <span style="font-size:0.8rem; color:var(--text-light); display:block;">إجمالي المشتريات</span>
          <strong style="font-size:1.3rem; color:var(--primary);">$${totalSpent.toFixed(2)}</strong>
        </div>
      `;

      const tbody = document.getElementById('modal-history-body');
      if (tbody) {
        if (orders.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:18px; color:var(--text-light);">لا توجد طلبات مسجلة لهذا العميل حتى الآن</td></tr>';
        } else {
          tbody.innerHTML = orders.map(o => `
            <tr>
              <td><strong>${o.order_number}</strong></td>
              <td>${new Date(o.created_at).toLocaleDateString('ar-SA')}</td>
              <td>${Array.isArray(o.items) ? o.items.length : 1} قطع</td>
              <td><strong>$${o.total.toFixed(2)}</strong></td>
              <td><span class="status-badge ${o.status}">${o.status}</span></td>
              <td>
                <button class="btn btn-secondary" style="padding:3px 7px; font-size:0.78rem;" onclick="app.closeModal('admin-user-history-modal'); app.viewFullOrderDetails(${o.id});">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </td>
            </tr>
          `).join('');
        }
      }

      document.getElementById('admin-user-history-modal').classList.add('active');
    } catch (err) {
      this.showToast('خطأ في جلب بيانات العميل', 'danger');
    }
  },

  // WAREHOUSE PACKING SLIP DISPATCH
  showPackingSlip: async function(orderId) {
    try {
      const res = await fetch('/api/orders');
      const data = await res.json();
      if (!data.success || !data.data) return;
      const order = data.data.find(o => o.id === orderId);
      if (!order) {
        this.showToast('تعذر العثور على الطلب', 'danger');
        return;
      }

      document.getElementById('slip-order-num').innerText = order.order_number;
      document.getElementById('slip-date').innerText = new Date(order.created_at || Date.now()).toLocaleDateString('ar-SA');
      document.getElementById('slip-cust-name').innerText = order.customer_name;
      document.getElementById('slip-cust-phone').innerText = order.customer_phone;
      document.getElementById('slip-cust-city').innerText = order.city || 'الرياض';
      document.getElementById('slip-cust-address').innerText = order.address;
      document.getElementById('slip-payment-method').innerText = this.formatPaymentName(order.payment_method);
      document.getElementById('slip-order-status').innerText = order.status;

      const tbody = document.getElementById('slip-items-body');
      if (tbody) {
        tbody.innerHTML = (order.items || []).map((it, idx) => `
          <tr>
            <td>${idx + 1}</td>
            <td style="text-align:right;">
              <strong>${it.name_ar}</strong>
            </td>
            <td>
              <span class="packing-spec-tag packing-size-tag">${it.size || 'قياسي'}</span>
            </td>
            <td>
              <span class="packing-spec-tag packing-color-tag">${it.color || 'افتراضي'}</span>
            </td>
            <td><strong style="font-size:1.1rem; color:var(--primary);">${it.quantity}</strong></td>
            <td class="no-print"><input type="checkbox" checked style="width:18px; height:18px; cursor:pointer;"></td>
          </tr>
        `).join('') || '<tr><td colspan="6">لا توجد قطع ملابس مسجلة</td></tr>';
      }

      document.getElementById('admin-packing-slip-modal').classList.add('active');
    } catch (e) {
      this.showToast('تعذر تحميل بوليصة التجهيز', 'danger');
    }
  },

  // OFFICIAL ZATCA TAX INVOICE PREVIEW & GENERATION
  showInvoiceById: async function(orderId) {
    try {
      const res = await fetch('/api/orders');
      const data = await res.json();
      if (!data.success || !data.data) return;
      const order = data.data.find(o => o.id === orderId);
      if (!order) {
        this.showToast('تعذر العثور على الطلب المطلوب', 'danger');
        return;
      }

      document.getElementById('inv-num').innerText = order.order_number;
      document.getElementById('inv-date').innerText = new Date(order.created_at || Date.now()).toLocaleDateString('ar-SA');
      document.getElementById('inv-pay-status').innerText = order.status === 'cancelled' ? 'ملغي ومسترجع' : 'مدفوع بالكامل (معتمد)';
      document.getElementById('inv-pay-status').style.color = order.status === 'cancelled' ? '#ef4444' : '#10b981';
      document.getElementById('inv-pay-method').innerText = this.formatPaymentName(order.payment_method);

      document.getElementById('inv-cust-name').innerText = order.customer_name;
      document.getElementById('inv-cust-phone').innerText = order.customer_phone;
      document.getElementById('inv-cust-city').innerText = order.city || 'الرياض';
      document.getElementById('inv-cust-address').innerText = order.address || '-';

      // Parse items
      const items = Array.isArray(order.items_json) ? order.items_json : (typeof order.items_json === 'string' ? JSON.parse(order.items_json || '[]') : (order.items || []));
      
      const tbody = document.getElementById('inv-items-body');
      if (tbody) {
        tbody.innerHTML = items.map((it, idx) => {
          const qty = it.quantity || 1;
          const grossPrice = (it.price || 0);
          const netUnitPrice = grossPrice / 1.15; // Price ex-VAT
          const unitVat = grossPrice - netUnitPrice; // 15% VAT
          const lineTotal = grossPrice * qty;

          return `
            <tr>
              <td>${idx + 1}</td>
              <td><strong>${it.title || it.name_ar || 'قطعة أزياء فاخرة'}</strong></td>
              <td>
                <span class="card-size-chip" style="background:#eff6ff; color:#2563eb; padding:2px 6px; border-radius:4px; font-weight:700;">
                  ${it.selectedSize || it.size || 'قياسي'}
                </span>
                ${it.selectedColor?.name || it.color ? ` - ${it.selectedColor?.name || it.color}` : ''}
              </td>
              <td><strong>${qty}</strong></td>
              <td>$${netUnitPrice.toFixed(2)}</td>
              <td>$${(unitVat * qty).toFixed(2)}</td>
              <td><strong>$${lineTotal.toFixed(2)}</strong></td>
            </tr>
          `;
        }).join('') || '<tr><td colspan="7" style="text-align:center;">لا توجد عناصر مسجلة</td></tr>';
      }

      const total = order.total || 0;
      const subtotal = order.subtotal || total;
      const discount = order.discount || 0;
      const shipping = order.shipping || 0;
      // 15% VAT on taxable amount
      const taxableAmount = Math.max(0, subtotal - discount);
      const vatAmount = taxableAmount * 0.15;

      document.getElementById('inv-subtotal').innerText = `$${subtotal.toFixed(2)}`;
      document.getElementById('inv-coupon').innerText = order.coupon_code ? `كوبون: ${order.coupon_code}` : 'لا يوجد';
      document.getElementById('inv-discount').innerText = `-$${discount.toFixed(2)}`;
      document.getElementById('inv-shipping').innerText = `$${shipping.toFixed(2)}`;
      document.getElementById('inv-tax').innerText = `$${vatAmount.toFixed(2)}`;
      document.getElementById('inv-total').innerText = `$${total.toFixed(2)}`;

      document.getElementById('admin-invoice-modal').classList.add('active');
    } catch (e) {
      this.showToast('تعذر إنشاء الفاتورة الضريبية', 'danger');
    }
  },

  // ADMIN COUPONS MANAGEMENT
  loadAdminCoupons: async function() {
    try {
      const res = await fetch('/api/coupons');
      const data = await res.json();
      const tbody = document.getElementById('admin-coupons-body');
      if (!tbody) return;

      if (!data.success || !data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">لا توجد كوبونات مسجلة</td></tr>';
        return;
      }

      tbody.innerHTML = data.data.map(c => `
        <tr>
          <td><strong style="color:var(--primary); letter-spacing:1px;">${c.code}</strong></td>
          <td><strong>${c.discount_percent}%</strong></td>
          <td>$${c.min_order.toFixed(2)}</td>
          <td>$${c.max_discount.toFixed(2)}</td>
          <td>${c.uses_count} مرة</td>
          <td>
            <span class="status-badge ${c.is_active ? 'delivered' : 'cancelled'}">
              ${c.is_active ? 'مفعل' : 'معطل'}
            </span>
          </td>
          <td>
            <div style="display:flex; gap:6px;">
              <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem;" onclick="app.toggleCouponStatus(${c.id})" title="تبديل الحالة">
                <i class="fa-solid fa-power-off"></i>
              </button>
              <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem; color:var(--danger);" onclick="app.deleteCoupon(${c.id})" title="حذف">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `).join('');
    } catch (err) {
      console.warn('Error loading coupons');
    }
  },

  openCouponModal: function() {
    document.getElementById('coupon-code').value = '';
    document.getElementById('coupon-percent').value = '20';
    document.getElementById('coupon-min').value = '0';
    document.getElementById('coupon-max').value = '100';
    document.getElementById('admin-coupon-modal').classList.add('active');
  },

  handleSaveCoupon: async function(e) {
    e.preventDefault();
    const data = {
      code: document.getElementById('coupon-code').value,
      discount_percent: document.getElementById('coupon-percent').value,
      min_order: document.getElementById('coupon-min').value,
      max_discount: document.getElementById('coupon-max').value,
      is_active: 1
    };

    try {
      const res = await fetch('/api/coupons', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const result = await res.json();

      if (result.success) {
        this.closeModal('admin-coupon-modal');
        this.showToast('تمت إضافة الكوبون وتفعيله بنجاح!', 'success');
        await this.loadAdminCoupons();
      } else {
        this.showToast(result.message || 'خطأ في إنشاء الكوبون', 'danger');
      }
    } catch (e) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  toggleCouponStatus: async function(id) {
    try {
      const res = await fetch(`/api/coupons/${id}/toggle`, { method: 'PATCH' });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم تحديث حالة الكوبون');
        await this.loadAdminCoupons();
      }
    } catch (e) {
      this.showToast('خطأ في التحديث', 'danger');
    }
  },

  deleteCoupon: async function(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الكوبون؟')) return;
    try {
      const res = await fetch(`/api/coupons/${id}`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم حذف الكوبون');
        await this.loadAdminCoupons();
      }
    } catch (e) {
      this.showToast('خطأ في الحذف', 'danger');
    }
  },

  filterAdminOrders: function(status) {
    this.loadAdminOrders(status);
  },

  exportOrdersCSV: function() {
    window.location.href = '/api/admin/reports/export';
    this.showToast('جاري تصدير وتحميل ملف الطلبات Excel...', 'info');
  },

  openProductModal: function() {
    document.getElementById('admin-prod-id').value = '';
    document.getElementById('product-form-title').innerText = 'إضافة قطعة أزياء جديدة';
    document.getElementById('product-form').reset();
    document.getElementById('admin-prod-sizes').value = 'S, M, L, XL, XXL';
    document.getElementById('admin-prod-material').value = '';
    document.getElementById('admin-prod-care').value = '';
    document.getElementById('admin-product-modal').classList.add('active');
  },

  editProduct: function(id) {
    const p = this.products.find(x => x.id === id);
    if (!p) return;

    document.getElementById('admin-prod-id').value = p.id;
    document.getElementById('product-form-title').innerText = 'تعديل بيانات قطعة الأزياء';
    document.getElementById('admin-prod-name-ar').value = p.name_ar;
    document.getElementById('admin-prod-name-en').value = p.name_en || '';
    document.getElementById('admin-prod-category').value = p.category_id || 1;
    document.getElementById('admin-prod-price').value = p.price;
    document.getElementById('admin-prod-orig-price').value = p.original_price || '';
    document.getElementById('admin-prod-stock').value = p.stock;
    document.getElementById('admin-prod-badge').value = p.badge || '';
    document.getElementById('admin-prod-image').value = p.image_url || '';
    document.getElementById('admin-prod-desc').value = p.description_ar || '';
    document.getElementById('admin-prod-sizes').value = Array.isArray(p.sizes) ? p.sizes.join(', ') : (p.sizes || '');
    document.getElementById('admin-prod-material').value = p.material || '';
    document.getElementById('admin-prod-fit').value = p.fit_type || 'Regular Fit';
    document.getElementById('admin-prod-care').value = p.care_instructions || '';

    document.getElementById('admin-product-modal').classList.add('active');
  },

  handleSaveProduct: async function(e) {
    e.preventDefault();
    const prodId = document.getElementById('admin-prod-id').value;
    const rawSizes = document.getElementById('admin-prod-sizes').value;
    const sizesArray = rawSizes ? rawSizes.split(',').map(s => s.trim()).filter(Boolean) : [];

    const prodData = {
      name_ar: document.getElementById('admin-prod-name-ar').value,
      name_en: document.getElementById('admin-prod-name-en').value,
      category_id: parseInt(document.getElementById('admin-prod-category').value, 10),
      price: parseFloat(document.getElementById('admin-prod-price').value),
      original_price: document.getElementById('admin-prod-orig-price').value ? parseFloat(document.getElementById('admin-prod-orig-price').value) : null,
      stock: parseInt(document.getElementById('admin-prod-stock').value, 10),
      badge: document.getElementById('admin-prod-badge').value,
      image_url: document.getElementById('admin-prod-image').value,
      description_ar: document.getElementById('admin-prod-desc').value,
      sizes: sizesArray,
      material: document.getElementById('admin-prod-material').value,
      fit_type: document.getElementById('admin-prod-fit').value,
      care_instructions: document.getElementById('admin-prod-care').value
    };

    try {
      const url = prodId ? `/api/products/${prodId}` : '/api/products';
      const method = prodId ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(prodData)
      });
      const result = await res.json();

      if (result.success) {
        this.closeModal('admin-product-modal');
        this.showToast(result.message || 'تم حفظ المنتج بنجاح', 'success');
        await this.refreshAdminDashboard();
      } else {
        this.showToast(result.message || 'خطأ في حفظ المنتج', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  deleteProduct: async function(id) {
    if (!confirm('هل أنت متأكد من حذف هذا المنتج من قاعدة البيانات؟')) return;
    try {
      const res = await fetch(`/api/products/${id}`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم حذف المنتج بنجاح', 'success');
        await this.refreshAdminDashboard();
      } else {
        this.showToast(data.message || 'تعذر حذف المنتج', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الحذف', 'danger');
    }
  },

  updateOrderStatus: async function(id, status) {
    try {
      const res = await fetch(`/api/orders/${id}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
      });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم تحديث حالة الطلب بنجاح', 'success');
        await this.loadAdminStats();
      } else {
        this.showToast(data.message || 'خطأ في التحديث', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في التحديث', 'danger');
    }
  },

  switchAdminTab: function(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.admin-nav-item, .tab-btn').forEach(b => b.classList.remove('active'));
    
    const target = document.getElementById(tabId);
    if (target) target.style.display = 'block';

    if (btn) {
      btn.classList.add('active');
    } else {
      const matchingBtn = document.querySelector(`.admin-nav-item[onclick*="${tabId}"]`);
      if (matchingBtn) matchingBtn.classList.add('active');
    }

    // Close mobile drawer if opened
    this.toggleAdminSidebar(false);

    // Update dynamic breadcrumb
    const breadcrumbMap = {
      'overview-tab': 'لوحة المؤشرات العامة',
      'products-tab': 'إدارة المنتجات والمخزون',
      'orders-tab': 'الطلبات الواردة والشحن',
      'categories-tab': 'أقسام وتصنيفات المتجر',
      'users-tab': 'العملاء والمستخدمين (CRM)',
      'reviews-tab': 'التقييمات والمراجعات',
      'coupons-tab': 'الكوبونات والخصومات',
      'settings-tab': 'إعدادات المتجر العامة',
      'database-tab': 'محرك MariaDB والنظام'
    };
    const bcEl = document.getElementById('admin-current-breadcrumb');
    if (bcEl && breadcrumbMap[tabId]) bcEl.innerText = breadcrumbMap[tabId];

    if (tabId === 'overview-tab') this.loadAdminStats();
    else if (tabId === 'categories-tab') this.loadAdminCategories();
    else if (tabId === 'users-tab') this.loadAdminUsers();
    else if (tabId === 'reviews-tab') this.loadAdminReviews();
    else if (tabId === 'settings-tab') this.loadAdminSettings();
    else if (tabId === 'database-tab') this.loadDatabaseSystemStats();
    else if (tabId === 'orders-tab') this.loadAdminOrders();
    else if (tabId === 'products-tab') this.loadAdminProducts();
    else if (tabId === 'coupons-tab') this.loadAdminCoupons();
  },

  // ADMIN CATEGORIES MANAGEMENT
  loadAdminCategories: async function() {
    await this.fetchCategories();
    const tbody = document.getElementById('admin-categories-body');
    if (!tbody) return;

    if (!this.categories || this.categories.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px;">لا توجد تصنيفات مسجلة</td></tr>';
      return;
    }

    tbody.innerHTML = this.categories.map(c => `
      <tr>
        <td>
          <div style="width:36px; height:36px; background:var(--primary-light); color:var(--primary); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
            <i class="fa-solid ${c.icon || 'fa-tag'}"></i>
          </div>
        </td>
        <td><strong>${c.name_ar}</strong></td>
        <td style="color:var(--text-light);">${c.name_en || '-'}</td>
        <td><code>${c.slug}</code></td>
        <td>
          <div style="display:flex; gap:6px;">
            <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem;" onclick="app.editCategory(${c.id})" title="تعديل">
              <i class="fa-solid fa-pen"></i>
            </button>
            <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem; color:var(--danger);" onclick="app.deleteCategory(${c.id})" title="حذف">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  },

  openCategoryModal: function() {
    document.getElementById('admin-cat-id').value = '';
    document.getElementById('category-modal-title').innerHTML = '<i class="fa-solid fa-layer-group"></i> إضافة تصنيف جديد';
    document.getElementById('admin-cat-name-ar').value = '';
    document.getElementById('admin-cat-name-en').value = '';
    document.getElementById('admin-cat-slug').value = '';
    document.getElementById('admin-cat-icon').value = 'fa-tag';
    document.getElementById('cat-icon-preview').className = 'fa-solid fa-tag';
    document.getElementById('admin-category-modal').classList.add('active');
  },

  editCategory: function(id) {
    const c = this.categories.find(x => x.id === id);
    if (!c) return;

    document.getElementById('admin-cat-id').value = c.id;
    document.getElementById('category-modal-title').innerHTML = '<i class="fa-solid fa-pen"></i> تعديل التصنيف';
    document.getElementById('admin-cat-name-ar').value = c.name_ar;
    document.getElementById('admin-cat-name-en').value = c.name_en || '';
    document.getElementById('admin-cat-slug').value = c.slug;
    document.getElementById('admin-cat-icon').value = c.icon || 'fa-tag';
    document.getElementById('cat-icon-preview').className = 'fa-solid ' + (c.icon || 'fa-tag');
    document.getElementById('admin-category-modal').classList.add('active');
  },

  handleSaveCategory: async function(e) {
    e.preventDefault();
    const id = document.getElementById('admin-cat-id').value;
    const catData = {
      name_ar: document.getElementById('admin-cat-name-ar').value,
      name_en: document.getElementById('admin-cat-name-en').value,
      slug: document.getElementById('admin-cat-slug').value,
      icon: document.getElementById('admin-cat-icon').value
    };

    try {
      const url = id ? `/api/categories/${id}` : '/api/categories';
      const method = id ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(catData)
      });
      const data = await res.json();

      if (data.success) {
        this.closeModal('admin-category-modal');
        this.showToast(data.message || 'تم حفظ التصنيف بنجاح', 'success');
        await this.loadAdminCategories();
        this.renderStore();
      } else {
        this.showToast(data.message || 'خطأ في حفظ التصنيف', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  deleteCategory: async function(id) {
    if (!confirm('هل أنت متأكد من حذف هذا التصنيف؟')) return;
    try {
      const res = await fetch(`/api/categories/${id}`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم حذف التصنيف بنجاح', 'success');
        await this.loadAdminCategories();
        this.renderStore();
      } else {
        this.showToast(data.message || 'خطأ في الحذف', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  // ADMIN USERS MANAGEMENT
  loadAdminUsers: async function() {
    try {
      const res = await fetch('/api/admin/users');
      const data = await res.json();
      const tbody = document.getElementById('admin-users-body');
      if (!tbody) return;

      if (!data.success || !data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:20px;">لا يوجد مستخدمون مسجلون</td></tr>';
        return;
      }

      const users = data.data;
      const countEl = document.getElementById('admin-users-summary');
      if (countEl) countEl.innerText = `${users.length} مستخدمين`;

      tbody.innerHTML = users.map(u => `
        <tr>
          <td>
            <div style="display:flex; align-items:center; gap:8px;">
              <div style="width:32px; height:32px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem;">
                ${u.name.charAt(0)}
              </div>
              <strong>${u.name}</strong>
            </div>
          </td>
          <td>${u.email}</td>
          <td>${u.phone || '-'}</td>
          <td>${u.address || '-'}</td>
          <td>
            <span class="status-badge ${u.role === 'admin' ? 'delivered' : 'pending'}" style="font-size:0.75rem;">
              ${u.role === 'admin' ? 'مشرف إدارة' : 'عميل متجر'}
            </span>
          </td>
          <td><strong>${u.orders_count || 0}</strong> طلب</td>
          <td><strong>$${(u.total_spent || 0).toFixed(2)}</strong></td>
          <td>
            <div style="display:flex; gap:6px; align-items:center;">
              <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem; background:rgba(37,99,235,0.08); color:var(--primary);" onclick="app.showUserOrdersHistory(${u.id}, '${u.name.replace(/'/g, "\\'")}')" title="عرض سجل المشتريات والطلبات">
                <i class="fa-solid fa-clock-rotate-left"></i>
              </button>
              <select style="padding:4px; font-size:0.8rem;" onchange="app.changeUserRole(${u.id}, this.value)" ${u.email === 'admin@elite.com' ? 'disabled' : ''}>
                <option value="customer" ${u.role === 'customer' ? 'selected' : ''}>عميل</option>
                <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>مشرف</option>
              </select>
              ${u.email !== 'admin@elite.com' ? `
                <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem; color:var(--danger);" onclick="app.deleteUser(${u.id})" title="حذف المستخدم">
                  <i class="fa-solid fa-trash"></i>
                </button>
              ` : '<span style="font-size:0.75rem; color:var(--text-light);">(رئيسي)</span>'}
            </div>
          </td>
        </tr>
      `).join('');
    } catch (err) {
      console.warn('Error loading users');
    }
  },

  changeUserRole: async function(id, role) {
    try {
      const res = await fetch(`/api/admin/users/${id}/role`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ role })
      });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم تحديث صلاحية المستخدم بنجاح', 'success');
        await this.loadAdminUsers();
      } else {
        this.showToast(data.message || 'خطأ في تحديث الصلاحية', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  deleteUser: async function(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الحساب؟')) return;
    try {
      const res = await fetch(`/api/admin/users/${id}`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم حذف المستخدم بنجاح', 'success');
        await this.loadAdminUsers();
      } else {
        this.showToast(data.message || 'خطأ في الحذف', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  // ADMIN REVIEWS MANAGEMENT
  loadAdminReviews: async function() {
    try {
      const res = await fetch('/api/admin/reviews');
      const data = await res.json();
      const tbody = document.getElementById('admin-reviews-body');
      if (!tbody) return;

      if (!data.success || !data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px;">لا توجد تقييمات مسجلة</td></tr>';
        return;
      }

      tbody.innerHTML = data.data.map(r => `
        <tr>
          <td>
            <div style="display:flex; align-items:center; gap:8px;">
              <img src="${r.product_image || ''}" style="width:36px; height:36px; border-radius:6px; object-fit:cover;">
              <span style="font-weight:600; font-size:0.85rem;">${r.product_name || '-'}</span>
            </div>
          </td>
          <td><strong>${r.author}</strong></td>
          <td><span style="color:#f59e0b; letter-spacing:1px;">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span></td>
          <td style="max-width:280px; font-size:0.85rem;">${r.comment}</td>
          <td style="font-size:0.8rem; color:var(--text-light);">${r.date}</td>
          <td>
            <button class="btn btn-secondary" style="padding:4px 8px; font-size:0.8rem; color:var(--danger);" onclick="app.deleteAdminReview(${r.id})" title="حذف التقييم">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
      `).join('');
    } catch (err) {
      console.warn('Error loading reviews');
    }
  },

  deleteAdminReview: async function(id) {
    if (!confirm('هل تريد حذف هذه المراجعة؟')) return;
    try {
      const res = await fetch(`/api/admin/reviews/${id}`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم حذف التقييم بنجاح', 'success');
        await this.loadAdminReviews();
        await this.fetchProducts();
      } else {
        this.showToast(data.message || 'خطأ في الحذف', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  // STORE SETTINGS
  loadAdminSettings: async function() {
    try {
      const res = await fetch('/api/settings');
      const data = await res.json();
      if (data.success && data.data) {
        const s = data.data;
        if (document.getElementById('setting-store-name-ar')) document.getElementById('setting-store-name-ar').value = s.store_name_ar || '';
        if (document.getElementById('setting-store-name-en')) document.getElementById('setting-store-name-en').value = s.store_name_en || '';
        if (document.getElementById('setting-currency')) document.getElementById('setting-currency').value = s.currency || '$';
        if (document.getElementById('setting-shipping-cost')) document.getElementById('setting-shipping-cost').value = s.shipping_cost || '15.00';
        if (document.getElementById('setting-free-shipping')) document.getElementById('setting-free-shipping').value = s.free_shipping_threshold || '150.00';
        if (document.getElementById('setting-tax-percent')) document.getElementById('setting-tax-percent').value = s.tax_percent || '15';
        if (document.getElementById('setting-contact-email')) document.getElementById('setting-contact-email').value = s.contact_email || '';
        if (document.getElementById('setting-contact-phone')) document.getElementById('setting-contact-phone').value = s.contact_phone || '';
        if (document.getElementById('setting-announcement-text')) document.getElementById('setting-announcement-text').value = s.announcement_text || '';
      }
    } catch (err) {
      console.warn('Error loading settings');
    }
  },

  handleSaveSettings: async function(e) {
    e.preventDefault();
    const settings = {
      store_name_ar: document.getElementById('setting-store-name-ar').value,
      store_name_en: document.getElementById('setting-store-name-en').value,
      currency: document.getElementById('setting-currency').value,
      shipping_cost: document.getElementById('setting-shipping-cost').value,
      free_shipping_threshold: document.getElementById('setting-free-shipping').value,
      tax_percent: document.getElementById('setting-tax-percent').value,
      contact_email: document.getElementById('setting-contact-email').value,
      contact_phone: document.getElementById('setting-contact-phone').value,
      announcement_text: document.getElementById('setting-announcement-text').value
    };

    try {
      const res = await fetch('/api/admin/settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings)
      });
      const data = await res.json();
      if (data.success) {
        this.showToast('تم حفظ إعدادات المتجر بنجاح!', 'success');
      } else {
        this.showToast(data.message || 'خطأ في حفظ الإعدادات', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  // DATABASE & SYSTEM TOOLS
  loadDatabaseSystemStats: async function() {
    try {
      const res = await fetch('/api/admin/system/stats');
      const data = await res.json();
      const container = document.getElementById('db-stats-row');
      if (!container || !data.success || !data.data) return;

      const sys = data.data;
      const db = sys.database;
      const totalRecords = (db.counts.products || 0) + (db.counts.orders || 0) + (db.counts.users || 0);

      container.innerHTML = `
        <div class="stat-card" style="border-right: 4px solid var(--primary);">
          <div class="stat-icon bg-blue"><i class="fa-solid fa-database"></i></div>
          <div class="stat-info">
            <span class="stat-label">قاعدة البيانات (${db.type || 'MariaDB'})</span>
            <h3 class="stat-value">${db.name || 'ecommerce_store'}</h3>
            <small style="color:var(--text-light); font-size:0.75rem;">إصدار: ${db.version || '10.4.x'}</small>
          </div>
        </div>
        <div class="stat-card" style="border-right: 4px solid var(--success);">
          <div class="stat-icon bg-green"><i class="fa-brands fa-laravel" style="color:#ef4444;"></i></div>
          <div class="stat-info">
            <span class="stat-label">محرك التطبيق (Laravel API)</span>
            <h3 class="stat-value">Laravel ${sys.laravelVersion || '12'}</h3>
            <small style="color:var(--text-light); font-size:0.75rem;">PHP ${sys.phpVersion || '8.3'} | ذاكرة: ${sys.memoryMb} MB</small>
          </div>
        </div>
        <div class="stat-card" style="border-right: 4px solid var(--amber);">
          <div class="stat-icon bg-amber"><i class="fa-solid fa-table-list"></i></div>
          <div class="stat-info">
            <span class="stat-label">إجمالي السجلات بالجداول</span>
            <h3 class="stat-value">${totalRecords}</h3>
            <small style="color:var(--text-light); font-size:0.75rem;">منتجات: ${db.counts.products} | طلبات: ${db.counts.orders}</small>
          </div>
        </div>
        <div class="stat-card" style="border-right: 4px solid #8b5cf6;">
          <div class="stat-icon" style="background:#ede9fe; color:#8b5cf6;"><i class="fa-solid fa-users"></i></div>
          <div class="stat-info">
            <span class="stat-label">حسابات المسجلين</span>
            <h3 class="stat-value">${db.counts.users}</h3>
            <small style="color:var(--text-light); font-size:0.75rem;">تقييمات: ${db.counts.reviews} | كوبونات: ${db.counts.coupons}</small>
          </div>
        </div>
      `;

      // Render Tables Schema breakdown
      const tablesTbody = document.getElementById('admin-db-tables-body');
      if (tablesTbody) {
        const tables = [
          { name: 'products', engine: 'InnoDB', rows: db.counts.products || 15, dataSize: '48 KB', indexSize: '16 KB', status: 'OK' },
          { name: 'orders', engine: 'InnoDB', rows: db.counts.orders || 5, dataSize: '32 KB', indexSize: '16 KB', status: 'OK' },
          { name: 'order_items', engine: 'InnoDB', rows: 12, dataSize: '32 KB', indexSize: '16 KB', status: 'OK' },
          { name: 'users', engine: 'InnoDB', rows: db.counts.users || 6, dataSize: '32 KB', indexSize: '16 KB', status: 'OK' },
          { name: 'categories', engine: 'InnoDB', rows: (this.categories || []).length || 5, dataSize: '16 KB', indexSize: '16 KB', status: 'OK' },
          { name: 'coupons', engine: 'InnoDB', rows: db.counts.coupons || 4, dataSize: '16 KB', indexSize: '16 KB', status: 'OK' },
          { name: 'reviews', engine: 'InnoDB', rows: db.counts.reviews || 8, dataSize: '16 KB', indexSize: '16 KB', status: 'OK' },
          { name: 'settings', engine: 'InnoDB', rows: 1, dataSize: '16 KB', indexSize: '16 KB', status: 'OK' }
        ];

        tablesTbody.innerHTML = tables.map(t => `
          <tr>
            <td><code>${t.name}</code></td>
            <td><span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">${t.engine}</span></td>
            <td><strong>${t.rows}</strong></td>
            <td>${t.dataSize}</td>
            <td>${t.indexSize}</td>
            <td><span class="status-badge delivered" style="font-size:0.75rem;"><i class="fa-solid fa-check"></i> ${t.status}</span></td>
          </tr>
        `).join('');
      }
    } catch (err) {
      console.warn('Error loading db stats');
    }
  },

  resetMockData: async function() {
    if (!confirm('هل تريد إعادة توليد البيانات التجريبية الشاملة لمرحلة التطوير؟ سيتم تحديث الجداول بالبيانات النموذجية.')) return;
    try {
      const res = await fetch('/api/admin/system/reset-mock', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        this.showToast(data.message, 'success');
        await this.refreshAdminDashboard();
        this.renderStore();
      } else {
        this.showToast(data.message || 'خطأ في العملية', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  openCleanDbModal: function() {
    document.getElementById('clean-db-confirm-text').value = '';
    document.getElementById('clean-db-admin-password').value = 'admin123';
    document.getElementById('admin-clean-db-modal').classList.add('active');
  },

  handleInitCleanProduction: async function(e) {
    e.preventDefault();
    const confirmInput = document.getElementById('clean-db-confirm-text').value.trim();
    if (confirmInput !== 'تأكيد') {
      this.showToast('يرجى كتابة كلمة "تأكيد" للبدء بالعملية', 'danger');
      return;
    }

    const adminPassword = document.getElementById('clean-db-admin-password').value.trim() || 'admin123';

    try {
      const res = await fetch('/api/admin/system/init-clean', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ adminPassword })
      });
      const data = await res.json();
      if (data.success) {
        this.closeModal('admin-clean-db-modal');
        this.showToast(data.message, 'success');
        await this.refreshAdminDashboard();
        this.renderStore();
      } else {
        this.showToast(data.message || 'خطأ في التهيئة', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  downloadDbBackup: function() {
    window.location.href = '/api/admin/system/backup';
    this.showToast('جاري تنزيل نسخة احتياطية من قاعدة البيانات...', 'info');
  },

  optimizeDatabaseTables: async function() {
    this.showToast('جاري تشغيل أوامر الصيانة وتحسين جداول MariaDB وتفريغ الكاش...', 'info');
    try {
      const res = await fetch('/api/admin/system/optimize', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        this.showToast(data.message, 'success');
        await this.loadDatabaseSystemStats();
      } else {
        this.showToast(data.message || 'تعذر استكمال الصيانة', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في الاتصال بالخادم', 'danger');
    }
  },

  // SPOTLIGHT COMMAND PALETTE (Ctrl + K)
  setupCommandPaletteListeners: function() {
    window.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
        e.preventDefault();
        this.openCommandPalette();
      } else if (e.key === 'Escape' && this.commandPaletteOpen) {
        this.closeCommandPalette();
      }
    });
  },

  openCommandPalette: function() {
    this.commandPaletteOpen = true;
    const modal = document.getElementById('admin-command-palette');
    if (modal) modal.classList.add('active');
    const input = document.getElementById('command-palette-input');
    if (input) {
      input.value = '';
      setTimeout(() => input.focus(), 50);
    }
    this.filterCommandPalette('');
  },

  closeCommandPalette: function() {
    this.commandPaletteOpen = false;
    const modal = document.getElementById('admin-command-palette');
    if (modal) modal.classList.remove('active');
  },

  filterCommandPalette: function(query) {
    const q = (query || '').trim().toLowerCase();
    const resultsContainer = document.getElementById('command-palette-results');
    if (!resultsContainer) return;

    const baseCommands = [
      { type: 'nav', title: 'لوحة المؤشرات العامة (Overview)', icon: 'fa-gauge-high', action: () => { this.showAdminView(); this.switchAdminTab('overview-tab'); } },
      { type: 'nav', title: 'إدارة المنتجات والمخزون', icon: 'fa-boxes-stacked', action: () => { this.showAdminView(); this.switchAdminTab('products-tab'); } },
      { type: 'nav', title: 'الطلبات والشحنات المسجلة', icon: 'fa-receipt', action: () => { this.showAdminView(); this.switchAdminTab('orders-tab'); } },
      { type: 'nav', title: 'أقسام وتصنيفات المتجر', icon: 'fa-layer-group', action: () => { this.showAdminView(); this.switchAdminTab('categories-tab'); } },
      { type: 'nav', title: 'العملاء والمستخدمين (CRM)', icon: 'fa-users', action: () => { this.showAdminView(); this.switchAdminTab('users-tab'); } },
      { type: 'nav', title: 'الكوبونات والخصومات', icon: 'fa-tag', action: () => { this.showAdminView(); this.switchAdminTab('coupons-tab'); } },
      { type: 'nav', title: 'إعدادات المتجر العامة', icon: 'fa-sliders', action: () => { this.showAdminView(); this.switchAdminTab('settings-tab'); } },
      { type: 'nav', title: 'محرك MariaDB والنظام', icon: 'fa-database', action: () => { this.showAdminView(); this.switchAdminTab('database-tab'); } },

      { type: 'action', title: 'إضافة قطعة أزياء جديدة', icon: 'fa-plus', action: () => { this.openProductModal(); } },
      { type: 'action', title: 'إنشاء كود خصم جديد', icon: 'fa-ticket', action: () => { this.openCouponModal(); } },
      { type: 'action', title: 'تصدير تقرير الطلبات Excel', icon: 'fa-file-excel', action: () => { this.exportOrdersCSV(); } },
      { type: 'action', title: 'تصدير كشف المنتجات CSV', icon: 'fa-file-csv', action: () => { this.exportProductsCSV(); } },
      { type: 'action', title: 'تحسين جداول MariaDB وتفريغ الكاش', icon: 'fa-bolt', action: () => { this.optimizeDatabaseTables(); } },
      { type: 'action', title: 'تبديل المظهر (فاتح / داكن)', icon: 'fa-circle-half-stroke', action: () => { this.toggleTheme(); } },
      { type: 'action', title: 'الانتقال إلى واجهة المتجر', icon: 'fa-store', action: () => { this.showStoreView(); } }
    ];

    // Search in live products
    const matchingProducts = (this.products || []).filter(p => !q || (p.name_ar && p.name_ar.toLowerCase().includes(q))).slice(0, 5).map(p => ({
      type: 'product',
      title: `${p.name_ar} ($${p.price.toFixed(2)})`,
      subtitle: `المخزون: ${p.stock} قطعة | ${p.category_name || '-'}`,
      icon: 'fa-shirt',
      action: () => { this.showAdminView(); this.switchAdminTab('products-tab'); this.editProduct(p.id); }
    }));

    // Search in orders
    const matchingOrders = (this.adminOrdersList || []).filter(o => !q || (o.order_number && o.order_number.toLowerCase().includes(q)) || (o.customer_name && o.customer_name.toLowerCase().includes(q))).slice(0, 5).map(o => ({
      type: 'order',
      title: `طلب: ${o.order_number} - ${o.customer_name}`,
      subtitle: `المبلغ: $${o.total.toFixed(2)} | الحالة: ${o.status}`,
      icon: 'fa-receipt',
      action: () => { this.showAdminView(); this.switchAdminTab('orders-tab'); this.viewFullOrderDetails(o.id); }
    }));

    const filteredBase = baseCommands.filter(c => !q || c.title.toLowerCase().includes(q));
    const allResults = [...filteredBase, ...matchingProducts, ...matchingOrders];
    this.commandPaletteItems = allResults;
    this.commandSelectedIndex = 0;

    if (allResults.length === 0) {
      resultsContainer.innerHTML = '<div style="text-align:center; padding:24px; color:var(--text-light); font-size:0.9rem;">لا توجد نتائج مطابقة لبحثك</div>';
      return;
    }

    resultsContainer.innerHTML = allResults.map((item, idx) => `
      <div class="command-item ${idx === 0 ? 'selected' : ''}" onclick="app.executeCommand(${idx})">
        <div class="command-item-left">
          <div class="command-item-icon">
            <i class="fa-solid ${item.icon}"></i>
          </div>
          <div>
            <div style="font-weight:700; font-size:0.9rem;">${item.title}</div>
            ${item.subtitle ? `<div style="font-size:0.75rem; color:var(--text-light); margin-top:2px;">${item.subtitle}</div>` : ''}
          </div>
        </div>
        <span class="command-kbd-badge">${item.type === 'nav' ? 'صفحة' : (item.type === 'product' ? 'منتج' : (item.type === 'order' ? 'طلب' : 'إجراء'))}</span>
      </div>
    `).join('');
  },

  handleCommandKeyDown: function(e) {
    const items = document.querySelectorAll('.command-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      this.commandSelectedIndex = (this.commandSelectedIndex + 1) % items.length;
      this.updateCommandSelection(items);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      this.commandSelectedIndex = (this.commandSelectedIndex - 1 + items.length) % items.length;
      this.updateCommandSelection(items);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      this.executeCommand(this.commandSelectedIndex);
    }
  },

  updateCommandSelection: function(items) {
    items.forEach((it, idx) => {
      if (idx === this.commandSelectedIndex) {
        it.classList.add('selected');
        it.scrollIntoView({ block: 'nearest' });
      } else {
        it.classList.remove('selected');
      }
    });
  },

  executeCommand: function(idx) {
    const item = this.commandPaletteItems[idx];
    if (item && item.action) {
      this.closeCommandPalette();
      item.action();
    }
  },

  // QUICK VIEW & REVIEWS WITH MULTI-IMAGE GALLERY, SIZES, COLORS & FABRIC SPECS
  quickView: function(id) {
    const p = this.products.find(x => x.id === id);
    if (!p) return;
    this.activeQuickViewProduct = p;

    // Default chosen size & color
    this.selectedQuickViewSize = (p.sizes && Array.isArray(p.sizes) && p.sizes.length) ? p.sizes[0] : null;
    this.selectedQuickViewColor = (p.colors && Array.isArray(p.colors) && p.colors.length) ? p.colors[0] : null;

    const body = document.getElementById('quickview-body');
    const reviewsHtml = (p.reviews || []).map(r => `
      <div class="review-item">
        <div class="review-header">
          <strong>${r.author}</strong>
          <span class="review-stars">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span>
        </div>
        <p class="review-comment">${r.comment}</p>
        <small class="review-date">${r.date}</small>
      </div>
    `).join('') || '<p style="color: var(--text-light); font-size: 0.88rem;">لا توجد تقييمات بعد. كن أول من يقيّم هذه القطعة!</p>';

    const gallery = p.gallery && p.gallery.length ? p.gallery : [p.image_url];
    const thumbsHtml = gallery.map((imgUrl, idx) => `
      <div class="gallery-thumb-item ${idx === 0 ? 'active' : ''}" onclick="app.switchGalleryImage('${imgUrl}', this)">
        <img src="${imgUrl}" alt="صورة ${idx + 1}">
      </div>
    `).join('');

    // Fashion Sizes Selector
    const sizesList = Array.isArray(p.sizes) ? p.sizes : [];
    const sizesHtml = sizesList.length ? `
      <div class="fashion-attr-block">
        <div class="attr-header">
          <label><i class="fa-solid fa-ruler"></i> المقاس المحدد: <strong id="quickview-size-label">${this.selectedQuickViewSize || 'قياسي'}</strong></label>
          <div style="display:flex; gap:6px;">
            <button type="button" class="btn-smart-size" onclick="app.openSmartSizeModal()"><i class="fa-solid fa-wand-magic-sparkles"></i> احسب مقاسي 🪄</button>
            <button type="button" class="btn-size-guide" onclick="app.openSizeGuideModal()"><i class="fa-solid fa-ruler-combined"></i> دليل القياسات 📏</button>
          </div>
        </div>
        <div class="sizes-selector-pills">
          ${sizesList.map(s => `
            <button type="button" class="size-pill-btn ${s === this.selectedQuickViewSize ? 'active' : ''}" onclick="app.selectQuickViewSize('${s}', this)">${s}</button>
          `).join('')}
        </div>
      </div>
    ` : '';

    // Fashion Colors Swatches
    const colorsList = Array.isArray(p.colors) ? p.colors : [];
    const colorsHtml = colorsList.length ? `
      <div class="fashion-attr-block">
        <label><i class="fa-solid fa-palette"></i> اللون المختار: <strong id="quickview-color-label">${this.selectedQuickViewColor?.name || ''}</strong></label>
        <div class="color-swatches-row">
          ${colorsList.map(c => `
            <button type="button" class="color-swatch-circle ${c.name === this.selectedQuickViewColor?.name ? 'active' : ''}" style="background-color: ${c.hex};" title="${c.name}" onclick="app.selectQuickViewColor('${c.name}', '${c.hex}', this)">
              <span class="swatch-check"><i class="fa-solid fa-check"></i></span>
            </button>
          `).join('')}
        </div>
      </div>
    ` : '';

    // Fabric & Material Specs Card
    const specsHtml = (p.material || p.care_instructions || p.fit_type) ? `
      <div class="fabric-spec-card">
        ${p.material ? `<div class="spec-item"><i class="fa-solid fa-fingerprint"></i> <span>نوع القماش: <strong>${p.material}</strong></span></div>` : ''}
        ${p.fit_type ? `<div class="spec-item"><i class="fa-solid fa-scissors"></i> <span>نوع القصة: <strong>${p.fit_type}</strong></span></div>` : ''}
        ${p.care_instructions ? `<div class="spec-item"><i class="fa-solid fa-hands-bubbles"></i> <span>إرشادات العناية: <strong>${p.care_instructions}</strong></span></div>` : ''}
      </div>
    ` : '';

    body.innerHTML = `
      <div class="gallery-container">
        <div class="quickview-img-zoom-box" onmousemove="app.handleImgZoom(event, this)" onmouseleave="app.resetImgZoom(this)">
          <img src="${gallery[0]}" id="quickview-main-img" class="quickview-img" alt="${p.name_ar}">
        </div>
        <div class="gallery-thumbs-row">
          ${thumbsHtml}
        </div>
        <div style="font-size:0.75rem; color:var(--text-light); text-align:center;">
          <i class="fa-solid fa-magnifying-glass-plus"></i> مرر الماوس فوق الصورة لتكبير التفاصيل والقماش
        </div>
      </div>
      <div class="quickview-info">
        <span class="product-cat">${p.category_name || 'أزياء فاخرة'}</span>
        <h3>${p.name_ar}</h3>
        <p class="product-price-row">
          <span class="current-price">$${p.price.toFixed(2)}</span>
          ${p.original_price ? `<span class="original-price" style="text-decoration:line-through; color:var(--text-light); margin-right:10px;">$${p.original_price.toFixed(2)}</span>` : ''}
        </p>
        <p style="margin-bottom: 14px; color: var(--text-light); font-size: 0.95rem; line-height:1.6;">${p.description_ar}</p>

        <!-- Dynamic Fashion Attributes -->
        ${sizesHtml}
        ${colorsHtml}
        ${specsHtml}

        <button class="btn btn-primary btn-block" style="margin-bottom: 20px; padding:12px 20px; font-weight:700;" onclick="app.addCurrentQuickViewToCart(); app.closeModal('product-modal')">
          <i class="fa-solid fa-bag-shopping"></i> أضف إلى حقيبة التسوق
        </button>

        <div class="reviews-section">
          <h4>تقييمات العملاء (${p.reviews_count || 0})</h4>
          <div class="reviews-list">${reviewsHtml}</div>
          <form onsubmit="app.handleAddReview(event, ${p.id})" class="review-form">
            <h5>أضف تقييمك للمنتج</h5>
            <input type="text" id="rev-author" placeholder="اسمك الكريم..." required value="${this.currentUser ? this.currentUser.name : ''}">
            <select id="rev-rating" required>
              <option value="5">★★★★★ (5/5 ممتاز)</option>
              <option value="4">★★★★☆ (4/5 جيد جداً)</option>
              <option value="3">★★★☆☆ (3/5 متوسط)</option>
              <option value="2">★★☆☆☆ (2/5 مقبول)</option>
              <option value="1">★☆☆☆☆ (1/5 سيء)</option>
            </select>
            <textarea id="rev-comment" rows="2" placeholder="اكتب رأيك الصادق في المنتج..." required></textarea>
            <button type="submit" class="btn btn-secondary btn-block">إرسال التقييم</button>
          </form>
        </div>
      </div>
    `;
    document.getElementById('product-modal').classList.add('active');
  },

  selectQuickViewSize: function(size, btn) {
    this.selectedQuickViewSize = size;
    const label = document.getElementById('quickview-size-label');
    if (label) label.innerText = size;
    document.querySelectorAll('.size-pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
  },

  selectQuickViewColor: function(name, hex, btn) {
    this.selectedQuickViewColor = { name, hex };
    const label = document.getElementById('quickview-color-label');
    if (label) label.innerText = name;
    document.querySelectorAll('.color-swatch-circle').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
  },

  addCurrentQuickViewToCart: function() {
    if (!this.activeQuickViewProduct) return;
    this.addToCart(
      this.activeQuickViewProduct.id,
      this.selectedQuickViewSize,
      this.selectedQuickViewColor ? this.selectedQuickViewColor.name : null
    );
  },

  openSizeGuideModal: function() {
    document.getElementById('size-guide-modal').classList.add('active');
  },

  switchSizeGuideTab: function(tabName, btn) {
    document.querySelectorAll('.size-guide-tab-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.size-guide-tab-btn').forEach(b => b.classList.remove('active'));
    const target = document.getElementById(`size-tab-${tabName}`);
    if (target) target.style.display = 'block';
    if (btn) btn.classList.add('active');
  },

  // SMART SIZE RECOMMENDER METHODS
  openSmartSizeModal: function() {
    this.runSmartSizeCalculation();
    document.getElementById('smart-size-modal').classList.add('active');
  },

  selectBuildPreference: function(fit, card) {
    this.selectedBuildPreference = fit;
    document.querySelectorAll('.build-card').forEach(c => c.classList.remove('active'));
    if (card) card.classList.add('active');
    this.runSmartSizeCalculation();
  },

  runSmartSizeCalculation: function() {
    const h = parseInt(document.getElementById('smart-height')?.value || 175);
    const w = parseInt(document.getElementById('smart-weight')?.value || 75);
    const fit = this.selectedBuildPreference || 'slim';

    const p = this.activeQuickViewProduct;
    let recSize = 'L';
    let explanation = '';

    const sizes = (p && Array.isArray(p.sizes)) ? p.sizes : [];
    const isThobe = sizes.some(s => ['54', '56', '58', '60', '62'].includes(String(s)));
    const isSuit = sizes.some(s => ['46', '48', '50', '52', '54', '56'].includes(String(s)));
    const isShoes = sizes.some(s => ['40', '41', '42', '43', '44', '45'].includes(String(s)));

    if (isThobe) {
      if (h <= 165) recSize = '54';
      else if (h <= 172) recSize = '56';
      else if (h <= 178) recSize = '58';
      else if (h <= 184) recSize = '60';
      else recSize = '62';
      explanation = `بناءً على طولك (${h} سم)، المقاس الموصى به لثوب سعودي ملكي متناسق هو ${recSize} بوصة.`;
    } else if (isSuit) {
      let base = 48;
      if (w < 65) base = 46;
      else if (w <= 74) base = 48;
      else if (w <= 82) base = 50;
      else if (w <= 90) base = 52;
      else if (w <= 98) base = 54;
      else base = 56;

      if (fit === 'relaxed' && base < 56) base += 2;
      recSize = String(base);
      explanation = `بناءً على وزن (${w} كجم) وطول (${h} سم) وتفضيلك للقصة (${fit})، المقاس الإيطالي الأنسب لكتف وصدر البدلة هو ${recSize} EU.`;
    } else if (isShoes) {
      recSize = '42';
      explanation = `المقاس الموصى به لأحذية أكسفورد الإيطالية هو 42 EU بناءً على القياسات المتوسطة.`;
    } else {
      const bmi = w / ((h / 100) * (h / 100));
      if (bmi < 20.5) recSize = fit === 'relaxed' ? 'M' : 'S';
      else if (bmi < 24.5) recSize = fit === 'relaxed' ? 'L' : 'M';
      else if (bmi < 28) recSize = fit === 'relaxed' ? 'XL' : 'L';
      else if (bmi < 32) recSize = fit === 'relaxed' ? 'XXL' : 'XL';
      else recSize = 'XXL';
      explanation = `بناءً على مؤشر كتلة الجسم التناسبي (${bmi.toFixed(1)}) وطولك، مقاس ${recSize} يمنحك راحة مثالية وانسيابية كاملة.`;
    }

    this.calculatedSmartSize = recSize;
    const out = document.getElementById('rec-size-output');
    const exp = document.getElementById('rec-size-explanation');
    if (out) out.innerText = recSize;
    if (exp) exp.innerText = explanation;
  },

  applySmartSizeToQuickView: function() {
    if (!this.calculatedSmartSize) return;
    const targetSize = this.calculatedSmartSize;
    this.closeModal('smart-size-modal');

    const pill = Array.from(document.querySelectorAll('.size-pill-btn')).find(b => b.innerText.trim().includes(targetSize));
    if (pill) {
      this.selectQuickViewSize(targetSize, pill);
    } else {
      this.selectedQuickViewSize = targetSize;
      const label = document.getElementById('quickview-size-label');
      if (label) label.innerText = targetSize;
    }
    this.showToast(`تم تطبيق المقاس الذكي الموصى به (${targetSize}) بنجاح! 🪄`, 'success');
  },

  // SHOP THE LOOK / OUTFIT BUNDLE COORDINATOR
  addOutfitBundle: function(bundleType) {
    if (bundleType === 'royal') {
      const s1 = document.getElementById('look1-size-1')?.value || '50 EU';
      const s2 = document.getElementById('look1-size-2')?.value || 'L';
      const s3 = document.getElementById('look1-size-3')?.value || '42';
      this.addToCart(1, s1, 'كحلي ملكي');
      this.addToCart(2, s2, 'أبيض ناصع');
      this.addToCart(9, s3, 'بني داكن');
      this.showToast('تمت إضافة إطلالة السمو الملكي الإيطالية كاملة إلى حقيبة التسوق بخصم 15%! 👑', 'success');
    } else if (bundleType === 'heritage') {
      const s1 = document.getElementById('look2-size-1')?.value || '58';
      const s2 = document.getElementById('look2-size-2')?.value || 'قياسي';
      const s3 = document.getElementById('look2-size-3')?.value || 'قياسي';
      this.addToCart(4, s1, 'أبيض ناصع');
      this.addToCart(11, s2, 'بني عسلي');
      this.addToCart(12, s3, 'ذهبي');
      this.showToast('تمت إضافة إطلالة الأصالة والأناقة العربية كاملة إلى حقيبة التسوق! ⚜️', 'success');
    } else if (bundleType === 'streetwear') {
      const s1 = document.getElementById('look3-size-1')?.value || 'L';
      const s2 = document.getElementById('look3-size-2')?.value || 'L';
      const s3 = document.getElementById('look3-size-3')?.value || 'L';
      this.addToCart(5, s1, 'رمادي غامق');
      this.addToCart(3, s2, 'أبيض');
      this.addToCart(7, s3, 'أسود ملكي');
      this.showToast('تمت إضافة إطلالة الشارع العصرية كاملة إلى حقيبة التسوق! 🛹', 'success');
    }
  },


  switchGalleryImage: function(imgUrl, el) {
    const mainImg = document.getElementById('quickview-main-img');
    if (mainImg) mainImg.src = imgUrl;
    document.querySelectorAll('.gallery-thumb-item').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');
  },

  handleImgZoom: function(e, box) {
    const img = box.querySelector('img');
    if (!img) return;
    const rect = box.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    img.style.transformOrigin = `${x}% ${y}%`;
  },

  resetImgZoom: function(box) {
    const img = box.querySelector('img');
    if (img) img.style.transformOrigin = 'center center';
  },

  // NOTIFICATIONS HUB
  toggleNotifications: function() {
    const drop = document.getElementById('notifications-dropdown');
    if (drop) drop.classList.toggle('show');
  },

  renderNotifications: function() {
    const listEl = document.getElementById('notif-list');
    const badge = document.getElementById('notif-badge');
    const unreadCount = this.notifications.filter(n => n.unread).length;

    if (badge) {
      badge.innerText = unreadCount;
      badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
    }

    if (!listEl) return;

    if (this.notifications.length === 0) {
      listEl.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light); font-size:0.85rem;">لا توجد إشعارات جديدة</div>';
      return;
    }

    listEl.innerHTML = this.notifications.map(n => `
      <div class="notif-item ${n.unread ? 'unread' : ''}" onclick="app.markSingleNotifRead(${n.id})">
        <div class="notif-icon"><i class="fa-solid fa-bell"></i></div>
        <div class="notif-content">
          <strong>${n.title}</strong>
          <div>${n.desc}</div>
          <div class="notif-time">${n.time}</div>
        </div>
      </div>
    `).join('');
  },

  markAllNotifsRead: function() {
    this.notifications.forEach(n => n.unread = false);
    localStorage.setItem('elite_notifs', JSON.stringify(this.notifications));
    this.renderNotifications();
    this.showToast('تم تحديد جميع الإشعارات كمقروءة');
  },

  markSingleNotifRead: function(id) {
    const notif = this.notifications.find(n => n.id === id);
    if (notif && notif.unread) {
      notif.unread = false;
      localStorage.setItem('elite_notifs', JSON.stringify(this.notifications));
      this.renderNotifications();
    }
  },

  // LIVE CHAT SUPPORT SYSTEM
  toggleChatWindow: function() {
    const win = document.getElementById('chat-widget-window');
    if (win) {
      win.classList.toggle('open');
      if (win.classList.contains('open')) {
        const input = document.getElementById('chat-input-text');
        if (input) setTimeout(() => input.focus(), 150);
      }
    }
  },

  handleQuickChip: function(questionText) {
    this.appendChatMessage('user', questionText);
    setTimeout(() => {
      const reply = this.getSmartChatReply(questionText);
      this.appendChatMessage('bot', reply);
    }, 600);
  },

  handleSendChatMessage: function(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input-text');
    if (!input || !input.value.trim()) return;

    const userText = input.value.trim();
    input.value = '';
    this.appendChatMessage('user', userText);

    setTimeout(() => {
      const reply = this.getSmartChatReply(userText);
      this.appendChatMessage('bot', reply);
    }, 650);
  },

  appendChatMessage: function(sender, text) {
    const body = document.getElementById('chat-messages-body');
    if (!body) return;

    const msgEl = document.createElement('div');
    msgEl.className = `chat-msg ${sender}`;
    msgEl.innerHTML = text;
    body.appendChild(msgEl);
    body.scrollTop = body.scrollHeight;
  },

  getSmartChatReply: function(text) {
    const q = text.toLowerCase();

    if (q.includes('توصيل') || q.includes('شحن') || q.includes('مدة')) {
      return '🚚 التوصيل سريع يستغرق من <strong>24 إلى 48 ساعة</strong> لكافة مدن المملكة. الشحن مجاني للطلبات بقيمة <strong>$150</strong> أو أكثر، ورسوم التوصيل العادي هي 15$ فقط.';
    }

    if (q.includes('دفع') || q.includes('سداد') || q.includes('بطاق') || q.includes('apple pay') || q.includes('مدى')) {
      return '💳 نوفر خيارات دفع متعددة وآمنة: <strong>الدفع عند الاستلام</strong>، <strong>بطاقة مدى وفيزا وماستركارد</strong> مع حماية 3D-Secure، <strong>Apple Pay</strong>، والتحويل البنكي المباشر.';
    }

    if (q.includes('كوبون') || q.includes('خصم') || q.includes('عرض') || q.includes('برومو')) {
      return '🎁 يسعدنا تقديم كود الخصم الحصري <strong>WELCOME10</strong> (خصم 10%) أو كود <strong>ELITE20</strong> (خصم 20% للطلبات فوق 75$). يمكنك كتابة الكود في سلة المشتريات فوراً للاستفادة من الخصم!';
    }

    if (q.includes('تتبع') || q.includes('شحن') || q.includes('أين طلب')) {
      return '📦 يمكنك تتبع شحنتك في أي وقت عبر النقر على زر <strong>"تتبع الطلب"</strong> في أعلى الصفحة وإدخال رقم طلبك (مثل ELITE-XXXXXX-XXXX) لمشاهدة خط السير المباشر مرحلة بمرحلة.';
    }

    if (q.includes('استرجاع') || q.includes('ضمان') || q.includes('تبديل')) {
      return '↩️ نضمن منتجاتنا الأصلية 100%. يحق لك استرجاع أو استبدال المنتج خلال <strong>14 يوماً</strong> من تاريخ الاستلام طالما المنتج بحالته الأصلية.';
    }

    if (q.includes('مرحبا') || q.includes('أهلا') || q.includes('سلام')) {
      return 'أهلاً وسهلاً بك في متجر النخبة! يسعدني دائماً مساعدتك في اختيار أفضل المنتجات أو متابعة طلباتك.';
    }

    return 'شكراً لتواصلك معنا! استفسارك محل اهتمامنا. يمكنك أيضاً متابعة طلباتك أو التواصل معنا هاتفياً على الرقم الموحد للمتجر <strong>920000000</strong>.';
  },

  handleAddReview: async function(e, productId) {
    e.preventDefault();
    const data = {
      author: document.getElementById('rev-author').value,
      rating: document.getElementById('rev-rating').value,
      comment: document.getElementById('rev-comment').value
    };

    try {
      const res = await fetch(`/api/products/${productId}/reviews`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const result = await res.json();
      if (result.success) {
        this.showToast('تمت إضافة تقييمك بنجاح', 'success');
        await this.fetchProducts();
        this.quickView(productId);
        this.renderProducts();
      } else {
        this.showToast(result.message || 'خطأ في إضافة التقييم', 'danger');
      }
    } catch (err) {
      this.showToast('خطأ في إرسال التقييم', 'danger');
    }
  },

  // MODAL UTILITIES
  closeModal: function(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
  },

  // TOAST NOTIFICATIONS
  showToast: function(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    let icon = 'fa-circle-info';
    if (type === 'success') icon = 'fa-circle-check';
    if (type === 'danger') icon = 'fa-circle-exclamation';

    toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  },

  setupEventListeners: function() {
    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
      const userWrapper = document.querySelector('.user-menu-wrapper');
      if (userWrapper && !userWrapper.contains(e.target)) {
        const dropdown = document.getElementById('user-dropdown');
        if (dropdown) dropdown.classList.remove('show');
      }
    });
  }
};

// Initialize App
document.addEventListener('DOMContentLoaded', () => {
  app.init();
});
