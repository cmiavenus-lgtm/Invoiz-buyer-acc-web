@extends('layouts.website')
@section('content')
<h2 style="font-weight:800">Chat with Seller</h2>
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px;margin-top:12px">
  @if(!session('buyer'))
    <div style="padding:20px;text-align:center;color:var(--text-secondary)">Please <a href="{{ url('/login') }}" style="color:var(--primary);font-weight:700">login as buyer</a> to message the seller.</div>
  @else
    <div style="display:flex;gap:10px;align-items:center;padding-bottom:12px;border-bottom:1px solid var(--border)">
      <div style="width:42px;height:42px;background:var(--primary);border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:800">S</div>
      <div>
        <div style="font-weight:700">Seller #{{ $sellerId }}</div>
        <div style="font-size:11px;color:var(--text-secondary)">Invoiz Store • typically replies in 5 min</div>
      </div>
    </div>
    <div id="msgs" style="height:280px;overflow:auto;padding:12px 0;display:flex;flex-direction:column;gap:8px">
      <div style="align-self:flex-start;background:var(--surface-soft);padding:10px 12px;border-radius:12px;max-width:70%;font-size:13px">Hello! How can I help you with this product?</div>
      <div style="align-self:flex-end;background:var(--primary);color:#fff;padding:10px 12px;border-radius:12px;max-width:70%;font-size:13px">Hi, I have a question about this product.</div>
    </div>
    <form onsubmit="event.preventDefault(); const i=document.getElementById('msgInput'); const v=i.value.trim(); if(!v) return; const d=document.createElement('div'); d.style.cssText='align-self:flex-end;background:var(--primary);color:#fff;padding:10px 12px;border-radius:12px;max-width:70%;font-size:13px'; d.textContent=v; document.getElementById('msgs').appendChild(d); i.value=''; document.getElementById('msgs').scrollTop=9999; setTimeout(()=>{ const r=document.createElement('div'); r.style.cssText='align-self:flex-start;background:var(--surface-soft);padding:10px 12px;border-radius:12px;max-width:70%;font-size:13px'; r.textContent='Thanks for your message! The seller will reply soon.'; document.getElementById('msgs').appendChild(r); document.getElementById('msgs').scrollTop=9999; },800);">
      <div style="display:flex;gap:8px;margin-top:12px">
        <input id="msgInput" type="text" placeholder="Type your message to the seller..." style="flex:1;padding:10px 12px;border:1px solid var(--border);border-radius:999px;outline:none">
        <button type="submit" style="padding:10px 16px;background:var(--primary);color:#fff;border:none;border-radius:999px;font-weight:700;cursor:pointer">Send</button>
      </div>
    </form>
    <div style="margin-top:8px;font-size:11px;color:var(--text-secondary);text-align:center">This is a demo chat — messages are simulated for buyer {{ session('buyer')['first_name'] }} ({{ session('buyer')['email'] }})</div>
  @endif
</div>
@endsection
