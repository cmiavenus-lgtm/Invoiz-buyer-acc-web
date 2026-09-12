@extends('layouts.website')
@section('content')
<h2 style="font-weight:800">Messages — who you can message</h2>
<p style="color:var(--text-secondary);font-size:12px;margin-top:4px">Like Shopee — chat with Invoiz shops</p>
<div style="display:grid;grid-template-columns:320px 1fr;gap:16px;margin-top:14px;min-height:420px">
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden">
    <div style="padding:12px 14px;border-bottom:1px solid var(--border);font-weight:700;font-size:13px">Chats</div>
    <div style="padding:8px">
      <a href="{{ url('/chat/seller/2') }}" style="display:flex;gap:10px;align-items:center;padding:10px;border-radius:10px;text-decoration:none;color:var(--text-primary);background:var(--accent);border:1px solid var(--border)">
        <div style="width:42px;height:42px;background:var(--primary);border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:800">I</div>
        <div style="flex:1">
          <div style="font-weight:700;font-size:13px">Invoiz Store</div>
          <div style="font-size:11px;color:var(--text-secondary)">Tap to chat • Online</div>
        </div>
        <span style="width:8px;height:8px;background:var(--success);border-radius:50%"></span>
      </a>
      <div style="margin-top:8px;padding:10px;background:var(--surface-soft);border-radius:10px;font-size:11px;color:var(--text-secondary)">
        <b>Other shops</b><br>
        More sellers will appear here when you follow stores or order from them. Like Shopee, all your shop chats in one place.
      </div>
    </div>
  </div>
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px;display:grid;place-items:center;text-align:center">
    <div>
      <div style="width:56px;height:56px;background:var(--accent);border-radius:50%;display:grid;place-items:center;margin:0 auto;color:var(--primary);font-size:22px">💬</div>
      <div style="font-weight:700;margin-top:10px">Select a shop to chat</div>
      <div style="font-size:12px;color:var(--text-secondary);margin-top:4px">Choose <b>Invoiz Store</b> on the left to start messaging — like Shopee chat.</div>
      <a href="{{ url('/chat/seller/2') }}" style="display:inline-block;margin-top:12px;padding:9px 16px;background:var(--primary);color:#fff;border-radius:999px;text-decoration:none;font-weight:700">Message Invoiz Store</a>
    </div>
  </div>
</div>
@endsection
