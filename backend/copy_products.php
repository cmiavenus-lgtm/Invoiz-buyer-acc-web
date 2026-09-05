<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

// Ensure categories from original invoiz exist
$origCategories = [
  'Baby Clothes & Accessories','Toys & Games','Educational Materials','Strollers & Gear','Nursery Furniture','Safety & Health','Pet Supplies','Electronics & Gadgets',"Women's Apparel","Men's Apparel",'Home & Garden','School Supplies','Makeup','Dresses','Furniture','Toys','Sports Equipment','Jewelry','Gadgets','Appliances','Tools',
  // Also keep existing Invoiz-main categories
  'Fashion','Electronics','Home & Living','Beauty & Health','Sports & Outdoors','Toys & Hobbies','Groceries','Books'
];
foreach ($origCategories as $name) {
  Category::firstOrCreate(['name'=>$name], ['description'=>$name.' category','status'=>'active']);
}

// Seller - use existing demo seller
$seller = User::where('email','seller@invoiz.test')->first();
if(!$seller){ echo "No seller found\n"; exit(1); }
$sellerId = $seller->id;
echo "Using seller $sellerId\n";

$catId = fn($name) => Category::where('name',$name)->value('id');

// Full product list copied from C:\Users\admin\OneDrive\Desktop\invoiz\backend\database\seeders\ProductSeeder.php
$list = [
  // Baby Clothes
  ['cat'=>'Baby Clothes & Accessories','sku'=>'BT-001','name'=>'Baby Onesie Set','brand'=>'TinyTots','price'=>24.99,'desc'=>'Soft cotton onesie set for newborns'],
  ['cat'=>'Baby Clothes & Accessories','sku'=>'BA-001','name'=>'Diaper Bag Backpack','brand'=>'MomEssentials','price'=>44.99,'desc'=>'Multi-function diaper bag backpack'],
  ['cat'=>'Toys & Games','sku'=>'TG-001','name'=>'Kids Wooden Blocks','brand'=>'PlayLearn','price'=>19.99,'desc'=>'Educational wooden building blocks'],
  ['cat'=>'Toys & Games','sku'=>'TG-002','name'=>'Kids Puzzle Set','brand'=>'PlayLearn','price'=>15.99,'desc'=>'Educational puzzle set for ages 3-6'],
  ['cat'=>'Strollers & Gear','sku'=>'SG-001','name'=>'Stroller Compact Fold','brand'=>'BabyRide','price'=>149.99,'desc'=>'Lightweight compact fold stroller'],
  ['cat'=>'Strollers & Gear','sku'=>'SG-002','name'=>'Baby Car Seat','brand'=>'SafeRide','price'=>199.99,'desc'=>'Rear-facing baby car seat with safety harness'],
  ['cat'=>'Nursery Furniture','sku'=>'NF-001','name'=>'Nursery Crib Adjustable','brand'=>'SleepWell','price'=>299.99,'desc'=>'Adjustable height nursery crib'],
  ['cat'=>'Safety & Health','sku'=>'SH-001','name'=>'Baby Safety Gate','brand'=>'SafeKids','price'=>34.99,'desc'=>'Pressure-mounted safety gate'],
  ['cat'=>'Safety & Health','sku'=>'SH-002','name'=>'Baby Bath Tub','brand'=>'CleanBaby','price'=>27.99,'desc'=>'Foldable baby bath tub with thermometer'],
  ['cat'=>'Educational Materials','sku'=>'EM-001','name'=>'Kids Art Set','brand'=>'CreativeKids','price'=>29.99,'desc'=>'Complete art supplies set for kids'],
  // School Supplies
  ['cat'=>'School Supplies','sku'=>'SS-PEN-001','name'=>'Ballpoint Pen Pack 50pcs','brand'=>'WriteWell','price'=>149.00,'desc'=>'Smooth-writing ballpoint pens, box of 50'],
  ['cat'=>'School Supplies','sku'=>'SS-NTB-002','name'=>'Spiral Notebook Set of 5','brand'=>'PageOne','price'=>199.00,'desc'=>'College-ruled spiral notebooks, assorted colors'],
  ['cat'=>'School Supplies','sku'=>'SS-BAG-003','name'=>'Student Backpack Waterproof','brand'=>'CarryAll','price'=>549.00,'desc'=>'Water-resistant backpack with laptop sleeve'],
  ['cat'=>'School Supplies','sku'=>'SS-GEO-004','name'=>'Geometry Tool Set','brand'=>'MathPro','price'=>129.00,'desc'=>'Complete geometry set in metal tin case'],
  // Makeup
  ['cat'=>'Makeup','sku'=>'MK-LIP-001','name'=>'Velvet Matte Lipstick','brand'=>'GlowGirl','price'=>299.00,'desc'=>'Long-wear matte lipstick, transfer-proof'],
  ['cat'=>'Makeup','sku'=>'MK-EYE-002','name'=>'Eyeshadow Palette 12 Shades','brand'=>'GlowGirl','price'=>499.00,'desc'=>'Highly pigmented nude and bold shades'],
  ['cat'=>'Makeup','sku'=>'MK-FND-003','name'=>'Liquid Foundation SPF30','brand'=>'PureBlend','price'=>449.00,'desc'=>'Medium-coverage liquid foundation with SPF'],
  ['cat'=>'Makeup','sku'=>'MK-BRS-004','name'=>'Makeup Brush Set 12pcs','brand'=>'BeautyPro','price'=>399.00,'desc'=>'Synthetic bristle brush set with pouch'],
  // Dresses
  ['cat'=>'Dresses','sku'=>'DR-FLR-001','name'=>'Floral Summer Midi Dress','brand'=>'BelleAmie','price'=>899.00,'desc'=>'Flowy floral midi dress, breathable fabric'],
  ['cat'=>'Dresses','sku'=>'DR-OFC-002','name'=>'Office Pencil Dress','brand'=>'BelleAmie','price'=>999.00,'desc'=>'Structured pencil dress for work'],
  ['cat'=>'Dresses','sku'=>'DR-EVE-003','name'=>'Sequined Evening Gown','brand'=>'NightVelvet','price'=>1899.00,'desc'=>'Elegant sequined gown for special occasions'],
  ['cat'=>'Dresses','sku'=>'DR-SUN-004','name'=>'Casual Linen Sundress','brand'=>'BreezeWear','price'=>749.00,'desc'=>'Light linen sundress for everyday wear'],
  // Furniture
  ['cat'=>'Furniture','sku'=>'FR-DSK-001','name'=>'Wooden Study Desk','brand'=>'OakCraft','price'=>4499.00,'desc'=>'Solid wood study desk with drawers'],
  ['cat'=>'Furniture','sku'=>'FR-CHR-002','name'=>'Ergonomic Office Chair','brand'=>'SitWell','price'=>3899.00,'desc'=>'Adjustable ergonomic chair with lumbar support'],
  ['cat'=>'Furniture','sku'=>'FR-SOF-003','name'=>'3-Seater Fabric Sofa','brand'=>'CozyHome','price'=>12999.00,'desc'=>'Comfortable fabric sofa, sturdy frame'],
  ['cat'=>'Furniture','sku'=>'FR-BOK-004','name'=>'Bookshelf 5-Tier','brand'=>'OakCraft','price'=>2799.00,'desc'=>'Space-saving 5-tier open bookshelf'],
  // Toys
  ['cat'=>'Toys','sku'=>'TY-BLK-001','name'=>'Building Blocks 100pcs','brand'=>'BrickFun','price'=>549.00,'desc'=>'Creative building blocks, compatible set'],
  ['cat'=>'Toys','sku'=>'TY-RCC-002','name'=>'Remote Control Car','brand'=>'SpeedKid','price'=>899.00,'desc'=>'High-speed RC car with rechargeable battery'],
  ['cat'=>'Toys','sku'=>'TY-TED-003','name'=>'Plush Teddy Bear Large','brand'=>'CuddleCo','price'=>449.00,'desc'=>'Super soft huggable teddy bear, 60cm'],
  ['cat'=>'Toys','sku'=>'TY-BRD-004','name'=>'Family Board Game Edition','brand'=>'FunTable','price'=>699.00,'desc'=>'Classic strategy board game for the family'],
  // Sports Equipment
  ['cat'=>'Sports Equipment','sku'=>'SP-YOG-001','name'=>'Yoga Mat 6mm','brand'=>'FlexFit','price'=>599.00,'desc'=>'Non-slip TPE yoga mat with carry strap'],
  ['cat'=>'Sports Equipment','sku'=>'SP-DMB-002','name'=>'Adjustable Dumbbell Set 20kg','brand'=>'IronPeak','price'=>2499.00,'desc'=>'Space-saving adjustable dumbbell pair'],
  ['cat'=>'Sports Equipment','sku'=>'SP-BKB-003','name'=>'Basketball Official Size','brand'=>'HoopStar','price'=>899.00,'desc'=>'Indoor/outdoor composite leather basketball'],
  ['cat'=>'Sports Equipment','sku'=>'SP-RES-004','name'=>'Resistance Bands Set','brand'=>'FlexFit','price'=>349.00,'desc'=>'5-level resistance bands with door anchor'],
  // Jewelry
  ['cat'=>'Jewelry','sku'=>'JW-NCK-001','name'=>'Gold-Plated Necklace Set','brand'=>'LuxeAura','price'=>1299.00,'desc'=>'Elegant gold-plated necklace and earrings set'],
  ['cat'=>'Jewelry','sku'=>'JW-EAR-002','name'=>'Freshwater Pearl Earrings','brand'=>'LuxeAura','price'=>899.00,'desc'=>'Genuine freshwater pearl drop earrings'],
  ['cat'=>'Jewelry','sku'=>'JW-WAT-003','name'=>'Stainless Steel Watch','brand'=>'TimeLux','price'=>2199.00,'desc'=>'Water-resistant classic steel watch'],
  ['cat'=>'Jewelry','sku'=>'JW-BRC-004','name'=>'Silver Charm Bracelet','brand'=>'LuxeAura','price'=>749.00,'desc'=>'925 silver bracelet with charm accents'],
  // Gadgets
  ['cat'=>'Gadgets','sku'=>'GD-EAR-001','name'=>'Wireless Earbuds Pro','brand'=>'SonicWave','price'=>1499.00,'desc'=>'True wireless earbuds with noise reduction'],
  ['cat'=>'Gadgets','sku'=>'GD-PWB-002','name'=>'Power Bank 20000mAh','brand'=>'VoltMax','price'=>999.00,'desc'=>'Fast-charging dual-USB power bank'],
  ['cat'=>'Gadgets','sku'=>'GD-SPK-003','name'=>'Mini Bluetooth Speaker','brand'=>'SonicWave','price'=>799.00,'desc'=>'Portable speaker with deep bass, IPX6'],
  ['cat'=>'Gadgets','sku'=>'GD-WCH-004','name'=>'Smart Watch Fitness Tracker','brand'=>'PulseTech','price'=>1899.00,'desc'=>'Heart-rate, sleep and step tracking watch'],
  // Appliances
  ['cat'=>'Appliances','sku'=>'AP-RIC-001','name'=>'Rice Cooker 1.8L','brand'=>'KusinaPro','price'=>1299.00,'desc'=>'Non-stick rice cooker with keep-warm function'],
  ['cat'=>'Appliances','sku'=>'AP-FAN-002','name'=>'Stand Fan 16 inch','brand'=>'BreezeAir','price'=>1099.00,'desc'=>'3-speed adjustable stand fan with timer'],
  ['cat'=>'Appliances','sku'=>'AP-MIC-003','name'=>'Microwave Oven 20L','brand'=>'HeatWave','price'=>3499.00,'desc'=>'Compact microwave with 5 power levels'],
  ['cat'=>'Appliances','sku'=>'AP-BLN-004','name'=>'3-in-1 Blender','brand'=>'KusinaPro','price'=>1599.00,'desc'=>'Blender, grinder and chopper in one'],
  // Tools
  ['cat'=>'Tools','sku'=>'TL-DRL-001','name'=>'Cordless Drill 12V','brand'=>'ToolForce','price'=>1999.00,'desc'=>'Compact cordless drill with 2 batteries'],
  ['cat'=>'Tools','sku'=>'TL-SCR-002','name'=>'Screwdriver Set 32pcs','brand'=>'ToolForce','price'=>349.00,'desc'=>'Precision screwdriver set with magnetic tips'],
  ['cat'=>'Tools','sku'=>'TL-BOX-003','name'=>'Tool Box Set 108pcs','brand'=>'BuildRight','price'=>2499.00,'desc'=>'Complete home repair tool kit with case'],
  ['cat'=>'Tools','sku'=>'TL-WRN-004','name'=>'Adjustable Wrench Set','brand'=>'BuildRight','price'=>399.00,'desc'=>'3-piece chrome vanadium wrench set'],
  // Additional 100+ products — full copy from invoiz ProductSeeder
  ['cat'=>'Baby Clothes & Accessories','sku'=>'BT-101','name'=>'Baby Romper Pack 3pcs','brand'=>'TinyTots','price'=>299.00,'desc'=>'Soft cotton baby romper pack'],
  ['cat'=>'Baby Clothes & Accessories','sku'=>'BT-102','name'=>'Baby Socks Set 6pcs','brand'=>'TinyTots','price'=>149.00,'desc'=>'Cute animal baby socks set'],
  ['cat'=>'Baby Clothes & Accessories','sku'=>'BT-103','name'=>'Baby Hat & Mittens Set','brand'=>'CozyBaby','price'=>199.00,'desc'=>'Warm hat and mittens for newborns'],
  ['cat'=>'Baby Clothes & Accessories','sku'=>'BT-104','name'=>'Baby Bib Set 5pcs','brand'=>'CleanBaby','price'=>179.00,'desc'=>'Waterproof baby bibs with pocket'],
  ['cat'=>'Baby Clothes & Accessories','sku'=>'BT-105','name'=>'Baby Muslin Blanket','brand'=>'CozyBaby','price'=>249.00,'desc'=>'Breathable muslin swaddle blanket'],
  ['cat'=>'Toys & Games','sku'=>'TG-101','name'=>'Wooden Stacking Rings','brand'=>'PlayLearn','price'=>249.00,'desc'=>'Colorful wooden stacking rings toy'],
  ['cat'=>'Toys & Games','sku'=>'TG-102','name'=>'Magnetic Building Tiles 42pcs','brand'=>'BrickFun','price'=>599.00,'desc'=>'Magnetic tiles for creative building'],
  ['cat'=>'Toys & Games','sku'=>'TG-103','name'=>'Plush Dinosaur Toy','brand'=>'CuddleCo','price'=>349.00,'desc'=>'Soft plush dinosaur for kids'],
  ['cat'=>'Toys & Games','sku'=>'TG-104','name'=>'Kids Doctor Play Set','brand'=>'PlayLearn','price'=>399.00,'desc'=>'Doctor kit with stethoscope and tools'],
  ['cat'=>'Toys & Games','sku'=>'TG-105','name'=>'Jumbo Coloring Book','brand'=>'CreativeKids','price'=>179.00,'desc'=>'Giant coloring book with 100 pages'],
  ['cat'=>'Strollers & Gear','sku'=>'SG-101','name'=>'Umbrella Stroller Lightweight','brand'=>'BabyRide','price'=>2999.00,'desc'=>'Compact umbrella stroller for travel'],
  ['cat'=>'Strollers & Gear','sku'=>'SG-102','name'=>'Baby Carrier Wrap','brand'=>'CozyBaby','price'=>799.00,'desc'=>'Ergonomic baby carrier wrap'],
  ['cat'=>'Strollers & Gear','sku'=>'SG-103','name'=>'Infant Car Mirror','brand'=>'SafeRide','price'=>349.00,'desc'=>'Wide-angle car mirror for baby'],
  ['cat'=>'Strollers & Gear','sku'=>'SG-104','name'=>'Stroller Organizer Bag','brand'=>'MomEssentials','price'=>449.00,'desc'=>'Organizer caddy for stroller handle'],
  ['cat'=>'Strollers & Gear','sku'=>'SG-105','name'=>'Baby Swaddle Wrap Set','brand'=>'TinyTots','price'=>299.00,'desc'=>'Adjustable swaddle wrap set 3pcs'],
  ['cat'=>'Nursery Furniture','sku'=>'NF-101','name'=>'Changing Table with Drawers','brand'=>'SleepWell','price'=>3999.00,'desc'=>'Changing table with storage drawers'],
  ['cat'=>'Nursery Furniture','sku'=>'NF-102','name'=>'Rocking Chair Classic','brand'=>'CozyHome','price'=>5499.00,'desc'=>'Classic nursery rocking chair'],
  ['cat'=>'Nursery Furniture','sku'=>'NF-103','name'=>'Kids Storage Box Wooden','brand'=>'PlayLearn','price'=>599.00,'desc'=>'Wooden toy storage box with lid'],
  ['cat'=>'Nursery Furniture','sku'=>'NF-104','name'=>'Star Projector Night Light','brand'=>'SleepWell','price'=>499.00,'desc'=>'Starry night projector lamp'],
  ['cat'=>'Nursery Furniture','sku'=>'NF-105','name'=>'Foam Puzzle Play Mat','brand'=>'PlayLearn','price'=>799.00,'desc'=>'Interlocking foam play mat 12pcs'],
  ['cat'=>'Safety & Health','sku'=>'SH-101','name'=>'Digital Baby Thermometer','brand'=>'SafeKids','price'=>299.00,'desc'=>'Fast digital thermometer for babies'],
  ['cat'=>'Safety & Health','sku'=>'SH-102','name'=>'Corner Guards 8pcs','brand'=>'SafeKids','price'=>179.00,'desc'=>'Clear corner guards for table edges'],
  ['cat'=>'Safety & Health','sku'=>'SH-103','name'=>'Outlet Covers 12pcs','brand'=>'SafeKids','price'=>149.00,'desc'=>'Child-proof outlet covers'],
  ['cat'=>'Safety & Health','sku'=>'SH-104','name'=>'Baby Nail Clipper Set','brand'=>'CleanBaby','price'=>199.00,'desc'=>'Safe baby nail clipper kit'],
  ['cat'=>'Safety & Health','sku'=>'SH-105','name'=>'Mini Humidifier','brand'=>'CleanBaby','price'=>599.00,'desc'=>'USB mini humidifier for nursery'],
  ['cat'=>'Educational Materials','sku'=>'EM-101','name'=>'Flash Cards Alphabet','brand'=>'CreativeKids','price'=>199.00,'desc'=>'Alphabet flash cards with pictures'],
  ['cat'=>'Educational Materials','sku'=>'EM-102','name'=>'Number Learning Board','brand'=>'PlayLearn','price'=>349.00,'desc'=>'Wooden number and counting board'],
  ['cat'=>'Educational Materials','sku'=>'EM-103','name'=>'Science Kit for Kids','brand'=>'CreativeKids','price'=>499.00,'desc'=>'Beginner science experiment kit'],
  ['cat'=>'Educational Materials','sku'=>'EM-104','name'=>'Story Books Set 10pcs','brand'=>'PageOne','price'=>549.00,'desc'=>'Classic story books for kids'],
  ['cat'=>'Educational Materials','sku'=>'EM-105','name'=>'Magnetic Letters & Numbers','brand'=>'PlayLearn','price'=>299.00,'desc'=>'Magnetic alphabet set for fridge'],
  ['cat'=>'School Supplies','sku'=>'SS-PEN-101','name'=>'Gel Pen Set 12 Colors','brand'=>'WriteWell','price'=>179.00,'desc'=>'Smooth gel pens, 12 vibrant colors'],
  ['cat'=>'School Supplies','sku'=>'SS-NTB-101','name'=>'Hardcover Journal A5','brand'=>'PageOne','price'=>149.00,'desc'=>'Hardcover lined journal 200 pages'],
  ['cat'=>'School Supplies','sku'=>'SS-CAS-101','name'=>'Pencil Case Canvas','brand'=>'CarryAll','price'=>129.00,'desc'=>'Canvas pencil pouch with zipper'],
  ['cat'=>'School Supplies','sku'=>'SS-CAL-101','name'=>'Scientific Calculator','brand'=>'MathPro','price'=>599.00,'desc'=>'Scientific calculator 240 functions'],
  ['cat'=>'School Supplies','sku'=>'SS-HIG-101','name'=>'Highlighter Pack 6pcs','brand'=>'WriteWell','price'=>99.00,'desc'=>'Pastel highlighters set'],
  ['cat'=>'School Supplies','sku'=>'SS-STK-101','name'=>'Sticky Notes 6 Pads','brand'=>'PageOne','price'=>89.00,'desc'=>'Sticky memo pads assorted colors'],
  ['cat'=>'School Supplies','sku'=>'SS-BOX-101','name'=>'Lunch Box Bento','brand'=>'CarryAll','price'=>249.00,'desc'=>'Bento lunch box with compartments'],
  ['cat'=>'Makeup','sku'=>'MK-LIP-101','name'=>'Lip Gloss Set 6pcs','brand'=>'GlowGirl','price'=>349.00,'desc'=>'Glossy lip gloss collection'],
  ['cat'=>'Makeup','sku'=>'MK-MAS-101','name'=>'Waterproof Mascara','brand'=>'BeautyPro','price'=>279.00,'desc'=>'Volume waterproof mascara'],
  ['cat'=>'Makeup','sku'=>'MK-CON-101','name'=>'Concealer Palette','brand'=>'PureBlend','price'=>399.00,'desc'=>'Cream concealer palette 6 shades'],
  ['cat'=>'Makeup','sku'=>'MK-SPO-101','name'=>'Beauty Sponge Set','brand'=>'BeautyPro','price'=>149.00,'desc'=>'Makeup sponge blender set'],
  ['cat'=>'Makeup','sku'=>'MK-LIN-101','name'=>'Lip Liner Pencil','brand'=>'GlowGirl','price'=>129.00,'desc'=>'Retractable lip liner'],
  ['cat'=>'Makeup','sku'=>'MK-LAS-101','name'=>'False Eyelashes 5 Pairs','brand'=>'BeautyPro','price'=>199.00,'desc'=>'Natural false lashes set'],
  ['cat'=>'Makeup','sku'=>'MK-SET-101','name'=>'Setting Spray','brand'=>'PureBlend','price'=>249.00,'desc'=>'Long-lasting makeup setting spray'],
  ['cat'=>'Dresses','sku'=>'DR-WRP-101','name'=>'Wrap Dress Polka Dot','brand'=>'BelleAmie','price'=>799.00,'desc'=>'Chic wrap dress with polka dots'],
  ['cat'=>'Dresses','sku'=>'DR-BLZ-101','name'=>'Blazer Dress Formal','brand'=>'BelleAmie','price'=>1199.00,'desc'=>'Tailored blazer dress for office'],
  ['cat'=>'Dresses','sku'=>'DR-MAX-101','name'=>'Maxi Dress Boho','brand'=>'BreezeWear','price'=>999.00,'desc'=>'Boho floral maxi dress'],
  ['cat'=>'Dresses','sku'=>'DR-DEN-101','name'=>'Denim Shirt Dress','brand'=>'BreezeWear','price'=>849.00,'desc'=>'Casual denim shirt dress'],
  ['cat'=>'Dresses','sku'=>'DR-RUF-101','name'=>'Tiered Ruffle Dress','brand'=>'BelleAmie','price'=>899.00,'desc'=>'Tiered ruffle midi dress'],
  ['cat'=>'Dresses','sku'=>'DR-SHE-101','name'=>'Sheath Dress Classic','brand'=>'NightVelvet','price'=>1099.00,'desc'=>'Classic sheath dress knee-length'],
  ['cat'=>'Dresses','sku'=>'DR-LAC-101','name'=>'Lace Cocktail Dress','brand'=>'NightVelvet','price'=>1299.00,'desc'=>'Elegant lace cocktail dress'],
  ['cat'=>'Furniture','sku'=>'FR-COR-101','name'=>'Corner Computer Desk','brand'=>'OakCraft','price'=>3499.00,'desc'=>'L-shaped corner computer desk'],
  ['cat'=>'Furniture','sku'=>'FR-FOL-101','name'=>'Folding Dining Chair','brand'=>'SitWell','price'=>899.00,'desc'=>'Foldable wooden dining chair'],
  ['cat'=>'Furniture','sku'=>'FR-REC-101','name'=>'Recliner Single Seat','brand'=>'CozyHome','price'=>8999.00,'desc'=>'Leather recliner with cup holder'],
  ['cat'=>'Furniture','sku'=>'FR-TV-101','name'=>'TV Stand Modern','brand'=>'OakCraft','price'=>2999.00,'desc'=>'Modern TV stand with shelves'],
  ['cat'=>'Furniture','sku'=>'FR-STD-101','name'=>'Standing Desk Converter','brand'=>'SitWell','price'=>2499.00,'desc'=>'Adjustable standing desk riser'],
  ['cat'=>'Furniture','sku'=>'FR-BAR-101','name'=>'Bar Stool Set 2pcs','brand'=>'OakCraft','price'=>1999.00,'desc'=>'Modern bar stool set of 2'],
  ['cat'=>'Furniture','sku'=>'FR-OTT-101','name'=>'Ottoman Storage','brand'=>'CozyHome','price'=>1299.00,'desc'=>'Fabric ottoman with storage'],
  ['cat'=>'Toys','sku'=>'TY-LEG-101','name'=>'LEGO City Set 300pcs','brand'=>'BrickFun','price'=>1299.00,'desc'=>'City building blocks 300 pieces'],
  ['cat'=>'Toys','sku'=>'TY-DRN-101','name'=>'Drone Mini with Camera','brand'=>'SpeedKid','price'=>1499.00,'desc'=>'Mini drone with HD camera'],
  ['cat'=>'Toys','sku'=>'TY-UNI-101','name'=>'Plush Unicorn 40cm','brand'=>'CuddleCo','price'=>399.00,'desc'=>'Rainbow plush unicorn'],
  ['cat'=>'Toys','sku'=>'TY-CHE-101','name'=>'Chess Board Wooden','brand'=>'FunTable','price'=>549.00,'desc'=>'Classic wooden chess set'],
  ['cat'=>'Toys','sku'=>'TY-MAR-101','name'=>'Marble Run 80pcs','brand'=>'BrickFun','price'=>699.00,'desc'=>'Marble run building set'],
  ['cat'=>'Toys','sku'=>'TY-TRA-101','name'=>'Train Set Electric','brand'=>'SpeedKid','price'=>899.00,'desc'=>'Electric train track set'],
  ['cat'=>'Toys','sku'=>'TY-ACT-101','name'=>'Action Figure 6pcs','brand'=>'CuddleCo','price'=>499.00,'desc'=>'Superhero action figures set'],
  ['cat'=>'Sports Equipment','sku'=>'SP-ROL-101','name'=>'Foam Roller','brand'=>'FlexFit','price'=>349.00,'desc'=>'High-density foam roller'],
  ['cat'=>'Sports Equipment','sku'=>'SP-KET-101','name'=>'Kettlebell 8kg','brand'=>'IronPeak','price'=>999.00,'desc'=>'Cast iron kettlebell 8kg'],
  ['cat'=>'Sports Equipment','sku'=>'SP-SOC-101','name'=>'Soccer Ball Size 5','brand'=>'HoopStar','price'=>599.00,'desc'=>'Official match soccer ball'],
  ['cat'=>'Sports Equipment','sku'=>'SP-JMP-101','name'=>'Jump Rope Speed','brand'=>'FlexFit','price'=>199.00,'desc'=>'Speed jump rope adjustable'],
  ['cat'=>'Sports Equipment','sku'=>'SP-PIL-101','name'=>'Pilates Ball 65cm','brand'=>'FlexFit','price'=>499.00,'desc'=>'Anti-burst pilates ball'],
  ['cat'=>'Sports Equipment','sku'=>'SP-PUL-101','name'=>'Doorway Pull Up Bar','brand'=>'IronPeak','price'=>799.00,'desc'=>'Doorway pull-up bar no drilling'],
  ['cat'=>'Sports Equipment','sku'=>'SP-BOX-101','name'=>'Boxing Gloves 12oz','brand'=>'IronPeak','price'=>699.00,'desc'=>'Training boxing gloves'],
  ['cat'=>'Jewelry','sku'=>'JW-CHO-101','name'=>'Layered Choker Set','brand'=>'LuxeAura','price'=>599.00,'desc'=>'Boho layered choker necklace set'],
  ['cat'=>'Jewelry','sku'=>'JW-HOO-101','name'=>'Gold Hoop Earrings','brand'=>'LuxeAura','price'=>399.00,'desc'=>'Classic gold hoop earrings'],
  ['cat'=>'Jewelry','sku'=>'JW-LEA-101','name'=>'Leather Strap Watch','brand'=>'TimeLux','price'=>1499.00,'desc'=>'Vintage leather strap watch'],
  ['cat'=>'Jewelry','sku'=>'JW-BEA-101','name'=>'Beaded Bracelet Set','brand'=>'LuxeAura','price'=>299.00,'desc'=>'Bohemian beaded bracelet stack'],
  ['cat'=>'Jewelry','sku'=>'JW-HEA-101','name'=>'Heart Pendant Necklace','brand'=>'LuxeAura','price'=>499.00,'desc'=>'Gold heart pendant necklace'],
  ['cat'=>'Jewelry','sku'=>'JW-STU-101','name'=>'Pearl Stud Earrings','brand'=>'LuxeAura','price'=>349.00,'desc'=>'Classic pearl stud earrings'],
  ['cat'=>'Jewelry','sku'=>'JW-BAN-101','name'=>'Metal Watch Band','brand'=>'TimeLux','price'=>799.00,'desc'=>'Stainless steel mesh watch band'],
  ['cat'=>'Gadgets','sku'=>'GD-HEA-101','name'=>'Wireless Headphones','brand'=>'SonicWave','price'=>1299.00,'desc'=>'Over-ear wireless headphones'],
  ['cat'=>'Gadgets','sku'=>'GD-CHA-101','name'=>'Fast Charger 65W','brand'=>'VoltMax','price'=>699.00,'desc'=>'65W GaN fast charger'],
  ['cat'=>'Gadgets','sku'=>'GD-SOU-101','name'=>'Soundbar Mini','brand'=>'SonicWave','price'=>1499.00,'desc'=>'Compact soundbar for TV'],
  ['cat'=>'Gadgets','sku'=>'GD-FIT-101','name'=>'Fitness Band','brand'=>'PulseTech','price'=>999.00,'desc'=>'Smart fitness band with display'],
  ['cat'=>'Gadgets','sku'=>'GD-CAS-101','name'=>'Earbuds Case Silicone','brand'=>'SonicWave','price'=>149.00,'desc'=>'Silicone protective case'],
  ['cat'=>'Gadgets','sku'=>'GD-HOL-101','name'=>'Car Phone Holder','brand'=>'VoltMax','price'=>249.00,'desc'=>'Dashboard car phone mount'],
  ['cat'=>'Gadgets','sku'=>'GD-LED-101','name'=>'LED Strip Lights 5m','brand'=>'VoltMax','price'=>399.00,'desc'=>'RGB LED strip with remote'],
  ['cat'=>'Appliances','sku'=>'AP-IND-101','name'=>'Induction Cooker','brand'=>'KusinaPro','price'=>1999.00,'desc'=>'Single induction cooker'],
  ['cat'=>'Appliances','sku'=>'AP-AIR-101','name'=>'Air Purifier','brand'=>'BreezeAir','price'=>3499.00,'desc'=>'HEPA air purifier for home'],
  ['cat'=>'Appliances','sku'=>'AP-KET-101','name'=>'Electric Kettle 1.7L','brand'=>'KusinaPro','price'=>599.00,'desc'=>'Stainless steel electric kettle'],
  ['cat'=>'Appliances','sku'=>'AP-MIX-101','name'=>'Hand Mixer 5 Speed','brand'=>'KusinaPro','price'=>799.00,'desc'=>'Electric hand mixer 5 speeds'],
  ['cat'=>'Appliances','sku'=>'AP-COF-101','name'=>'Coffee Maker Drip','brand'=>'HeatWave','price'=>1299.00,'desc'=>'Drip coffee maker 10 cups'],
  ['cat'=>'Appliances','sku'=>'AP-VAC-101','name'=>'Handheld Vacuum Cleaner','brand'=>'BreezeAir','price'=>1499.00,'desc'=>'Cordless handheld vacuum'],
  ['cat'=>'Appliances','sku'=>'AP-TOA-101','name'=>'Toaster 2 Slice','brand'=>'HeatWave','price'=>699.00,'desc'=>'2-slice pop-up toaster'],
  ['cat'=>'Tools','sku'=>'TL-ANG-101','name'=>'Angle Grinder 4 inch','brand'=>'ToolForce','price'=>1499.00,'desc'=>'Powerful angle grinder 750W'],
  ['cat'=>'Tools','sku'=>'TL-HAM-101','name'=>'Hammer Claw 16oz','brand'=>'ToolForce','price'=>349.00,'desc'=>'Fiberglass claw hammer'],
  ['cat'=>'Tools','sku'=>'TL-TAP-101','name'=>'Measuring Tape 5m','brand'=>'BuildRight','price'=>149.00,'desc'=>'Retractable measuring tape'],
  ['cat'=>'Tools','sku'=>'TL-SOC-101','name'=>'Socket Set 46pcs','brand'=>'BuildRight','price'=>1299.00,'desc'=>'Drive socket set 46 pieces'],
  ['cat'=>'Tools','sku'=>'TL-SAW-101','name'=>'Handsaw 12 inch','brand'=>'ToolForce','price'=>299.00,'desc'=>'Sharp handsaw for wood'],
  ['cat'=>'Tools','sku'=>'TL-PLI-101','name'=>'Pliers Set 3pcs','brand'=>'ToolForce','price'=>279.00,'desc'=>'Pliers set 3-piece'],
  ['cat'=>'Tools','sku'=>'TL-GLO-101','name'=>'Work Gloves Pair','brand'=>'BuildRight','price'=>149.00,'desc'=>'Heavy-duty work gloves'],
];

$added=0;
foreach($list as $p){
  $cid = $catId($p['cat']);
  if(!$cid){ echo "Missing cat {$p['cat']}\n"; continue; }
  $product = Product::updateOrCreate(['sku'=>$p['sku']],[
    'seller_id'=>$sellerId,
    'category_id'=>$cid,
    'name'=>$p['name'],
    'description'=>$p['desc'],
    'brand'=>$p['brand'],
    'model'=>'GEN-'.$p['sku'],
    'sku'=>$p['sku'],
    'material'=>'Generic',
    'dimensions'=>'Standard',
    'weight'=>'500g',
    'warranty'=>'No Warranty',
    'origin'=>'Philippines',
    'price'=>$p['price'],
    'stock'=>rand(30,200),
    'image'=>'products/p1_1.png',
    'status'=>'active',
  ]);
  $added++;
}
echo "Copied $added products from invoiz folder to Invoiz-main\n";
echo "Total products now: ".Product::count()."\n";
echo "Categories: ".Category::count()."\n";
