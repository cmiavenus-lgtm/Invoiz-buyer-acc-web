@extends('layouts.website')
@section('content')
@php function cf($n){ $h=array_sum(array_map('ord', str_split($n))); $pal=['#0F766E','#1D4ED8','#7C3AED','#DB2777','#EA580C']; return $pal[$h%count($pal)]; } @endphp
@if(session('success'))<div style="background:#E8F5EE;color:#1D6B43;padding:10px;border-radius:8px;border:1px solid #BFE3D0;margin-bottom:10px;font-size:13px">{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#FDECEC;color:#B91C1C;padding:10px;border-radius:8px;border:1px solid #FECACA;margin-bottom:10px;font-size:13px">{{ session('error') }}</div>@endif
@if($products->isEmpty())
  <div style="padding:60px 20px;text-align:center">
    <div style="width:64px;height:64px;background:var(--surface-soft);border-radius:50%;display:grid;place-items:center;margin:0 auto 12px;font-size:24px">🛒</div>
    <div style="font-weight:700;font-size:14px">Your cart is empty</div>
    <div style="color:var(--text-secondary);font-size:12px;margin-top:4px">Add products from the store to see them here.</div>
    <a href="{{ url('/') }}" style="display:inline-block;margin-top:14px;padding:9px 16px;background:var(--primary);color:#fff;border-radius:999px;text-decoration:none;font-weight:700;font-size:13px">Go shopping</a>
  </div>
@else
  <h2 style="font-weight:800;margin:0 0 12px">Cart</h2>
  @foreach($products as $p)
    @php $c=cf($p->name); $qty = session('cart')[$p->id] ?? 1; $hasImg = $p->image && $p->image !== 'products/p1_1.png' && file_exists(storage_path('app/public/'.$p->image)); @endphp
    <div style="display:flex;gap:12px;align-items:center;background:#fff;border:1px solid var(--border);border-left:4px solid {{ $c }};border-radius:14px;padding:10px;margin-bottom:10px">
      @if($hasImg)
        <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}" style="width:72px;height:72px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
      @else
        <div style="width:72px;height:72px;background:{{ $c }};border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:800">{{ strtoupper(substr($p->name,0,2)) }}</div>
      @endif
      <div style="flex:1">
        <div style="font-weight:700;color:{{ $c }}">{{ $p->name }}</div>
        <div style="font-size:11px;color:var(--text-secondary)">{{ $p->brand }} • {{ $p->stock }} left</div>
        <div style="margin-top:6px;display:inline-block;background:{{ $c }};color:#fff;padding:4px 8px;border-radius:999px;font-weight:800;font-size:12px">₱{{ number_format($p->price,2) }}</div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;min-width:140px">
        <div style="display:flex;align-items:center;gap:6px;background:var(--surface-soft);border:1px solid var(--border);border-radius:999px;padding:3px">
          <a href="{{ url('/cart/dec/'.$p->id) }}" style="width:28px;height:28px;border-radius:50%;background:#fff;border:1px solid var(--border);display:grid;place-items:center;text-decoration:none;color:var(--text-primary);font-weight:800">−</a>
          <span style="min-width:28px;text-align:center;font-weight:700;font-size:13px">Qty {{ $qty }}</span>
          <a href="{{ url('/cart/add/'.$p->id) }}" style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;text-decoration:none;font-weight:800">+</a>
        </div>
        <div style="display:flex;gap:6px">
          <a href="{{ url('/cart/remove/'.$p->id) }}" style="padding:6px 10px;border-radius:999px;border:1px solid var(--border);text-decoration:none;color:var(--text-secondary);font-size:11px;font-weight:600">Remove</a>
          <a href="{{ url('/buy/'.$p->id) }}" style="padding:7px 14px;border-radius:999px;background:var(--primary);color:#fff;text-decoration:none;font-weight:700;font-size:12px">Buy</a>
        </div>
      </div>
    </div>
  @endforeach
  <div style="margin-top:14px;display:flex;justify-content:flex-end;gap:10px;align-items:center;flex-wrap:wrap">
    <div style="font-weight:700">Total: ₱{{ number_format($products->sum('price'),2) }} <span style="font-weight:400;color:var(--text-secondary);font-size:11px">({{ $products->count() }} items)</span></div>
    <a href="{{ url('/buy/all') }}" style="padding:10px 18px;border-radius:999px;background:var(--primary);color:#fff;text-decoration:none;font-weight:700">Buy All</a>
  </div>
@endif
@endsection

