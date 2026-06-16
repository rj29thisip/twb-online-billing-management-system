<!DOCTYPE html>
<html lang="en" id="htmlRoot">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TWB Billing') — TWB Water Billing</title>

  {{-- ── FAVICON ──────────────────────────────────────────────── --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
  <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('favicon-192.png') }}">

  {{-- Preconnect for faster font load --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  {{-- Theme init: read saved preference BEFORE render to avoid flash --}}
  <script>
    (function() {
      var saved = localStorage.getItem('twb_theme') || 'dark';
      if (saved === 'light') document.documentElement.classList.add('light-mode');
    })();
  </script>

  @stack('head')
</head>
<body>

{{-- ═══ HAMBURGER TOGGLE (mobile only) ══════════════════════════ --}}
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Open menu" onclick="toggleSidebar()">
  <span class="material-icons">menu</span>
</button>

{{-- Sidebar overlay (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ═══ SIDEBAR ════════════════════════════════════════════════ --}}
<aside class="sidebar" id="sidebar">
  {{-- Brand logo — links to dashboard --}}
  @if(auth()->user()->isStaff())
  <a href="{{ route('admin.dashboard') }}" class="sidebar-brand" style="text-decoration:none;display:flex;align-items:center;gap:12px;">
  @else
  <a href="{{ route('customer.dashboard') }}" class="sidebar-brand" style="text-decoration:none;display:flex;align-items:center;gap:12px;">
  @endif
    <div class="brand-logo">
      <span class="material-icons" style="color:#fff;font-size:25px">water_drop</span>
    </div>
    <div>
        <div class="brand-text">TWB ONLINE</div>
        <div class="brand-sub">Water Billing Management</div>
    </div>
  </a>

  <nav class="sidebar-nav">
    @if(auth()->user()->isStaff())
      {{-- ── Main / Dashboard (all staff) ── --}}
      <div class="nav-section-title">Main</div>
      <a href="{{ route('admin.dashboard') }}"
         class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">dashboard</span>
        Dashboard
        @if(!auth()->user()->isAdmin() && !auth()->user()->isHeadquarters() && auth()->user()->district_id)
          <span class="sidebar-district-chip">{{ optional(auth()->user()->district)->name }}</span>
        @endif
      </a>

      {{-- ── Customers (admin, cashier, account_employee) ── --}}
      @if(auth()->user()->canManageCustomers())
      <div class="nav-section-title">Customers</div>
      <a href="{{ route('admin.customers.index') }}"
         class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">people</span> Customers
      </a>
      @endif

      {{-- ── Meters (admin, account_employee) ── --}}
      @if(auth()->user()->canAccessMeters())
      <div class="nav-section-title">Meters</div>
      <a href="{{ route('admin.meters.index') }}"
         class="nav-item {{ request()->routeIs('admin.meters.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">speed</span> Meters
      </a>
      <a href="{{ route('admin.readings.index') }}"
         class="nav-item {{ request()->routeIs('admin.readings.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">data_usage</span> Readings
      </a>
      @endif

      {{-- ── Billing (cashier sees invoices+payments; account_employee also sees create) ── --}}
      @if(auth()->user()->canViewInvoices() || auth()->user()->canProcessPayments())
      <div class="nav-section-title">Billing</div>

      @if(auth()->user()->canViewInvoices())
      <a href="{{ route('admin.invoices.index') }}"
         class="nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">receipt_long</span> Invoices
      </a>
      @endif

      @if(auth()->user()->canCreateInvoices())
      <a href="{{ route('admin.billing.check') }}"
         class="nav-item {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">fact_check</span> Create Invoices
      </a>
      @endif

      @if(auth()->user()->canProcessPayments())
      <a href="{{ route('admin.payments.index') }}"
         class="nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">payments</span> Payments
      </a>
      @endif
      @endif

      {{-- ── Configuration (admin, account_employee) ── --}}
      @if(auth()->user()->canAccessConfig())
      <div class="nav-section-title">Configuration</div>
      <a href="{{ route('admin.config.tariffs.index') }}"
         class="nav-item {{ request()->routeIs('admin.config.tariffs.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">layers</span> Tariffs
      </a>
      <a href="{{ route('admin.config.taxes.index') }}"
         class="nav-item {{ request()->routeIs('admin.config.taxes.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">percent</span> Taxes
      </a>
      <a href="{{ route('admin.config.discounts.index') }}"
         class="nav-item {{ request()->routeIs('admin.config.discounts.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">discount</span> Discounts
      </a>
      <a href="{{ route('admin.announcements.index') }}"
         class="nav-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">campaign</span> Announcements
      </a>
      @endif

      {{-- ── Account: Change Password (all staff) ── --}}
      <div class="nav-section-title">Account</div>
      <a href="{{ route('admin.account.password.show') }}"
         class="nav-item {{ request()->routeIs('admin.account.password.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">lock_reset</span> Change Password
      </a>

      {{-- ── System (admin only) ── --}}
      @if(auth()->user()->isAdmin())
      <div class="nav-section-title">System</div>
      <a href="{{ route('admin.users.index') }}"
         class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">manage_accounts</span> Users
      </a>
      <a href="{{ route('admin.districts.index') }}"
         class="nav-item {{ request()->routeIs('admin.districts.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">map</span> Districts
      </a>
      <a href="{{ route('admin.email-config.index') }}"
         class="nav-item {{ request()->routeIs('admin.email-config.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">email</span> Email Config
      </a>
      <a href="{{ route('admin.audit.index') }}"
         class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">history</span> Audit Logs
      </a>
      @endif

    @else
      <div class="nav-section-title">My Account</div>
      <a href="{{ route('customer.dashboard') }}"
         class="nav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">dashboard</span> Dashboard
      </a>
      <a href="{{ route('customer.usage') }}"
         class="nav-item {{ request()->routeIs('customer.usage*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">bar_chart</span> Water Usage
      </a>
      <a href="{{ route('customer.history') }}"
         class="nav-item {{ request()->routeIs('customer.history') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">history</span> Billing History
      </a>
      <a href="{{ route('customer.invoices.index') }}"
         class="nav-item {{ request()->routeIs('customer.invoices.*') ? 'active' : '' }}"
         onclick="closeSidebar()">
        <span class="material-icons">receipt</span> Invoices
      </a>
    @endif
  </nav>

  <div class="sidebar-footer">
    @if(auth()->user()->isStaff())
      {{-- <a href="{{ route('customer.profile') }}" class="user-profile" style="text-decoration:none;" onclick="closeSidebar()"></a> --}}
      <div class="user-profile">
        <div class="avatar">{{ substr(auth()->user()->name, 0, 2) }}</div>
          <div>
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
      </div>
      {{-- </a> --}}
    @else
      <a href="{{ route('customer.profile') }}" class="user-profile" style="text-decoration:none;" onclick="closeSidebar()">
        <div class="avatar">{{ substr(auth()->user()->name, 0, 2) }}</div>
        <div>
          <div class="user-name">{{ auth()->user()->name }}</div>
          <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
      </a>
    @endif
    <form action="{{ route('logout') }}" method="POST" style="margin-top:10px">
      @csrf
      <button type="submit" class="nav-item"
              {{-- style="width:100%;border:none;background:none;cursor:pointer;color:rgba(255,255,255,0.55);font-size:16px;display:flex;align-items:center;gap:14px;font-family:inherit;"> --}}
              style="width:100%; border:none; background:none; cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:flex-start; padding-left:0; gap:14px; font-family:inherit;">
        <span class="material-icons">logout</span> Sign Out
      </button>
    </form>
  </div>
</aside>

{{-- ═══ MAIN CONTENT ════════════════════════════════════════════ --}}
<div class="main-content">

  {{-- TOPBAR --}}
  <header class="topbar">
    <div class="topbar-left">
      <span class="page-breadcrumb">@yield('breadcrumb', 'Dashboard')</span>
      <span class="page-title">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="topbar-right">

      {{-- PDF EXPORT — only on dashboard --}}
      @if(auth()->user()->isStaff() && request()->routeIs('admin.dashboard'))
      <button class="icon-btn" title="Export Dashboard PDF"
              onclick="document.getElementById('dashPdfModal').style.display='flex'"
              style="position:relative;">
        <span class="material-icons">picture_as_pdf</span>
      </button>
      @endif

      {{-- DARK/LIGHT MODE TOGGLE --}}
      <button class="icon-btn" id="themeToggle" title="Toggle light/dark mode" onclick="toggleTheme()">
        <span class="material-icons" id="themeIcon">light_mode</span>
      </button>

      {{-- NOTIFICATION BELL — Customer portal only --}}
      @if(!auth()->user()->isStaff())
      <div class="notif-wrap" id="notifWrap">
        <button class="icon-btn" id="notifBtn" title="Notifications" onclick="toggleNotif(event)">
          <span class="material-icons">notifications</span>
          <div class="badge" id="notifBadge" style="display:none;"></div>
        </button>

        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-header">
            <span class="notif-header-title">Notifications</span>
            <span class="notif-count-badge" id="notifCountBadge" style="display:none;"></span>
          </div>
          <div class="notif-list" id="notifList">
            <div class="notif-empty">
              <span class="material-icons">notifications_none</span>
              Loading...
            </div>
          </div>
        </div>
      </div>
      @endif

    </div>
  </header>

  {{-- FLASH MESSAGES --}}
  @if(session('success'))
    <div class="alert alert-success" role="alert">
      <span class="material-icons" style="font-size:18px">check_circle</span>
      {{ session('success') }}
      <button class="alert-close" onclick="this.parentElement.remove()" aria-label="Dismiss">
        <span class="material-icons" style="font-size:16px">close</span>
      </button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-error" role="alert">
      <span class="material-icons" style="font-size:18px">error</span>
      {{ session('error') }}
      <button class="alert-close" onclick="this.parentElement.remove()" aria-label="Dismiss">
        <span class="material-icons" style="font-size:16px">close</span>
      </button>
    </div>
  @endif

  {{-- PAGE BODY --}}
  <div class="page-body">
    @yield('content')
  </div>

</div>{{-- /main-content --}}

<script src="{{ asset('js/app.js') }}"></script>

<script>
// ── SIDEBAR TOGGLE (mobile) ───────────────────────────────────────
function toggleSidebar() {
  var s = document.getElementById('sidebar');
  var o = document.getElementById('sidebarOverlay');
  if (!s || !o) return;
  var isOpen = s.classList.contains('open');
  if (isOpen) {
    s.classList.remove('open');
    o.classList.remove('open');
    document.documentElement.style.overflow = '';
  } else {
    s.classList.add('open');
    o.classList.add('open');
    // Prevent background scroll on iOS Safari — use html element, not body
    document.documentElement.style.overflow = 'hidden';
  }
}
function closeSidebar() {
  var s = document.getElementById('sidebar');
  var o = document.getElementById('sidebarOverlay');
  if (!s || !o) return;
  s.classList.remove('open');
  o.classList.remove('open');
  document.documentElement.style.overflow = '';
}

// ── LIGHT / DARK TOGGLE ───────────────────────────────────────────
function updateThemeIcon(isLight) {
  var icon = document.getElementById('themeIcon');
  var btn  = document.getElementById('themeToggle');
  if (icon) icon.textContent = isLight ? 'dark_mode' : 'light_mode';
  if (btn)  btn.title        = isLight ? 'Switch to Dark Mode' : 'Switch to Light Mode';
}

function toggleTheme() {
  var root = document.documentElement;
  var isLight = root.classList.contains('light-mode');
  if (isLight) {
    root.classList.remove('light-mode');
    try { localStorage.setItem('twb_theme', 'dark'); } catch(e){}
    updateThemeIcon(false);
  } else {
    root.classList.add('light-mode');
    try { localStorage.setItem('twb_theme', 'light'); } catch(e){}
    updateThemeIcon(true);
  }
}

// Init icon as soon as DOM is ready
(function() {
  var run = function() {
    var isLight = document.documentElement.classList.contains('light-mode');
    updateThemeIcon(isLight);
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();

// Close sidebar on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' || e.keyCode === 27) closeSidebar();
});

// Also close sidebar if window resizes back to desktop width
window.addEventListener('resize', function() {
  if (window.innerWidth > 768) closeSidebar();
});
</script>

@if(!auth()->user()->isStaff())
<script>
// ── CUSTOMER NOTIFICATION SYSTEM (ES5 compatible) ─────────────────
var NOTIF_URL = '{{ route("notifications.index") }}';
var notifLoaded = false;

function toggleNotif(e) {
  e.stopPropagation();
  var dd = document.getElementById('notifDropdown');
  var wasOpen = dd.classList.contains('open');
  dd.classList.toggle('open');
  if (!wasOpen && !notifLoaded) { loadNotifications(); }
}

document.addEventListener('click', function(e) {
  var wrap = document.getElementById('notifWrap');
  if (wrap && !wrap.contains(e.target)) {
    var dd = document.getElementById('notifDropdown');
    if (dd) { dd.classList.remove('open'); }
  }
});

function loadNotifications() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', NOTIF_URL, true);
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.setRequestHeader('Accept', 'application/json');
  xhr.onload = function() {
    if (xhr.status >= 200 && xhr.status < 300) {
      try {
        var data = JSON.parse(xhr.responseText);
        notifLoaded = true;
        renderNotifications(data);
      } catch(ex) {
        showNotifError();
      }
    } else {
      showNotifError();
    }
  };
  xhr.onerror = function() { showNotifError(); };
  xhr.send();
}

function showNotifError() {
  var el = document.getElementById('notifList');
  if (el) { el.innerHTML = '<div class="notif-empty"><span class="material-icons">error_outline</span>Could not load</div>'; }
}

function renderNotifications(data) {
  var list  = document.getElementById('notifList');
  var badge = document.getElementById('notifBadge');
  var count = document.getElementById('notifCountBadge');
  var n     = data.count || 0;

  if (n > 0) {
    badge.style.display = 'block';
    count.style.display = 'inline-block';
    count.textContent   = n > 99 ? '99+' : String(n);
  } else {
    badge.style.display = 'none';
    count.style.display = 'none';
  }

  if (n === 0) {
    list.innerHTML = '<div class="notif-empty"><span class="material-icons">notifications_none</span>No new notifications</div>';
    return;
  }

  var colorMap = { orange:'notif-icon-orange', blue:'notif-icon-blue', purple:'notif-icon-purple', green:'notif-icon-green' };
  var html = '';
  for (var i = 0; i < data.notifications.length; i++) {
    var item = data.notifications[i];
    var iconClass = colorMap[item.color] || 'notif-icon-blue';
    html += '<a class="notif-item" href="' + esc(item.url) + '">' +
      '<div class="notif-icon-wrap ' + iconClass + '">' +
      '<span class="material-icons">' + esc(item.icon) + '</span></div>' +
      '<div class="notif-body">' +
      '<div class="notif-title">' + esc(item.title) + '</div>' +
      '<div class="notif-msg">'   + esc(item.message) + '</div>' +
      '<div class="notif-time">'  + esc(item.time)    + '</div>' +
      '</div></a>';
  }
  list.innerHTML = html;
}

function esc(str) {
  var d = document.createElement('div');
  d.appendChild(document.createTextNode(str || ''));
  return d.innerHTML;
}

// Load notification badge count on page load
(function() {
  var run = function() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', NOTIF_URL, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          var data = JSON.parse(xhr.responseText);
          if (data.count > 0) {
            var b = document.getElementById('notifBadge');
            var c = document.getElementById('notifCountBadge');
            if (b) { b.style.display = 'block'; }
            if (c) { c.style.display = 'inline-block'; c.textContent = data.count > 99 ? '99+' : String(data.count); }
          }
        } catch(ex) {}
      }
    };
    xhr.onerror = function() {};
    xhr.send();
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
</script>
@endif

@stack('scripts')

@if(auth()->user()->isStaff())
{{-- ═══ DASHBOARD PDF EXPORT MODAL ══════════════════════════════════ --}}
<div id="dashPdfModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;">
  <div style="background:var(--surface,#1e2a3a);border:1px solid var(--border,rgba(255,255,255,0.1));border-radius:12px;padding:28px 32px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,0.4);">

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
      <span class="material-icons" style="color:#e74c3c;font-size:22px;">picture_as_pdf</span>
      <h3 style="margin:0;font-size:16px;color:var(--text-primary,#fff);">Export Executive Summary</h3>
    </div>
    <p style="font-size:13px;color:var(--text-secondary,rgba(255,255,255,0.6));margin:0 0 20px;">
      Generate a PDF report containing financial overview, collection rates, meter readings, anomalies, overdue invoices, and top customers by usage.
    </p>

    <div style="background:rgba(52,152,219,0.1);border:1px solid rgba(52,152,219,0.3);border-radius:8px;padding:14px 16px;margin-bottom:20px;">
      <div style="font-size:12px;color:var(--text-secondary,rgba(255,255,255,0.6));margin-bottom:6px;">Select Period</div>
      <input type="month" id="pdfMonth"
             value="{{ now()->format('Y-m') }}"
             max="{{ now()->format('Y-m') }}"
             style="width:100%;background:var(--surface-2,rgba(255,255,255,0.05));border:1px solid var(--border,rgba(255,255,255,0.15));border-radius:6px;padding:8px 12px;color:var(--text-primary,#fff);font-size:13px;font-family:inherit;">
      <div style="font-size:11px;color:var(--text-muted,rgba(255,255,255,0.4));margin-top:6px;" id="pdfTitlePreview">
        Executive Summary for {{ now()->format('F Y') }} — as per {{ now()->format('d/m/Y') }}
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <button onclick="document.getElementById('dashPdfModal').style.display='none'"
              style="padding:8px 18px;background:none;border:1px solid var(--border,rgba(255,255,255,0.15));border-radius:6px;color:var(--text-secondary,rgba(255,255,255,0.6));cursor:pointer;font-size:13px;">
        Cancel
      </button>
      <button id="btnDlPdf" onclick="downloadDashPdf()"
              style="padding:8px 20px;background:var(--accent-blue,#1a73e8);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:13px;font-weight:500;display:flex;align-items:center;gap:6px;">
        <span class="material-icons" style="font-size:16px;">download</span> Download PDF
      </button>
    </div>
  </div>
</div>

<script>
(function() {
  var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  var today = '{{ now()->format("d/m/Y") }}';
  var baseUrl = '{{ route("admin.dashboard.export-pdf") }}';

  function labelFromVal(val) {
    if (!val) return '';
    var parts = val.split('-');
    return monthNames[parseInt(parts[1],10)-1] + ' ' + parts[0];
  }

  var picker = document.getElementById('pdfMonth');
  var preview = document.getElementById('pdfTitlePreview');

  if (picker) {
    picker.addEventListener('change', function() {
      if (preview) preview.textContent = 'Executive Summary for ' + labelFromVal(this.value) + ' — as per ' + today;
    });
  }

  window.downloadDashPdf = function() {
    var val = picker ? picker.value : '{{ now()->format("Y-m") }}';
    var btn = document.getElementById('btnDlPdf');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="material-icons" style="font-size:16px;">refresh</span> Generating...';
    }
    // Use direct navigation — respects Content-Disposition: attachment header properly
    // (Edge/Chrome sometimes ignore it with programmatic <a> clicks)
    window.location.href = baseUrl + '?month=' + val;
    setTimeout(function() {
      document.getElementById('dashPdfModal').style.display = 'none';
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons" style="font-size:16px;">download</span> Download PDF';
      }
    }, 3000);
  };

  // Close modal on backdrop click
  document.getElementById('dashPdfModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
})();
</script>
@endif
</body>
</html>
