<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\Hash;
$u = User::updateOrCreate(['email'=>'juan@test.com'],[
  'first_name'=>'Juan','last_name'=>'Buyer','middle_initial'=>null,'sex'=>'male',
  'password'=>Hash::make('password123'),'phone'=>'09171234567','birthday'=>'1995-06-15','age'=>31,
  'approval_status'=>'approved','role'=>'buyer','status'=>'active',
  'province'=>'Test','municipality'=>'Test','barangay'=>'Test','address_line'=>'123 Street'
]);
echo $u->email." ".$u->approval_status.PHP_EOL;
$c = Cart::firstOrCreate(['buyer_id'=>$u->id]);
echo "cart ".$c->id.PHP_EOL;
$u2 = User::where('email','cmiavenus@gmail.com')->first();
if($u2){ $u2->update(['approval_status'=>'approved']); echo "approved cmiavenus".PHP_EOL; }
