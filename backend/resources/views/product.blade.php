@extends('layouts.website')
@section('content')
@php function cFor($n){ $h=array_sum(array_map('ord', str_split($n))); $pal=['#0F766E','#1D4ED8','#7C3AED','#DB2777','#EA580C','#16A34A','#0891B2','#4F46E5']; return $pal[$h%count($pal)]; } @endphp
<a href="{{ url('/'.(request('category') ? '?category='.request('category').(request('search') ? '&search='.urlencode(request('search')) : '') : (request('search') ? '?search='.urlencode(request('search')) : ''))) }}" style="color:var(--text-secondary);text-decoration:none;font-weight:600">← Back to {{ request('category') ? 'filtered' : 'store' }}</a>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px">
  @php $hasPImg = $p->image && $p->image !== 'products/p1_1.png' && file_exists(storage_path('app/public/'.$p->image)); @endphp
  <div style="height:420px;background:#fff;border-radius:16px;display:grid;place-items:center;padding:12px;overflow:hidden;text-align:center;border:1px solid var(--border)">
    @if($hasPImg)
      <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}" style="max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;display:block">
    @else
      <div style="width:100%;height:100%;background:{{ cFor($p->name) }};border-radius:12px;display:grid;place-items:center;color:#fff;padding:20px">
        <div>
          <div style="width:80px;height:80px;background:rgba(255,255,255,.22);border-radius:18px;display:grid;place-items:center;margin:0 auto;font-weight:800;font-size:28px">{{ strtoupper(substr($p->name,0,2)) }}</div>
          <div style="margin-top:12px;font-weight:700;font-size:18px">{{ $p->name }}</div>
          <div style="opacity:.9">{{ $p->brand }}</div>
        </div>
      </div>
    @endif
  </div>
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:16px">
    <div style="color:#0F766E;font-weight:800;font-size:24px">₱{{ number_format($p->price,2) }}</div>
    <div style="font-weight:700;margin-top:6px">{{ $p->name }}</div>
    <div style="color:var(--text-secondary);font-size:12px;margin-top:4px">{{ $p->stock }} left • {{ $p->category->name ?? '' }}</div>
    @if($p->variants->count())
      <div style="margin-top:12px"><b>Colors:</b> <div style="display:flex;gap:6px;margin-top:6px">@foreach($p->variants as $v)<span style="padding:6px 10px;border-radius:999px;border:1px solid var(--border);font-size:12px;font-weight:600">{{ $v->variant_value }}</span>@endforeach</div></div>
    @endif
    <div style="margin-top:16px;display:flex;gap:10px">
      <a href="{{ url('/cart/add/'.$p->id) }}" style="flex:1;text-align:center;background:#fff;color:var(--primary);border:1.5px solid var(--primary);padding:11px 16px;border-radius:999px;text-decoration:none;font-weight:700">🛒 Add to Cart</a>
      <a href="{{ url('/buy/'.$p->id) }}" style="flex:1;text-align:center;background:var(--primary);color:#fff;padding:11px 16px;border-radius:999px;text-decoration:none;font-weight:700">⚡ Buy Now</a>
    </div>
    <a href="{{ url('/chat/seller/'.$p->seller_id) }}" style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:10px;padding:10px;background:#fff;border:1px solid var(--border);border-radius:999px;text-decoration:none;color:var(--primary-dark);font-weight:700;font-size:13px">
      <svg style="width:16px;height:16px;fill:var(--primary)" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/></svg>
      Message Seller — tell something
    </a>
    <div style="margin-top:12px;color:var(--text-secondary);font-size:13px;line-height:1.6">{{ $p->description }}</div>
  </div>
</div>
@endsection
