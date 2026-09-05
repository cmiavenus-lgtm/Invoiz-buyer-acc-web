@extends('layouts.website')
@section('content')
@php
function colorFor($name){ $h=array_sum(array_map('ord', str_split($name))); $pal=['#0F766E','#1D4ED8','#7C3AED','#DB2777','#EA580C','#16A34A','#0891B2','#4F46E5','#DC2626','#475569']; return $pal[$h % count($pal)]; }
function initials($n){ $p=preg_split('/\s+/', trim($n)); if(count($p)>=2) return strtoupper($p[0][0].$p[1][0]); return strtoupper(substr($p[0],0,2)); }
@endphp
<div class="hero">
  <div class="hero-left">
    <div style="background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);padding:5px 8px;border-radius:999px;font-size:10px;font-weight:700;display:inline-block;width:fit-content">✓ Trusted by 10,000+ buyers • COD nationwide</div>
    <div style="font-size:24px;font-weight:800;margin-top:10px">Invoiz — Curated for you</div>
    <div style="font-size:12.5px;margin-top:8px;opacity:.95">Premium essentials at honest prices. Vouchers & verified sellers.</div>
    <div style="margin-top:14px;display:flex;gap:8px">
      <a href="{{ url('/products') }}" class="btn btn-primary" style="background:#fff;color:#0F766E;text-decoration:none">Shop collection</a>
      <a href="{{ url('/products') }}" class="btn btn-ghost" style="color:#fff;border-color:#fff;text-decoration:none">View deals</a>
    </div>
  </div>
  <div class="hero-right">
    <div style="text-align:center">
      <div style="width:64px;height:64px;background:#0F766E;border-radius:16px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:22px;margin:0 auto">I</div>
      <div style="font-weight:800;margin-top:8px">Invoiz</div>
      <div style="font-size:10px;color:#64748B;letter-spacing:1px">Desktop Store</div>
      <div style="margin-top:10px;background:#0F172A;color:#fff;padding:8px 10px;border-radius:12px;font-size:10px;font-weight:700">WELCOME10 • SAVE15 <span style="background:#F59E0B;padding:3px 7px;border-radius:999px;margin-left:6px">COD</span></div>
    </div>
  </div>
</div>

<div style="margin:12px 0 8px;display:flex;gap:8px;overflow:auto;padding:4px 0">
  <a href="{{ url('/') }}" style="padding:8px 14px;border-radius:999px;background:{{ request('category')?'#fff':'#0F766E' }};color:{{ request('category')?'#1B1B1E':'#fff' }};border:1px solid {{ request('category')?'#E2E8F0':'#0F766E' }};text-decoration:none;font-weight:700;font-size:12px">All</a>
  @foreach($categories as $c)
    <a href="{{ url('/?category='.$c->id) }}" style="padding:8px 14px;border-radius:999px;background:{{ (string)request('category')===(string)$c->id ? '#0F766E':'#fff' }};color:{{ (string)request('category')===(string)$c->id ? '#fff':'#1B1B1E' }};border:1px solid {{ (string)request('category')===(string)$c->id ? '#0F766E':'#E2E8F0' }};text-decoration:none;font-weight:700;font-size:12px;white-space:nowrap">{{ $c->name }}</a>
  @endforeach
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin:14px 0 10px">
  <div style="font-weight:800">For You</div>
  <div style="color:var(--text-secondary);font-size:12px">{{ $products->total() }} items • PHP/Laravel</div>
</div>

<div class="grid">
  @foreach($products as $p)
    @php $c=colorFor($p->name); @endphp
    <a href="{{ url('/product/'.$p->id) }}" style="text-decoration:none;color:inherit">
      <div class="card">
        <div class="card-thumb" style="background:{{ $c }}">
          <div>
            <div style="width:44px;height:44px;background:rgba(255,255,255,.22);border-radius:12px;display:grid;place-items:center;margin:0 auto;font-weight:800">{{ initials($p->name) }}</div>
            <div style="margin-top:8px;font-weight:700;font-size:13px">{{ Str::limit($p->name, 28) }}</div>
          </div>
        </div>
        <div class="card-body">
          <div style="font-weight:700;font-size:13.5px;color:{{ $c }};min-height:38px">{{ $p->name }}</div>
          <div class="color-dots">
            @for($i=0;$i<3;$i++)
              <span class="dot" style="background:{{ ['#0F766E','#DB2777','#F59E0B','#1D4ED8','#16A34A'][($p->id+$i)%5] }}"></span>
            @endfor
            <span style="font-size:10px;color:var(--text-secondary);font-weight:600;margin-left:4px">3 colors</span>
          </div>
          <div style="color:#0F766E;font-weight:800;font-size:16px;margin-top:6px">₱{{ number_format($p->price,2) }}</div>
          <div style="font-size:11px;color:var(--text-secondary);margin-top:4px">{{ $p->brand }} • {{ $p->stock }} left • COD</div>
        </div>
      </div>
    </a>
  @endforeach
</div>

<div style="margin:16px 0">{{ $products->links() }}</div>
@endsection
