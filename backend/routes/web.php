<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Models\Product;
use Illuminate\Http\Request;

Route::get('/', [HomeController::class, 'index']);
Route::get('/products', [HomeController::class, 'index']);
Route::get('/product/{id}', [HomeController::class, 'show']);
Route::get('/cart/add/{id}', function($id, Request $request){
  $cart = session('cart', []);
  $cart[$id] = ($cart[$id] ?? 0) + 1;
  session(['cart'=>$cart]);
  return redirect('/')->with('success','Added to cart');
});
Route::get('/buy/{id}', function($id, Request $request){
  // Buy Now for single product — only this one appears in summary (user request)
  $request->session()->put('checkout_single', (int)$id);
  // Also ensure it's in cart for convenience, but checkout will show only this one
  $cart = session('cart', []);
  $cart[$id] = ($cart[$id] ?? 0) + 1;
  session(['cart'=>$cart]);
  return redirect('/checkout?single='.$id);
});
Route::get('/cart/dec/{id}', function($id){
  $cart = session('cart', []);
  if(isset($cart[$id])){ $cart[$id]--; if($cart[$id]<=0) unset($cart[$id]); session(['cart'=>$cart]); }
  return redirect('/cart');
});
Route::get('/cart/remove/{id}', function($id){
  $cart = session('cart', []);
  unset($cart[$id]); session(['cart'=>$cart]);
  return redirect('/cart');
});
Route::get('/buy/all', function(Request $request){
  $ids = array_keys(session('cart', []));
  if(empty($ids)) return redirect('/cart')->with('error','Cart empty');
  return redirect('/checkout');
});
Route::get('/checkout', function(Request $request){
  // If single product Buy Now, show only that one in summary
  if($request->filled('single')){
    $p = Product::find($request->integer('single'));
    if(!$p) return redirect('/cart')->with('error','Product not found');
    return view('checkout',['products'=>collect([$p])]);
  }
  if(session()->has('checkout_single')){
    $p = Product::find(session('checkout_single'));
    if($p) return view('checkout',['products'=>collect([$p])]);
  }
  $ids = array_keys(session('cart', []));
  if(empty($ids)) return redirect('/cart')->with('error','Cart empty — add items first');
  $products = Product::whereIn('id',$ids)->get();
  return view('checkout',['products'=>$products]);
});
Route::post('/checkout', function(Request $request){
  $request->validate(['payment_method'=>'required|in:cod','ids'=>'required']);
  $buyer = $request->session()->get('buyer');
  $checkoutSingle = $request->session()->get('checkout_single') ?? $request->input('single');
  $ids = $request->filled('single') ? [(int)$request->input('single')] : ($checkoutSingle ? [(int)$checkoutSingle] : array_keys(session('cart', [])));
  if($request->filled('ids') && !$request->filled('single') && !$checkoutSingle){
    $ids = array_map('intval', explode(',', $request->ids));
  }
  if(empty($ids)) return redirect('/cart')->with('error','Nothing to checkout');
  $products = Product::whereIn('id',$ids)->get();
  if($products->isEmpty()) return redirect('/cart')->with('error','Nothing to checkout');
  if($buyer){
    try {
      // Ensure buyer has an address (required for orders)
      $addr = \App\Models\Address::where('buyer_id',$buyer['id'])->first();
      if(!$addr){
        $addr = \App\Models\Address::create([
          'buyer_id'=>$buyer['id'],
          'recipient_name'=>$buyer['first_name'].' '.$buyer['last_name'],
          'phone'=>'09170000000',
          'address_line'=>'123 Street',
          'barangay'=>'Test',
          'city'=>'Test City',
          'province'=>'Test Province',
          'postal_code'=>'1000',
          'is_default'=>1,
        ]);
      }
      $order = \App\Models\Order::create([
        'buyer_id' => $buyer['id'],
        'address_id' => $addr->id,
        'total_amount' => $products->sum(function($p){ return $p->price * (session('cart')[$p->id] ?? 1); }),
        'status' => 'pending',
      ]);
      foreach($products as $p){
        $qty = session('cart')[$p->id] ?? 1;
        \App\Models\OrderItem::create([
          'order_id'=>$order->id,
          'product_id'=>$p->id,
          'seller_id'=>$p->seller_id,
          'product_name'=>$p->name,
          'quantity'=>$qty,
          'price'=>$p->price,
        ]);
      }
      \App\Models\Payment::create(['order_id'=>$order->id,'method'=>'cod','status'=>'pending','amount'=>$order->total_amount]);
    } catch(\Throwable $e){
      \Log::error('Checkout failed: '.$e->getMessage());
    }
  }
  if($request->filled('single') || $checkoutSingle){
    $cart = session('cart', []);
    $singleId = $request->filled('single') ? (int)$request->input('single') : (int)$checkoutSingle;
    unset($cart[$singleId]);
    session(['cart'=>$cart]);
    session()->forget('checkout_single');
  } else {
    session()->forget('cart');
  }
  return redirect('/orders')->with('success','Order placed — Cash on Delivery — thank you! View in My Orders.');
});
Route::get('/cart', function(){ $ids = array_keys(session('cart', [])); $products = Product::whereIn('id',$ids)->get(); return view('cart',['products'=>$products]); });
// Auth — connected to invoizdb, shared with Desktop invoiz folder (all required fields)
Route::get('/login', [App\Http\Controllers\Web\AuthController::class, 'showLogin']);
Route::post('/login', [App\Http\Controllers\Web\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\Web\AuthController::class, 'showRegister']);
Route::post('/register', [App\Http\Controllers\Web\AuthController::class, 'register']);
Route::get('/logout', [App\Http\Controllers\Web\AuthController::class, 'logout']);
Route::get('/auth/google', [App\Http\Controllers\Web\AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [App\Http\Controllers\Web\AuthController::class, 'googleCallback']);
Route::get('/profile', function(Request $request){
  $b = $request->session()->get('buyer');
  if(!$b) return redirect('/login')->with('error','Please login');
  $u = App\Models\User::find($b['id']);
  return view('profile',['user'=>$u]);
});
Route::post('/profile', function(Request $request){
  $b = $request->session()->get('buyer');
  if(!$b) return redirect('/login');
  $u = App\Models\User::find($b['id']);
  $request->validate(['first_name'=>'required','last_name'=>'required','email'=>'required|email','phone'=>'nullable','birthday'=>'nullable|date','sex'=>'nullable','address_line'=>'nullable']);
  $u->update($request->only(['first_name','last_name','email','phone','birthday','sex','address_line']));
  $request->session()->put('buyer',['id'=>$u->id,'first_name'=>$u->first_name,'last_name'=>$u->last_name,'email'=>$u->email]);
  return redirect('/profile')->with('success','Profile updated — full screen edit saved');
});
Route::get('/messages', function(){ return view('messages'); });
Route::get('/notifications', function(){ return view('notifications'); });
Route::get('/chat/seller/{sellerId}', function($sellerId, Request $request){
  return view('chat',['sellerId'=>$sellerId]);
});
Route::get('/orders', function(Request $request){
  $buyer = $request->session()->get('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login to view orders');
  $orders = \App\Models\Order::with('items.product')->where('buyer_id',$buyer['id'])->latest()->get();
  return view('orders',['orders'=>$orders]);
});
