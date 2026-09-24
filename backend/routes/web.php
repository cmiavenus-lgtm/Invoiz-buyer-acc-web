<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;

Route::get('/', [HomeController::class, 'index']);
Route::get('/products', [HomeController::class, 'index']);
Route::get('/product/{id}', [HomeController::class, 'show']);

// --- Cart (DB-backed, login required) ---
Route::get('/cart/add/{id}', function($id){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login to add items to cart');
  $product = Product::find($id);
  if(!$product) return back()->with('error','Product not found');
  $currentQty = Cart::where('buyer_id', $buyer['id'])->sum('quantity');
  $existing = Cart::where('buyer_id', $buyer['id'])->where('product_id', $id)->first();
  $addQty = $existing ? 1 : 1;
  if($currentQty + $addQty > 200) return back()->with('error','Cart limit is 200 items');
  if($existing){ $existing->increment('quantity'); }
  else { Cart::create(['buyer_id'=>$buyer['id'],'product_id'=>$id,'quantity'=>1]); }
  return back()->with('success','Added to cart');
});

Route::get('/buy/{id}', function($id){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login to buy');
  $product = Product::find($id);
  if(!$product) return redirect('/')->with('error','Product not found');
  session(['checkout_single'=>$id]);
  return redirect('/checkout?single='.$id);
});

Route::get('/cart/dec/{id}', function($id){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login');
  $item = Cart::where('buyer_id', $buyer['id'])->where('product_id', $id)->first();
  if($item){
    if($item->quantity <= 1) $item->delete();
    else $item->decrement('quantity');
  }
  return redirect('/cart');
});

Route::get('/cart/remove/{id}', function($id){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login');
  Cart::where('buyer_id', $buyer['id'])->where('product_id', $id)->delete();
  return redirect('/cart');
});

Route::get('/cart', function(){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login to view cart');
  $cartItems = Cart::where('buyer_id', $buyer['id'])->get();
  $productIds = $cartItems->pluck('product_id')->toArray();
  $products = Product::whereIn('id', $productIds)->get();
  return view('cart', ['products'=>$products, 'cartItems'=>$cartItems]);
});

Route::get('/buy/seller/{sellerId}', function($sellerId){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login to buy');
  $cartItems = Cart::where('buyer_id',$buyer['id'])->get();
  $pIds = $cartItems->pluck('product_id')->toArray();
  $products = Product::whereIn('id',$pIds)->where('seller_id',$sellerId)->get();
  if($products->isEmpty()) return redirect('/cart')->with('error','No items from this seller in cart');

  // Get or create address
  $addr = \App\Models\Address::where('buyer_id',$buyer['id'])->first();
  if(!$addr){
    $addr = \App\Models\Address::create([
      'buyer_id'=>$buyer['id'],'recipient_name'=>$buyer['first_name'].' '.$buyer['last_name'],
      'phone'=>'09170000000','address_line'=>'123 Street','barangay'=>'Test',
      'city'=>'Test City','province'=>'Test Province','postal_code'=>'1000','is_default'=>1,
    ]);
  }

  $qtyMap = $cartItems->pluck('quantity','product_id')->toArray();
  $total = 0;
  foreach($products as $p){ $qty = $qtyMap[$p->id] ?? 1; $total += $p->price * $qty; }

  $order = \App\Models\Order::create([
    'buyer_id'=>$buyer['id'],'seller_id'=>$sellerId,'address_id'=>$addr->id,
    'total'=>$total,'total_amount'=>$total,'sub_total'=>$total,
    'discount_total'=>0,'shipping_fee'=>0,'tax_total'=>0,'commission_amount'=>0,
    'status'=>'pending','payment_status'=>'pending','delivery_status'=>'pending',
    'order_number'=>'ORD-'.strtoupper(uniqid()),
  ]);
  foreach($products as $p){
    $qty = $qtyMap[$p->id] ?? 1;
    \App\Models\OrderItem::create([
      'order_id'=>$order->id,'product_id'=>$p->id,'seller_id'=>$p->seller_id,
      'product_name'=>$p->name,'quantity'=>$qty,'price'=>$p->price,
      'unit_price'=>$p->price,'total_price'=>$p->price * $qty,
    ]);
    Cart::where('buyer_id',$buyer['id'])->where('product_id',$p->id)->delete();
  }
  \App\Models\Payment::create(['order_id'=>$order->id,'method'=>'cod','status'=>'pending','amount'=>$total]);
  session()->forget(['checkout_single','checkout_seller_id','checkout_all']);
  return redirect('/orders')->with('success','Order placed for Seller #'.$sellerId.' — Cash on Delivery — thank you!');
});

