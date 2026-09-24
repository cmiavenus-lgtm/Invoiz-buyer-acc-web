<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
class HomeController extends Controller {
  public function index(Request $request){
    $q = Product::with('category')->where('status','active');
    if($request->filled('category')) $q->where('category_id', $request->integer('category'));
    if($request->filled('search')) $q->where('name','like','%'.$request->search.'%');
    $products = $q->orderByRaw("CASE WHEN category_id = (SELECT id FROM categories WHERE name = 'Appliances' LIMIT 1) THEN 0 ELSE 1 END, created_at DESC")->get();
    $categories = Category::where('active', 1)
      ->orderByRaw("CASE WHEN name = 'Appliances' THEN 0 ELSE 1 END, name")
      ->get();
    return view('home', compact('products','categories'));
  }
  public function show($id){
    $p = Product::with(['category','variants'])->findOrFail($id);
    return view('product', ['p'=>$p]);
  }
}
