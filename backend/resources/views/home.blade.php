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
    <div style="margin-top:14px">
      <a href="{{ url('/products') }}" class="btn btn-primary" style="background:#fff;color:#0F766E;text-decoration:none">Shop collection</a>
    </div>
  </div>
  <div class="hero-right">
    <div style="text-align:center">
      <img src="{{ asset('images/logo.png') }}" alt="Invoiz" style="width:80px;height:auto;object-fit:contain;margin:0 auto;display:block;background:#fff;border-radius:8px;padding:4px;border:1px solid var(--border)">
      <div style="font-size:9px;color:#64748B;letter-spacing:1px;margin-top:4px">Desktop Store • Curated for you</div>
      <div style="margin-top:8px;background:#0F766E;color:#fff;padding:6px 8px;border-radius:999px;font-size:9px;font-weight:700;text-align:center">Welcome Save 15 COD</div>
    </div>
  </div>
</div>

<div style="margin:12px 0 8px;display:flex;gap:8px;overflow:auto;padding:4px 0;flex-wrap:wrap;max-height:80px;align-content:flex-start">
  <a href="{{ url('/') }}" style="padding:8px 14px;border-radius:999px;background:{{ request('category')?'#fff':'#0F766E' }};color:{{ request('category')?'#1B1B1E':'#fff' }};border:1px solid {{ request('category')?'#E2E8F0':'#0F766E' }};text-decoration:none;font-weight:700;font-size:12px">All</a>
  @foreach($categories as $c)
    <a href="{{ url('/?category='.$c->id) }}" style="padding:8px 14px;border-radius:999px;background:{{ (string)request('category')===(string)$c->id ? '#0F766E':'#fff' }};color:{{ (string)request('category')===(string)$c->id ? '#fff':'#1B1B1E' }};border:1px solid {{ (string)request('category')===(string)$c->id ? '#0F766E':'#E2E8F0' }};text-decoration:none;font-weight:700;font-size:12px;white-space:nowrap">{{ $c->name }}</a>
  @endforeach
</div>

<div class="grid" style="margin-top:14px">
  @foreach($products as $p)
    @php $c=colorFor($p->name); $hasRealImage = $p->image && $p->image !== 'products/p1_1.png' && file_exists(storage_path('app/public/'.$p->image)); $qs = request('category') ? '?category='.request('category').(request('search') ? '&search='.urlencode(request('search')) : '') : (request('search') ? '?search='.urlencode(request('search')) : ''); @endphp
    <a href="{{ url('/product/'.$p->id.$qs) }}" style="text-decoration:none;color:inherit">
      <div class="card">
        <div class="card-thumb" style="background:{{ $hasRealImage ? '#fff' : $c }};padding:0;overflow:hidden">
          @if($hasRealImage)
            <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}" style="width:100%;height:100%;object-fit:cover;display:block">
          @else
            <div style="width:100%;height:100%;display:grid;place-items:center;background:{{ $c }};padding:12px">
              <div>
                <div style="width:44px;height:44px;background:rgba(255,255,255,.22);border-radius:12px;display:grid;place-items:center;margin:0 auto;font-weight:800;color:#fff">{{ initials($p->name) }}</div>
                <div style="margin-top:8px;font-weight:700;font-size:13px;color:#fff">{{ $p->name }}</div>
              </div>
            </div>
          @endif
        </div>
        <div class="card-body">
          <div style="font-weight:700;font-size:13.5px;color:{{ $c }}">{{ $p->name }}</div>
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

@endsection
