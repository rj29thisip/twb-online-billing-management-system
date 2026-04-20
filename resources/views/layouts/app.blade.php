<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'TWB Billing') — TWB Water Billing</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
  <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('favicon-192.png') }}">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('head')
</head>
<body>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo"><span class="material-icons" style="color:#fff;font-size:20px">water_drop</span></div>
    <div><div class="brand-text">TWB Billing</div><div class="brand-sub">Customer Portal</div></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-title">My Account</div>
    <a href="{{ route('customer.dashboard') }}" class="nav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
      <span class="material-icons">dashboard</span> Dashboard
    </a>
    <a href="{{ route('customer.usage') }}" class="nav-item {{ request()->routeIs('customer.usage*') ? 'active' : '' }}">
      <span class="material-icons">bar_chart</span> Water Usage
    </a>
    <a href="{{ route('customer.history') }}" class="nav-item {{ request()->routeIs('customer.history') ? 'active' : '' }}">
      <span class="material-icons">history</span> Billing History
    </a>
  </nav>
  <div class="sidebar-footer">
    <a href="{{ route('customer.profile') }}" class="user-profile" style="text-decoration:none;display:flex;align-items:center;gap:12px;">
      <div class="avatar">{{ substr(auth()->user()->name, 0, 2) }}</div>
      <div><div class="user-name">{{ auth()->user()->name }}</div><div class="user-role">Customer</div></div>
    </a>
    <form action="{{ route('logout') }}" method="POST" style="margin-top:10px">
      @csrf
      <button type="submit" class="nav-item" style="width:100%;border:none;background:none;cursor:pointer;color:var(--text-secondary);font-size:13.5px;display:flex;align-items:center;gap:14px;">
        <span class="material-icons">logout</span> Sign Out
      </button>
    </form>
  </div>
</aside>
<div class="main-content">
  <header class="topbar">
    <div class="topbar-left">
      <span class="page-breadcrumb">@yield('breadcrumb', 'Dashboard')</span>
      <span class="page-title">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="topbar-right">
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
            <div class="notif-empty"><span class="material-icons">notifications_none</span>Loading...</div>
          </div>
        </div>
      </div>
    </div>
  </header>
  @if(session('success'))
    <div class="alert alert-success" role="alert">
      <span class="material-icons" style="font-size:18px">check_circle</span>
      {{ session('success') }}
      <button class="alert-close" onclick="this.parentElement.remove()"><span class="material-icons" style="font-size:16px">close</span></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-error" role="alert">
      <span class="material-icons" style="font-size:18px">error</span>
      {{ session('error') }}
      <button class="alert-close" onclick="this.parentElement.remove()"><span class="material-icons" style="font-size:16px">close</span></button>
    </div>
  @endif
  <div class="page-body">@yield('content')</div>
</div>
<script src="{{ asset('js/app.js') }}"></script>
<script>
const NOTIF_URL='{{ route("notifications.index") }}';let notifLoaded=false;
function toggleNotif(e){e.stopPropagation();const dd=document.getElementById('notifDropdown');const w=dd.classList.contains('open');dd.classList.toggle('open');if(!w&&!notifLoaded)loadNotifications();}
document.addEventListener('click',function(e){const w=document.getElementById('notifWrap');if(w&&!w.contains(e.target))document.getElementById('notifDropdown')?.classList.remove('open');});
function loadNotifications(){fetch(NOTIF_URL,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}).then(r=>r.json()).then(d=>{notifLoaded=true;renderNotifications(d);}).catch(()=>{document.getElementById('notifList').innerHTML='<div class="notif-empty"><span class="material-icons">error_outline</span>Could not load</div>';});}
function renderNotifications(data){const list=document.getElementById('notifList');const badge=document.getElementById('notifBadge');const count=document.getElementById('notifCountBadge');const n=data.count||0;if(n>0){badge.style.display='block';count.style.display='inline-block';count.textContent=n>99?'99+':n;}else{badge.style.display='none';count.style.display='none';}if(n===0){list.innerHTML='<div class="notif-empty"><span class="material-icons">notifications_none</span>No new notifications</div>';return;}const cm={orange:'notif-icon-orange',blue:'notif-icon-blue',purple:'notif-icon-purple',green:'notif-icon-green'};list.innerHTML=data.notifications.map(item=>`<a class="notif-item" href="${item.url}"><div class="notif-icon-wrap ${cm[item.color]||'notif-icon-blue'}"><span class="material-icons">${item.icon}</span></div><div class="notif-body"><div class="notif-title">${esc(item.title)}</div><div class="notif-msg">${esc(item.message)}</div><div class="notif-time">${esc(item.time)}</div></div></a>`).join('');}
function esc(s){const d=document.createElement('div');d.appendChild(document.createTextNode(s||''));return d.innerHTML;}
document.addEventListener('DOMContentLoaded',function(){fetch(NOTIF_URL,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}).then(r=>r.json()).then(data=>{if(data.count>0){document.getElementById('notifBadge').style.display='block';const c=document.getElementById('notifCountBadge');c.style.display='inline-block';c.textContent=data.count>99?'99+':data.count;}}).catch(()=>{});});
</script>
@stack('scripts')
</body>
</html>