Route::get('/buy/all', function(){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login');
  $cartItems = Cart::where('buyer_id',$buyer['id'])->get();
  if($cartItems->isEmpty()) return redirect('/cart')->with('error','Cart is empty');
  session(['checkout_all'=>true]);
  session()->forget('checkout_single');
  session()->forget('checkout_seller_id');
  return redirect('/checkout?all=1');
});

Route::get('/buy/selected', function(Request $request){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login');
  $ids = $request->input('ids');
  if(!$ids) return redirect('/cart')->with('error','No items selected');
  $idArr = array_map('intval', explode(',', $ids));
  $cartItems = Cart::where('buyer_id',$buyer['id'])->whereIn('product_id',$idArr)->get();
  if($cartItems->isEmpty()) return redirect('/cart')->with('error','Selected items not found in cart');

  // Get or create address
  $addr = \App\Models\Address::where('buyer_id',$buyer['id'])->first();
  if(!$addr){
    $addr = \App\Models\Address::create([
      'buyer_id'=>$buyer['id'],'recipient_name'=>$buyer['first_name'].' '.$buyer['last_name'],
      'phone'=>'09170000000','address_line'=>'123 Street','barangay'=>'Test',
      'city'=>'Test City','province'=>'Test Province','postal_code'=>'1000','is_default'=>1,
    ]);
  }

  $pIds = $cartItems->pluck('product_id')->toArray();
  $products = Product::whereIn('id',$pIds)->get();
  $bySeller = $products->groupBy('seller_id');
  $qtyMap = $cartItems->pluck('quantity','product_id')->toArray();
  $ordersCreated = 0;

  foreach($bySeller as $sid => $sProds){
    $total = 0;
    $order = \App\Models\Order::create([
      'buyer_id'=>$buyer['id'],'seller_id'=>$sid,'address_id'=>$addr->id,
      'total'=>0,'total_amount'=>0,'sub_total'=>0,
      'discount_total'=>0,'shipping_fee'=>0,'tax_total'=>0,'commission_amount'=>0,
      'status'=>'pending','payment_status'=>'pending','delivery_status'=>'pending',
      'order_number'=>'ORD-'.strtoupper(uniqid()),
    ]);
    foreach($sProds as $p){
      $qty = $qtyMap[$p->id] ?? 1;
      $lineTotal = $p->price * $qty;
      $total += $lineTotal;
      \App\Models\OrderItem::create([
        'order_id'=>$order->id,'product_id'=>$p->id,'seller_id'=>$p->seller_id,
        'product_name'=>$p->name,'quantity'=>$qty,'price'=>$p->price,
        'unit_price'=>$p->price,'total_price'=>$lineTotal,
      ]);
      Cart::where('buyer_id',$buyer['id'])->where('product_id',$p->id)->delete();
    }
    $order->update(['total'=>$total,'total_amount'=>$total,'sub_total'=>$total]);
    \App\Models\Payment::create(['order_id'=>$order->id,'method'=>'cod','status'=>'pending','amount'=>$total]);
    $ordersCreated++;
  }

  $msg = $ordersCreated > 1
    ? "Placed {$ordersCreated} orders for selected items — thank you!"
    : 'Order placed for selected items — thank you!';
  return redirect('/orders')->with('success',$msg);
});

