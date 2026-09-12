@extends('layouts.website')
@section('content')
<h2 style="font-weight:800">My Orders</h2>
@if(session('success'))<div style="background:#E8F5EE;color:#1D6B43;padding:10px;border-radius:8px;border:1px solid #BFE3D0;margin:10px 0;font-size:13px">{{ session('success') }}</div>@endif
@if($orders->isEmpty())
  <div style="padding:40px;text-align:center;color:var(--text-secondary)">
    <div style="font-size:32px">📦</div>
    <div style="font-weight:700;margin-top:8px">No orders yet</div>
    <div style="font-size:12px;margin-top:4px">Your orders will appear here after you buy.</div>
    <a href="{{ url('/') }}" style="display:inline-block;margin-top:12px;padding:9px 16px;background:var(--primary);color:#fff;border-radius:999px;text-decoration:none;font-weight:700">Shop now</a>
  </div>
@else
  @foreach($orders as $o)
    <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;margin-bottom:12px">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <b>Order #{{ $o->id }}</b>
        <span style="padding:5px 10px;border-radius:999px;background:var(--accent);color:var(--primary-dark);font-weight:700;font-size:11px">{{ $o->status }}</span>
      </div>
      <div style="color:var(--text-secondary);font-size:11px;margin-top:4px">Total ₱{{ number_format($o->total_amount,2) }} • {{ $o->created_at->format('M d, Y') }}</div>
      @foreach($o->items as $it)
        @php
          $prod = $it->product;
          $hasImg = $prod && $prod->image && $prod->image !== 'products/p1_1.png' && file_exists(storage_path('app/public/'.$prod->image));
          $c = '#0F766E';
          if($prod){ $h=array_sum(array_map('ord', str_split($prod->name))); $pal=['#0F766E','#1D4ED8','#7C3AED','#DB2777','#EA580C']; $c=$pal[$h%count($pal)]; }
        @endphp
        <div style="display:flex;gap:10px;align-items:center;padding:8px 0;border-top:1px solid var(--border);margin-top:8px">
          @if($hasImg)
            <img src="{{ asset('storage/'.$prod->image) }}" alt="{{ $it->product_name }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
          @else
            <div style="width:48px;height:48px;background:{{ $c }};border-radius:8px;display:grid;place-items:center;color:#fff;font-weight:800;font-size:11px">{{ strtoupper(substr($it->product_name,0,2)) }}</div>
          @endif
          <div style="flex:1">
            <div style="font-weight:700;font-size:13px">{{ $it->product_name }}</div>
            <div style="font-size:11px;color:var(--text-secondary)">Qty {{ $it->quantity }} • {{ $prod->brand ?? '' }}</div>
          </div>
          <div style="font-weight:700">₱{{ number_format($it->price * $it->quantity,2) }}</div>
        </div>
      @endforeach
    </div>
  @endforeach
@endif
@endsection
