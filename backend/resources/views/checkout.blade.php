@extends('layouts.website')
@section('content')
<h2 style="font-weight:800">Checkout — choose payment</h2>
<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:16px;margin-top:12px">
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px">
    <h3 style="margin:0 0 10px">Payment method</h3>
    <form method="POST" action="{{ url('/checkout') }}">
      @csrf
      <input type="hidden" name="ids" value="{{ implode(',', $products->pluck('id')->toArray()) }}">
      <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--primary);border-radius:12px;margin-bottom:10px;cursor:pointer;background:var(--accent)">
        <input type="radio" name="payment_method" value="cod" checked style="accent-color:var(--primary)">
        <img src="https://cdn-icons-png.flaticon.com/512/2168/2168741.png" alt="COD" style="width:28px;height:28px;object-fit:contain;background:#fff;border-radius:6px;padding:3px;border:1px solid var(--border)">
        <span style="font-weight:700">Cash on Delivery (COD) — only payment method</span>
        <span style="margin-left:auto;color:var(--primary);font-size:11px;font-weight:700">✓ Selected</span>
      </label>
      <div style="margin-top:12px;padding:10px;background:var(--accent);border:1px solid var(--primary);border-radius:10px;font-size:11px;color:var(--primary-dark)">COD only: Pay when your order is delivered. No GCash/PayMaya.</div>
      <button type="submit" style="width:100%;margin-top:14px;padding:12px;background:var(--primary);color:#fff;border:none;border-radius:10px;font-weight:800;cursor:pointer">Place Order</button>
    </form>
  </div>
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px">
    <h3 style="margin:0 0 10px">Order summary</h3>
    @foreach($products as $p)
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px"><span>{{ $p->name }} × {{ session('cart')[$p->id] ?? 1 }}</span><b>₱{{ number_format($p->price * (session('cart')[$p->id] ?? 1),2) }}</b></div>
    @endforeach
    <div style="display:flex;justify-content:space-between;margin-top:10px;font-weight:800">Total <span>₱{{ number_format($products->sum(function($p){ return $p->price * (session('cart')[$p->id] ?? 1); }),2) }}</span></div>
    @if($products->pluck('seller_id')->unique()->count() > 1)
      <div style="margin-top:8px;padding:8px;background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;color:#92400E;font-size:11px">⚠️ Multiple stores — COD per store may separate.</div>
    @endif
  </div>
</div>
@endsection