// --- Checkout (DB-backed, login required) ---
Route::get('/checkout', function(Request $request){
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login to checkout');

  // Single item buy
  if($request->filled('single')){
    $p = Product::find($request->integer('single'));
    if(!$p) return redirect('/cart')->with('error','Product not found');
    return view('checkout',['products'=>collect([$p]),'single'=>$p->id,'cartItems'=>collect(),'checkoutMode'=>'single']);
  }
  if(session()->has('checkout_single')){
    $p = Product::find(session('checkout_single'));
    if($p) return view('checkout',['products'=>collect([$p]),'single'=>$p->id,'cartItems'=>collect(),'checkoutMode'=>'single']);
  }

  // Buy from specific seller
  if($request->filled('seller') || session()->has('checkout_seller_id')){
    $sellerId = $request->input('seller') ?? session('checkout_seller_id');
    $cartItems = Cart::where('buyer_id',$buyer['id'])->get();
    $pIds = $cartItems->pluck('product_id')->toArray();
    $products = Product::whereIn('id',$pIds)->where('seller_id',$sellerId)->get();
    if($products->isEmpty()) return redirect('/cart')->with('error','No items from this seller');
    $sellerQtyMap = [];
    foreach($cartItems as $ci){ $p = $products->where('id',$ci->product_id)->first(); if($p) $sellerQtyMap[$ci->product_id] = $ci->quantity; }
    return view('checkout',['products'=>$products,'cartItems'=>$cartItems,'single'=>null,'checkoutMode'=>'seller','sellerId'=>$sellerId,'sellerQtyMap'=>$sellerQtyMap]);
  }

  // Buy all (creates separate orders per seller)
  if($request->filled('all') || session()->has('checkout_all')){
    $cartItems = Cart::where('buyer_id',$buyer['id'])->get();
    if($cartItems->isEmpty()) return redirect('/cart')->with('error','Cart is empty');
    $pIds = $cartItems->pluck('product_id')->toArray();
    $products = Product::whereIn('id',$pIds)->get();
    $bySeller = $products->groupBy('seller_id');
    $sellerGroups = [];
    foreach($bySeller as $sid => $sProds){
      $sTotal = 0;
      $sQtyMap = [];
      foreach($cartItems as $ci){
        if($ci->product_id && $sProds->where('id',$ci->product_id)->isNotEmpty()){
          $sQtyMap[$ci->product_id] = $ci->quantity;
          $p = $sProds->firstWhere('id',$ci->product_id);
          $sTotal += $p->price * $ci->quantity;
        }
      }
      $sellerGroups[$sid] = ['products'=>$sProds,'total'=>$sTotal,'qtyMap'=>$sQtyMap];
    }
    $grandTotal = collect($sellerGroups)->sum('total');
    return view('checkout',['products'=>$products,'cartItems'=>$cartItems,'single'=>null,'checkoutMode'=>'all','sellerGroups'=>$sellerGroups,'grandTotal'=>$grandTotal]);
  }

  return redirect('/cart');
});

