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
  return redirect('/')->with('success','Added to basket');
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
Route::get('/orders', function(){ return view('welcome'); });
