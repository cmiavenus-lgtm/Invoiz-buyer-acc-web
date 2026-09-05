<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Product;
use App\Models\ProductVariant;

$colors = ['Black','White','Navy','Red','Blue','Green','Beige','Gray','Brown','Pastel'];
$cnt=0;
foreach(Product::all() as $p){
  if($p->variants()->count()>0) continue;
  // assign 3 colors based on product id
  $base = $p->id % count($colors);
  for($i=0;$i<3;$i++){
    $col = $colors[($base+$i)%count($colors)];
    ProductVariant::firstOrCreate(
      ['product_id'=>$p->id,'variant_type'=>'Color','variant_value'=>$col],
      ['price_adjustment'=>0,'stock'=>rand(20,80),'status'=>'active']
    );
  }
  $cnt++;
}
echo "Added colors to $cnt products\n";
echo "Total variants: ".ProductVariant::count()."\n";
