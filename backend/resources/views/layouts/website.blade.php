<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoiz — Desktop Website (PHP/Laravel only)</title>
  <style>
    :root{--primary:#16697A;--primary-dark:#0E4A57;--secondary:#F0A202;--accent:#EAF4F3;--background:#F7F6F2;--card:#FFFFFF;--text-primary:#1B1B1E;--text-secondary:#6E6E73;--success:#2E8B57;--warning:#E05A33;--gold:#F5A623;--border:#E8E6E0;--surface-soft:#F0EEE9}
    *{box-sizing:border-box} body{margin:0;font-family:'Segoe UI',system-ui,sans-serif;background:var(--background);color:var(--text-primary);font-size:12px;line-height:1.5}
    .topbar{height:48px;background:var(--card);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;padding:0 12px;position:sticky;top:0;z-index:20}
    .hamburger{width:32px;height:32px;border:1px solid var(--border);background:#fff;border-radius:8px;display:grid;place-items:center;cursor:pointer;flex-shrink:0;font-size:14px}
    .hamburger:hover{background:var(--surface-soft)}
    .brand{display:flex;align-items:center;gap:6px;font-weight:800;font-size:15px;color:var(--primary-dark)}
    .brand-badge{width:32px;height:32px;border-radius:9px;background:linear-gradient(180deg,var(--primary),var(--primary-dark));color:#fff;display:grid;place-items:center;font-weight:800;font-size:14px}
    .search{flex:1;max-width:420px;height:32px;background:var(--surface-soft);border:1px solid var(--border);border-radius:999px;display:flex;align-items:center;padding:0 10px;gap:6px}
    .search input{flex:1;border:none;background:transparent;outline:none;font-size:12px}
    .shell{display:flex;min-height:calc(100vh - 48px)}
    .sidebar{width:220px;background:var(--card);border-right:1px solid var(--border);padding:10px 8px;position:sticky;top:48px;height:calc(100vh - 48px);overflow:auto;transition:all .2s;flex-shrink:0}
    .sidebar.collapsed{width:0;padding:0;overflow:hidden;border:none;transform:translateX(-100%)}
    .sidebar h4{font-size:9px;letter-spacing:1.2px;color:#9CA3AF;text-transform:uppercase;margin:10px 0 4px;padding:0 6px}
    .side-link{display:flex;align-items:center;gap:10px;padding:7px 10px;border-radius:8px;text-decoration:none;color:#4B5563;font-weight:600;font-size:12px}
    .side-link:hover{background:var(--surface-soft);color:var(--primary-dark)}
    .side-link.active{background:var(--accent);color:var(--primary-dark)}
    .nav-icon{width:16px;height:16px;fill:#9CA3AF;flex-shrink:0}
    .side-link:hover .nav-icon,.side-link.active .nav-icon{fill:var(--primary)}
    .main{flex:1;min-width:0;display:flex;flex-direction:column}
    .nav{height:34px;background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px;padding:0 12px;font-size:11px;overflow:auto;white-space:nowrap}
    .nav a{padding:4px 9px;border-radius:999px;text-decoration:none;color:var(--text-primary);font-weight:600;font-size:11px;border:1px solid transparent;flex-shrink:0}
    .nav a:hover{background:var(--surface-soft);border-color:var(--border)}
    .container{max-width:1160px;margin:0 auto;padding:0 12px;width:100%}
    .hero{height:180px;background:linear-gradient(135deg,#0F766E,#0D9488);border-radius:14px;display:flex;overflow:hidden;margin:8px 12px;box-shadow:0 3px 12px rgba(15,118,110,.12)}
    .hero-left{flex:5;padding:16px 14px;color:#fff;display:flex;flex-direction:column;justify-content:center}
    .hero-right{flex:4;margin:8px;background:#fff;border-radius:10px;display:grid;place-items:center;padding:10px;box-shadow:0 3px 8px rgba(0,0,0,.05)}
    .grid{display:grid;gap:10px}
    @media(min-width:1280px){.grid{grid-template-columns:repeat(5,1fr)}}
    @media(min-width:1024px) and (max-width:1279px){.grid{grid-template-columns:repeat(4,1fr)}}
    @media(min-width:768px) and (max-width:1023px){.grid{grid-template-columns:repeat(3,1fr)}}
    @media(min-width:480px) and (max-width:767px){.grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:479px){.grid{grid-template-columns:repeat(1,1fr)}}
    .card{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:transform .18s, box-shadow .18s}
    .card:hover{transform:translateY(-4px);box-shadow:0 10px 18px rgba(0,0,0,.09)}
    .card-thumb{height:120px;display:grid;place-items:center;color:#fff;font-weight:800;text-align:center;padding:8px}
    .card-body{padding:8px}
    .color-dots{display:flex;gap:3px;align-items:center;margin:3px 0}
    .dot{width:10px;height:10px;border-radius:50%;border:1.5px solid #fff;box-shadow:0 1px 2px rgba(0,0,0,.08)}
    .btn{padding:7px 12px;border-radius:999px;border:none;font-weight:700;cursor:pointer;font-size:11px}
    .btn-primary{background:var(--primary);color:#fff}
    .btn-ghost{background:#fff;border:1px solid var(--border)}
    .footer{padding:12px;background:var(--card);border-top:1px solid var(--border);margin-top:16px;text-align:center;color:var(--text-secondary);font-size:10px}
    /* Responsive */
    @media(max-width:768px){
      .search{display:none}
      .sidebar{position:fixed;left:0;top:48px;z-index:30;box-shadow:2px 0 12px rgba(0,0,0,.08)}
      .sidebar.collapsed{transform:translateX(-100%)}
      .hero{flex-direction:column;height:auto}
      .hero-right{display:none}
      .topbar{padding:0 10px}
    }
    @media(max-width:480px){
      .topbar{height:44px}
      .brand{font-size:14px}
      .brand-badge{width:28px;height:28px;font-size:13px}
      .hero{margin:8px}
      .hero-left{padding:14px}
    }
  </style>
</head>
<body>
  <header class="topbar">
    <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('collapsed')" title="Toggle sidebar">☰</button>
    <div class="brand"><div class="brand-badge">I</div> Invoiz <span style="font-weight:400;color:var(--text-secondary);font-size:11px;margin-left:4px">PHP/Laravel</span></div>
    <form method="GET" action="{{ url('/') }}" class="search"><span>⌕</span><input type="text" name="search" value="{{ request('search') }}" placeholder="Search — desktop store"><button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:999px;padding:5px 10px;cursor:pointer;font-size:12px">Search</button></form>
    <a href="{{ url('/cart') }}" style="text-decoration:none;color:var(--text-primary);font-weight:600;font-size:13px;display:flex;align-items:center;gap:6px"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM17 18c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zM7.22 14h9.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 21 5H6.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7z"/></svg> Cart</a>
    <a href="{{ url('/orders') }}" style="text-decoration:none;color:var(--text-primary);font-weight:600;font-size:13px;display:flex;align-items:center;gap:6px"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6zm0 4h18v2H3v-2zm0 4h12v2H3v-2z"/></svg> Orders</a>
    @if(session('buyer'))
      <span style="font-weight:700;font-size:13px">{{ session('buyer')['first_name'] }}</span>
      <a href="{{ url('/logout') }}" style="color:var(--warning);font-weight:700;text-decoration:none;font-size:12px">Logout</a>
    @else
      <a href="{{ url('/login') }}" class="btn btn-ghost">Login</a>
      <a href="{{ url('/register') }}" class="btn btn-primary">Register</a>
    @endif
  </header>
  <div class="shell">
    <aside id="sidebar" class="sidebar">
      <h4>Overview</h4>
      <a class="side-link active" href="{{ url('/') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm10 8h8V11h-8v10zm0-18v6h8V3h-8zM3 21h8v-6H3v6z"/></svg> Home</a>
      <a class="side-link" href="{{ url('/products') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm2 2v6h12v-6H6z"/></svg> Shop</a>
      <a class="side-link" href="{{ url('/cart') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM17 18c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zM7.22 14h9.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 21 5H6.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7z"/></svg> Basket</a>
      <h4>Account</h4>
      <a class="side-link" href="{{ url('/orders') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6zm0 4h18v2H3v-2zm0 4h12v2H3v-2z"/></svg> My Orders</a>
      <a class="side-link" href="{{ url('/login') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-4.418 0-8 1.79-8 4v2h16v-2c0-2.21-3.582-4-8-4z"/></svg> Login</a>
      <a class="side-link" href="{{ url('/register') }}"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-7 0c2.21 0 4-1.79 4-4S10.21 6 8 6 4 7.79 4 10s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h8v-2c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v2h8v-2c0-2.66-5.33-4-8-4H8z"/></svg> Register</a>
      <h4>Help</h4>
      <a class="side-link" href="#"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/></svg> Messages</a>
      <a class="side-link" href="#"><svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2a1 1 0 0 1 2 0v2a5 5 0 0 0 10 0v-2a1 1 0 1 1 2 0zM12 19a1 1 0 0 1-1-1h2a1 1 0 0 1-1 1z"/></svg> Support</a>
      <div style="margin-top:auto;padding-top:12px;border-top:1px solid var(--border);font-size:10px;color:var(--text-secondary);text-align:center">Invoiz v1 • Desktop</div>
    </aside>
    <div class="main">
      <nav class="nav">
        <a href="{{ url('/') }}">Home</a>
        <a href="{{ url('/?category=1') }}">Categories</a>
        <a href="{{ url('/products') }}">Shop</a>
        <span style="margin-left:auto;color:var(--text-secondary);font-size:10px;font-weight:600">✓ Buyer protection • COD</span>
      </nav>
      <main class="container" style="padding-top:8px;flex:1">
        @yield('content')
      </main>
      <footer class="footer">Invoiz © 2026 • Desktop • PHP/Laravel only • No Dart/Flutter</footer>
    </div>
  </div>
</body>
</html>
