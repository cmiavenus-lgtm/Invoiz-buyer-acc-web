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

  private function nameParts($name){
    $parts = preg_split('/\s+/', trim($name), 2);
    return ['first_name' => $parts[0] ?? '', 'last_name' => $parts[1] ?? ''];
  }

  private function setBuyerSession($r, $u){
    $np = $this->nameParts($u->name ?? $u->first_name.' '.$u->last_name);
    $r->session()->put('buyer',[
      'id'=>$u->id,
      'first_name'=>$np['first_name'],
      'last_name'=>$np['last_name'],
      'email'=>$u->email,
    ]);
  }

  public function login(Request $r){
    $r->validate(['email'=>'required|email','password'=>'required']);
    $u = User::where('email',$r->email)->first();
    if(!$u || !Hash::check($r->password, $u->password)) return back()->withErrors(['email'=>'Invalid email or password'])->withInput();
    if(!empty($u->account_status) && $u->account_status!=='active') return back()->withErrors(['email'=>'Your account is not active.'])->withInput();
    if(!$u->email_verified_at){
      $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
      $u->update(['otp'=>$otp,'otp_expires_at'=>now()->addMinutes(5)]);
      try{ \App\Services\PHPMailerService::sendOTP($u->email, $otp); } catch(\Throwable $e){}
      $r->session()->put('pending_login_id',$u->id);
      return redirect('/verify?email='.urlencode($u->email))->with('success','OTP sent to '.$u->email.' — enter code to complete login');
    }
    Auth::login($u);
    $this->setBuyerSession($r, $u);
    $np = $this->nameParts($u->name ?? '');
    return redirect('/')->with('success','Welcome back, '.$np['first_name'].'!');
  }

  public function register(Request $r){
    $r->validate([
      'first_name'=>'required|string|max:100',
      'last_name'=>'required|string|max:100',
      'email'=>'required|email|max:150|unique:users,email',
      'password'=>'required|string|min:8|confirmed',
      'phone'=>'nullable|string|max:30',
    ]);
    $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $fullName = trim($r->first_name.' '.$r->last_name);
    $u = User::create([
      'name'=>$fullName,
      'first_name'=>$r->first_name,
      'last_name'=>$r->last_name,
      'email'=>$r->email,
      'password'=>Hash::make($r->password),
      'phone'=>$r->phone ?? '',
      'role'=>'buyer',
      'account_status'=>'active',
      'otp'=>$otp,
      'otp_expires_at'=>now()->addMinutes(5),
      'email_verified_at'=>null,
    ]);
    Cart::create(['buyer_id'=>$u->id, 'product_id'=>0, 'quantity'=>0]);
    Cart::where('buyer_id',$u->id)->where('product_id',0)->delete();
    try{ \App\Services\PHPMailerService::sendOTP($u->email, $otp); } catch(\Throwable $e){}
    return redirect('/verify?email='.urlencode($u->email))->with('success','We sent a 6-digit code to '.$u->email);
  }

  public function showVerify(Request $r){
    $email = $r->query('email') ?? $r->session()->get('verify_email', 'your email');
    return view('auth.verify',['email'=>$email]);
  }

  public function resend(Request $r){
    $email = $r->query('email') ?? $r->input('email');
    $u = User::where('email',$email)->first();
    if(!$u) return back()->withErrors(['email'=>'Email not found']);
    $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $u->update(['otp'=>$otp,'otp_expires_at'=>now()->addMinutes(5)]);
    try{ \App\Services\PHPMailerService::sendOTP($u->email, $otp); } catch(\Throwable $e){ return back()->with('error','Failed to send OTP: '.$e->getMessage()); }
    return back()->with('success','New code sent to '.$email)->with('resent', true);
  }

  public function verifyCode(Request $r){
    $r->validate(['code'=>'required|digits:6','email'=>'required']);
    $u = User::where('email',$r->email)->first();
    if(!$u) return back()->withErrors(['code'=>'Email not found']);
    if(!$u->otp || $u->otp !== $r->code) return back()->withErrors(['code'=>'Wrong OTP'])->withInput();
    if($u->otp_expires_at && $u->otp_expires_at->isPast()) return back()->withErrors(['code'=>'OTP expired — click Resend OTP'])->withInput();
    $u->update(['email_verified_at'=>now(),'otp'=>null,'otp_expires_at'=>null]);
    if($r->session()->has('pending_login_id')){
      $uid = $r->session()->pull('pending_login_id');
      $uu = User::find($uid);
      if($uu){
        Auth::login($uu);
        $this->setBuyerSession($r, $uu);
        $np = $this->nameParts($uu->name ?? '');
        return redirect('/')->with('success','Verified — welcome, '.$np['first_name'].'!');
      }
    }
    return redirect('/login')->with('success','Email verified — you can now log in');
  }

  public function googleRedirect(Request $r){
    $email = $r->query('email') ?? 'your Gmail';
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $r->session()->put('auth_code', $code);
    $r->session()->put('verify_email', $email);
    try{ \Illuminate\Support\Facades\Mail::raw("Your Invoiz Gmail authentication code is: $code", function($m) use ($email){ $m->to($email)->subject('Invoiz — Gmail authentication code'); }); } catch(\Throwable $e){}
    return view('auth.verify',['email'=>$email]);
  }
  public function googleCallback(){ return redirect('/login'); }

  public function logout(Request $r){
    Auth::logout();
    $r->session()->forget('buyer');
    $r->session()->forget('checkout_single');
    $r->session()->invalidate();
    $r->session()->regenerateToken();
    return redirect('/')->with('success','Logged out successfully');
  }
}