Route::post('/checkout', function(Request $request){
  $request->validate(['payment_method'=>'required|in:cod']);
  $buyer = session('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login');

  // Get or create address
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

  $checkoutMode = $request->input('checkout_mode', session('checkout_single') ? 'single' : (session('checkout_all') ? 'all' : 'seller'));
  $ordersCreated = 0;

  try {
    if($checkoutMode === 'single'){
      // Single item buy
      $checkoutSingle = session('checkout_single') ?? $request->input('single');
      $product = Product::find((int)$checkoutSingle);
      if(!$product) return redirect('/cart')->with('error','Product not found');
      $total = $product->price;
      $order = \App\Models\Order::create([
        'buyer_id'=>$buyer['id'],'seller_id'=>$product->seller_id,'address_id'=>$addr->id,
        'total'=>$total,'total_amount'=>$total,'sub_total'=>$total,
        'discount_total'=>0,'shipping_fee'=>0,'tax_total'=>0,'commission_amount'=>0,
        'status'=>'pending','payment_status'=>'pending','delivery_status'=>'pending',
        'order_number'=>'ORD-'.strtoupper(uniqid()),
      ]);
      \App\Models\OrderItem::create([
        'order_id'=>$order->id,'product_id'=>$product->id,'seller_id'=>$product->seller_id,
        'product_name'=>$product->name,'quantity'=>1,'price'=>$product->price,
        'unit_price'=>$product->price,'total_price'=>$product->price,
      ]);
      \App\Models\Payment::create(['order_id'=>$order->id,'method'=>'cod','status'=>'pending','amount'=>$total]);
      Cart::where('buyer_id',$buyer['id'])->where('product_id',(int)$checkoutSingle)->delete();
      $ordersCreated = 1;

    } elseif($checkoutMode === 'seller'){
      // Buy from one seller
      $sellerId = session('checkout_seller_id') ?? $request->input('seller_id');
      $cartItems = Cart::where('buyer_id',$buyer['id'])->get();
      $pIds = $cartItems->pluck('product_id')->toArray();
      $products = Product::whereIn('id',$pIds)->where('seller_id',$sellerId)->get();
      if($products->isEmpty()) return redirect('/cart')->with('error','No items from this seller');
      $total = 0;
      $qtyMap = $cartItems->pluck('quantity','product_id')->toArray();
      foreach($products as $p){ $qty = $qtyMap[$p->id] ?? 1; $total += $p->price * $qty; }
      $order = \App\Models\Order::create([
        'buyer_id'=>$buyer['id'],'seller_id'=>$sellerId,'address_id'=>$addr->id,
        'total'=>$total,'total_amount'=>$total,'sub_total'=>$total,
        'discount_total'=>0,'shipping_fee'=>0,'tax_total'=>0,'commission_amount'=>0,
        'status'=>'pending','payment_status'=>'pending','delivery_status'=>'pending',
        'order_number'=>'ORD-'.strtoupper(uniqid()),
      ]);
      foreach($products as $p){
        $qty = $qtyMap[$p->id] ?? 1;
        \App\Models\OrderItem::create([
          'order_id'=>$order->id,'product_id'=>$p->id,'seller_id'=>$p->seller_id,
          'product_name'=>$p->name,'quantity'=>$qty,'price'=>$p->price,
          'unit_price'=>$p->price,'total_price'=>$p->price * $qty,
        ]);
        Cart::where('buyer_id',$buyer['id'])->where('product_id',$p->id)->delete();
      }
      \App\Models\Payment::create(['order_id'=>$order->id,'method'=>'cod','status'=>'pending','amount'=>$total]);
      $ordersCreated = 1;

    } else {
      // Buy all — separate order per seller
      $cartItems = Cart::where('buyer_id',$buyer['id'])->get();
      $pIds = $cartItems->pluck('product_id')->toArray();
      $products = Product::whereIn('id',$pIds)->get();
      $bySeller = $products->groupBy('seller_id');
      $qtyMap = $cartItems->pluck('quantity','product_id')->toArray();

      foreach($bySeller as $sid => $sProds){
        $total = 0;
        $order = \App\Models\Order::create([
          'buyer_id'=>$buyer['id'],'seller_id'=>$sid,'address_id'=>$addr->id,
          'total'=>0,'total_amount'=>0,'sub_total'=>0,
          'discount_total'=>0,'shipping_fee'=>0,'tax_total'=>0,'commission_amount'=>0,
          'status'=>'pending','payment_status'=>'pending','delivery_status'=>'pending',
          'order_number'=>'ORD-'.strtoupper(uniqid()),
        ]);
        foreach($sProds as $p){
          $qty = $qtyMap[$p->id] ?? 1;
          $lineTotal = $p->price * $qty;
          $total += $lineTotal;
          \App\Models\OrderItem::create([
            'order_id'=>$order->id,'product_id'=>$p->id,'seller_id'=>$p->seller_id,
            'product_name'=>$p->name,'quantity'=>$qty,'price'=>$p->price,
            'unit_price'=>$p->price,'total_price'=>$lineTotal,
          ]);
          Cart::where('buyer_id',$buyer['id'])->where('product_id',$p->id)->delete();
        }
        $order->update(['total'=>$total,'total_amount'=>$total,'sub_total'=>$total]);
        \App\Models\Payment::create(['order_id'=>$order->id,'method'=>'cod','status'=>'pending','amount'=>$total]);
        $ordersCreated++;
      }
    }
  } catch(\Throwable $e){
    \Log::error('Checkout failed: '.$e->getMessage());
    return back()->with('error','Checkout failed: '.$e->getMessage());
  }

  session()->forget(['checkout_single','checkout_seller_id','checkout_all']);
  $msg = $ordersCreated > 1
    ? "Placed {$ordersCreated} orders — Cash on Delivery — thank you!"
    : 'Order placed — Cash on Delivery — thank you!';
  return redirect('/orders')->with('success',$msg);
});

