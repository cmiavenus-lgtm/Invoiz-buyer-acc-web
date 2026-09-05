@extends('layouts.website')
@section('content')
@php function cFor($n){ $h=array_sum(array_map('ord', str_split($n))); $pal=['#0F766E','#1D4ED8','#7C3AED','#DB2777','#EA580C','#16A34A','#0891B2','#4F46E5']; return $pal[$h%count($pal)]; } @endphp
<a href="{{ url('/') }}" style="color:var(--text-secondary);text-decoration:none;font-weight:600">← Back</a>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px">
  <div style="height:360px;background:{{ cFor($p->name) }};border-radius:16px;display:grid;place-items:center;color:#fff;padding:20px;text-align:center">
    <div>
      <div style="width:80px;height:80px;background:rgba(255,255,255,.22);border-radius:18px;display:grid;place-items:center;margin:0 auto;font-weight:800;font-size:28px">{{ strtoupper(substr($p->name,0,2)) }}</div>
      <div style="margin-top:12px;font-weight:700;font-size:18px">{{ $p->name }}</div>
      <div style="opacity:.9">{{ $p->brand }}</div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:16px">
    <div style="color:#0F766E;font-weight:800;font-size:24px">₱{{ number_format($p->price,2) }}</div>
    <div style="font-weight:700;margin-top:6px">{{ $p->name }}</div>
    <div style="color:var(--text-secondary);font-size:12px;margin-top:4px">{{ $p->stock }} left • {{ $p->category->name ?? '' }}</div>
    @if($p->variants->count())
      <div style="margin-top:12px"><b>Colors:</b> <div style="display:flex;gap:6px;margin-top:6px">@foreach($p->variants as $v)<span style="padding:6px 10px;border-radius:999px;border:1px solid var(--border);font-size:12px;font-weight:600">{{ $v->variant_value }}</span>@endforeach</div></div>
    @endif
    <div style="margin-top:12px"><a href="{{ url('/cart/add/'.$p->id) }}" style="background:var(--primary);color:#fff;padding:10px 16px;border-radius:999px;text-decoration:none;font-weight:700">Add to Cart (Basket)</a></div>
    <div style="margin-top:12px;color:var(--text-secondary);font-size:13px;line-height:1.6">{{ $p->description }}</div>
  </div>
</div>
@endsection
