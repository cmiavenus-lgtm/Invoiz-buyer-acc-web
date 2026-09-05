@extends('layouts.website')
@section('content')
<h2 style="font-weight:800">Basket (Cart) — color with name</h2>
@php function cf($n){ $h=array_sum(array_map('ord', str_split($n))); $pal=['#0F766E','#1D4ED8','#7C3AED','#DB2777','#EA580C']; return $pal[$h%count($pal)]; } @endphp
@if($products->isEmpty())
  <div style="padding:40px;text-align:center;color:var(--text-secondary)">Basket empty — add products from home.</div>
@else
  @foreach($products as $p)
    @php $c=cf($p->name); @endphp
    <div style="display:flex;gap:12px;align-items:center;background:#fff;border:1px solid var(--border);border-left:4px solid {{ $c }};border-radius:14px;padding:10px;margin-bottom:10px">
      <div style="width:72px;height:72px;background:{{ $c }};border-radius:10px;display:grid;place-items:center;color:#fff;font-weight:800">{{ strtoupper(substr($p->name,0,2)) }}</div>
      <div style="flex:1">
        <div style="font-weight:700;color:{{ $c }}">{{ $p->name }}</div>
        <div style="font-size:11px;color:var(--text-secondary)">{{ $p->name }}</div>
        <div style="margin-top:6px;display:inline-block;background:{{ $c }};color:#fff;padding:4px 8px;border-radius:999px;font-weight:800;font-size:12px">₱{{ number_format($p->price,2) }}</div>
      </div>
      <div style="color:var(--text-secondary)">Qty {{ session('cart')[$p->id] ?? 1 }}</div>
    </div>
  @endforeach
@endif
@endsection