// --- Auth ---
Route::get('/login', [App\Http\Controllers\Web\AuthController::class, 'showLogin']);
Route::post('/login', [App\Http\Controllers\Web\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\Web\AuthController::class, 'showRegister']);
Route::post('/register', [App\Http\Controllers\Web\AuthController::class, 'register']);
Route::get('/logout', [App\Http\Controllers\Web\AuthController::class, 'logout']);
Route::get('/verify', [App\Http\Controllers\Web\AuthController::class, 'showVerify']);
Route::post('/verify', [App\Http\Controllers\Web\AuthController::class, 'verifyCode']);
Route::post('/verify/resend', [App\Http\Controllers\Web\AuthController::class, 'resend']);
Route::get('/auth/google', [App\Http\Controllers\Web\AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [App\Http\Controllers\Web\AuthController::class, 'googleCallback']);

// --- Profile ---
Route::get('/profile', function(Request $request){
  $b = $request->session()->get('buyer');
  if(!$b) return redirect('/login')->with('error','Please login');
  $u = \App\Models\User::find($b['id']);
  return view('profile',['user'=>$u]);
});
Route::post('/profile', function(Request $request){
  $b = $request->session()->get('buyer');
  if(!$b) return redirect('/login');
  $u = \App\Models\User::find($b['id']);
  $request->validate(['first_name'=>'required','last_name'=>'required','email'=>'required|email','phone'=>'nullable']);
  $fullName = trim($request->first_name.' '.$request->last_name);
  $u->update([
    'name'=>$fullName,
    'first_name'=>$request->first_name,
    'last_name'=>$request->last_name,
    'email'=>$request->email,
    'phone'=>$request->phone ?? '',
  ]);
  $request->session()->put('buyer',['id'=>$u->id,'first_name'=>$request->first_name,'last_name'=>$request->last_name,'email'=>$u->email]);
  return redirect('/profile')->with('success','Profile updated');
});

// --- My Orders (DB-backed, account-specific) ---
Route::get('/orders', function(Request $request){
  $buyer = $request->session()->get('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login to view orders');
  $tab = $request->input('tab', 'all');
  $q = \App\Models\Order::with('items.product')->where('buyer_id',$buyer['id']);
  if($tab === 'pending') $q->where('status','pending');
  elseif($tab === 'out_for_delivery') $q->whereIn('status',['confirmed','shipped']);
  elseif($tab === 'delivered') $q->where('status','delivered');
  $orders = $q->latest()->get();
  return view('orders',['orders'=>$orders,'activeTab'=>$tab]);
});

Route::get('/messages', function(Request $request){
  $buyer = $request->session()->get('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login');
  $userId = $buyer['id'];

  // Get sellers the buyer has ordered from
  $orderSellerIds = \App\Models\Order::where('buyer_id',$userId)->pluck('seller_id')->unique()->toArray();

  // Get sellers the buyer has chatted with
  $chatSellerIds = \App\Models\Message::where('sender_id',$userId)->orWhere('receiver_id',$userId)
    ->selectRaw('CASE WHEN sender_id=? THEN receiver_id ELSE sender_id END as other_id',[$userId])
    ->distinct()->pluck('other_id')->toArray();

  // Combine: only sellers who are in orders AND have messages
  $sellerIds = array_unique(array_intersect($orderSellerIds, $chatSellerIds));

  $conversations = collect();
  foreach($sellerIds as $sellerId){
    $last = \App\Models\Message::where(function($q) use($userId,$sellerId){
      $q->where(['sender_id'=>$userId,'receiver_id'=>$sellerId]);
    })->orWhere(function($q) use($userId,$sellerId){
      $q->where(['sender_id'=>$sellerId,'receiver_id'=>$userId]);
    })->latest()->first();
    if($last){
      $unread = \App\Models\Message::where('sender_id',$sellerId)->where('receiver_id',$userId)->where('is_read',0)->count();
      $conversations->push(['other_id'=>$sellerId,'last'=>$last,'unread'=>$unread]);
    }
  }
  $conversations = $conversations->sortByDesc(function($c){ return $c['last']->created_at; })->values();
  return view('messages',['conversations'=>$conversations]);
});

Route::get('/chat/seller/{sellerId}', function(Request $request, $sellerId){
  $buyer = $request->session()->get('buyer');
  if(!$buyer) return redirect('/login')->with('error','Please login');
  // Mark messages from seller as read
  \App\Models\Message::where('sender_id',$sellerId)->where('receiver_id',$buyer['id'])->where('is_read',0)->update(['is_read'=>1]);
  $messages = \App\Models\Message::where(function($q) use($buyer,$sellerId){
    $q->where(['sender_id'=>$buyer['id'],'receiver_id'=>$sellerId]);
  })->orWhere(function($q) use($buyer,$sellerId){
    $q->where(['sender_id'=>$sellerId,'receiver_id'=>$buyer['id']]);
  })->orderBy('created_at','asc')->get();
  return view('chat',['sellerId'=>$sellerId,'messages'=>$messages]);
});

Route::post('/chat/send/{sellerId}', function(Request $request, $sellerId){
  $buyer = $request->session()->get('buyer');
  if(!$buyer) return response()->json(['error'=>'Login required'],401);
  $request->validate(['body'=>'required|string|max:2000']);
  \App\Models\Message::create([
    'sender_id'=>$buyer['id'],
    'receiver_id'=>$sellerId,
    'body'=>$request->input('body'),
    'is_read'=>0,
  ]);
  return response()->json(['ok'=>true]);
});

Route::get('/chat/fetch/{sellerId}', function(Request $request, $sellerId){
  $buyer = $request->session()->get('buyer');
  if(!$buyer) return response()->json(['error'=>'Login required'],401);
  $after = $request->input('after');
  $q = \App\Models\Message::where(function($q) use($buyer,$sellerId){
    $q->where(['sender_id'=>$buyer['id'],'receiver_id'=>$sellerId]);
  })->orWhere(function($q) use($buyer,$sellerId){
    $q->where(['sender_id'=>$sellerId,'receiver_id'=>$buyer['id']]);
  });
  if($after) $q->where('created_at','>',$after);
  $messages = $q->orderBy('created_at','asc')->get()->map(function($m) use($buyer){
    return [
      'id'=>$m->id,
      'body'=>$m->body,
      'is_me'=>$m->sender_id==$buyer['id'],
      'created_at'=>$m->created_at->toDateTimeString(),
    ];
  });
  return response()->json($messages);
});

Route::get('/notifications', function(){ return view('notifications'); });
