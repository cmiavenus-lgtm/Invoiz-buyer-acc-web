<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {
  public function showLogin(){ return view('auth.login'); }
  public function showRegister(){ return view('auth.register'); }
  public function login(Request $r){
    $r->validate(['email'=>'required|email','password'=>'required']);
    $u = User::where('email',$r->email)->first();
    if(!$u || !Hash::check($r->password, $u->password)) return back()->withErrors(['email'=>'Invalid email or password'])->withInput();
    if($u->approval_status==='pending') return back()->withErrors(['email'=>'Your account is still pending approval.'])->withInput();
    if($u->approval_status==='rejected') return back()->withErrors(['email'=>'Your registration was rejected.'])->withInput();
    if($u->status!=='active') return back()->withErrors(['email'=>'Your account is not active.'])->withInput();
    Auth::login($u);
    $r->session()->put('buyer',['id'=>$u->id,'first_name'=>$u->first_name,'last_name'=>$u->last_name,'email'=>$u->email]);
    return redirect('/')->with('success','Welcome back, '.$u->first_name.'!');
  }
  public function register(Request $r){
    $r->validate([
      'first_name'=>'required|string|max:100',
      'last_name'=>'required|string|max:100',
      'email'=>'required|email|max:150|unique:users,email',
      'password'=>'required|string|min:8|confirmed',
      'phone'=>'required|string|max:30',
      'birthday'=>'required|date|before:today',
      'sex'=>'required|in:male,female,other',
      'address_line'=>'required|string|max:255',
    ]);
    $age = \Carbon\Carbon::parse($r->birthday)->age;
    $u = User::create([
      'first_name'=>$r->first_name,
      'last_name'=>$r->last_name,
      'email'=>$r->email,
      'password'=>Hash::make($r->password),
      'phone'=>$r->phone,
      'sex'=>$r->sex,
      'birthday'=>$r->birthday,
      'age'=>$age,
      'approval_status'=>'approved',
      'role'=>'buyer',
      'status'=>'active',
      'province'=>'Test','municipality'=>'Test','barangay'=>'Test','address_line'=>$r->address_line,
    ]);
    Cart::firstOrCreate(['buyer_id'=>$u->id]);
    Auth::login($u);
    $r->session()->put('buyer',['id'=>$u->id,'first_name'=>$u->first_name,'last_name'=>$u->last_name,'email'=>$u->email]);
    return redirect('/')->with('success','Registered successfully — welcome, '.$u->first_name.'!');
  }
  public function googleRedirect(){ return redirect('/login')->with('error','Continue with Google needs Google OAuth keys. Set GOOGLE_CLIENT_ID/SECRET in .env and install laravel/socialite.'); }
  public function googleCallback(){ return redirect('/login'); }
  public function logout(Request $r){
    Auth::logout();
    $r->session()->forget('buyer');
    $r->session()->invalidate(); $r->session()->regenerateToken();
    return redirect('/')->with('success','Logged out.');
  }
}
