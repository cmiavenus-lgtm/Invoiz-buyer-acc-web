import 'package:flutter/material.dart';
import '../config.dart';
import '../services/api_service.dart';
import '../services/recently_viewed_service.dart';
import '../theme.dart';
import '../widgets/auth_service_provider.dart';
import '../widgets/main_layout.dart';
import 'product_detail_screen.dart';
import 'product_list_screen.dart';
import 'seller_store_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _api = ApiService();
  final _searchCtrl = TextEditingController();

  List _categories = [];
  List _products = [];
  List _recent = [];
  bool _loading = true;
  int? _selectedCategory;
  String _search = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final cats = await _api.get('categories');
      final prods = await _api.get('products', query: {'per_page': '20'});
      final recent = await RecentlyViewedService.load();
      setState(() {
        _categories = (cats['categories'] as List).cast<Map<String, dynamic>>();
        final data = prods['data'] as List;
        _products = data.cast<Map<String, dynamic>>();
        _recent = recent;
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
      _showError(e.toString());
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  void _searchProducts() {
    setState(() {
      _search = _searchCtrl.text.trim();
      _selectedCategory = null;
    });
    if (_search.isEmpty) {
      _load();
      return;
    }
    setState(() => _loading = true);
    _api.get('products', query: {'search': _search, 'per_page': '30'}).then((prods) {
      setState(() {
        _products = (prods['data'] as List).cast<Map<String, dynamic>>();
        _loading = false;
      });
    }).catchError((e) {
      setState(() => _loading = false);
      _showError(e.toString());
    });
  }

  void _selectCategory(int? id) {
    setState(() {
      _selectedCategory = id;
      _loading = true;
    });
    final query = id != null ? {'category_id': '$id', 'per_page': '30'} : {'per_page': '20'};
    _api.get('products', query: query).then((prods) {
      setState(() {
        _products = (prods['data'] as List).cast<Map<String, dynamic>>();
        _loading = false;
      });
    }).catchError((e) {
      setState(() => _loading = false);
      _showError(e.toString());
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MainLayout(
      title: 'Home',
      child: Column(
        children: [
          _searchBar(context),
          if (_selectedCategory != null)
            _selectedCategoryBar(),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? const Center(child: Text('No products found.'))
                    : _buildBody(context),
          ),
        ],
      ),
    );
  }

  Widget _selectedCategoryBar() {
    final name = _categories
            .firstWhere((c) => c['id'] == _selectedCategory, orElse: () => {'name': ''})['name']
        as String;
    return Container(
      width: double.infinity,
      color: AppColors.accent,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          Icon(Icons.filter_alt_outlined, size: 16, color: AppColors.primary),
          const SizedBox(width: 6),
          Text(
            name,
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.primary),
          ),
          const Spacer(),
          GestureDetector(
            onTap: () => _selectCategory(null),
            child: Icon(Icons.close, size: 18, color: AppColors.primary),
          ),
        ],
      ),
    );
  }

  Widget _searchBar(BuildContext context) {
    return Container(
      color: AppColors.card,
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 16),
      child: Row(children: [
        Expanded(child: Container(height: 48, decoration: BoxDecoration(color: AppColors.surfaceSoft, borderRadius: BorderRadius.circular(999), border: Border.all(color: AppColors.border)), child: Row(children: [const SizedBox(width: 14), Icon(Icons.search_rounded, color: AppColors.textSecondary, size: 20), const SizedBox(width: 8), Expanded(child: TextField(controller: _searchCtrl, textInputAction: TextInputAction.search, onSubmitted: (_) => _searchProducts(), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500), decoration: const InputDecoration(hintText: 'Search products, brands or categories — desktop store', border: InputBorder.none, isDense: true, contentPadding: EdgeInsets.symmetric(vertical: 12))))]))),
        const SizedBox(width: 12),
        SizedBox(height: 48, child: ElevatedButton.icon(onPressed: _searchProducts, icon: const Icon(Icons.search, size: 18), label: const Text('Search'), style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(horizontal: 22), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999))))),
        const SizedBox(width: 10),
        OutlinedButton.icon(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ProductListScreen())), icon: const Icon(Icons.tune, size: 18), label: const Text('Filters'), style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)), side: BorderSide(color: AppColors.border))),
      ]),
    );
  }

  Widget _buildBody(BuildContext context) {
    return CustomScrollView(
      slivers: [
        SliverToBoxAdapter(child: _heroBanner(context)),
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 22, 16, 12),
            child: Row(
              children: [
                const Text(
                  'Categories',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, letterSpacing: -0.2),
                ),
                const Spacer(),
                TextButton(
                  onPressed: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const ProductListScreen()),
                  ),
                  child: const Text('View all'),
                ),
              ],
            ),
          ),
        ),
        SliverToBoxAdapter(
          child: SizedBox(
            height: 100,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _categories.length,
              separatorBuilder: (_, __) => const SizedBox(width: 12),
              itemBuilder: (context, i) => _categoryChip(_categories[i]),
            ),
          ),
        ),
        if (_recent.isNotEmpty) ...[
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 24, 16, 10),
              child: Row(
                children: [
                  const Text(
                    'Recently Viewed',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, letterSpacing: -0.2),
                  ),
                  const Spacer(),
                  IconButton(
                    icon: Icon(Icons.history, size: 20, color: AppColors.textSecondary),
                    tooltip: 'Clear history',
                    onPressed: () async {
                      await RecentlyViewedService.clear();
                      if (mounted) setState(() => _recent = []);
                    },
                  ),
                ],
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: SizedBox(
              height: 190,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: _recent.length,
                separatorBuilder: (_, __) => const SizedBox(width: 12),
                itemBuilder: (context, i) => _recentCard(_recent[i]),
              ),
            ),
          ),
        ],
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 24, 16, 10),
            child: Row(
              children: [
                const Text(
                  'For You',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, letterSpacing: -0.2),
                ),
                const Spacer(),
                Text(
                  '${_products.length} items',
                  style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                ),
              ],
            ),
          ),
        ),
        SliverPadding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 20),
          sliver: SliverGrid(
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: _gridCount(context),
              mainAxisSpacing: 14,
              crossAxisSpacing: 14,
              childAspectRatio: _gridAspect(context),
            ),
            delegate: SliverChildBuilderDelegate(
              (context, i) => _productCard(_products[i]),
              childCount: _products.length,
            ),
          ),
        ),
      ],
    );
  }

  Widget _recentCard(Map<String, dynamic> p) {
    final price = double.tryParse('${p['price']}') ?? 0;
    final sold = p['sold'] is int ? (p['sold'] as int) : 0;
    return GestureDetector(
      onTap: () async {
        await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => ProductDetailScreen(productId: p['id'] as int),
          ),
        );
        if (mounted) {
          final recent = await RecentlyViewedService.load();
          setState(() => _recent = recent);
        }
      },
      child: Container(
        width: 130,
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 100,
              width: double.infinity,
              color: AppColors.surfaceSoft,
              child: _productColorBox(p['name'] as String, fontSize: 11),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      p['name'] as String,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 11.5, height: 1.2, fontWeight: FontWeight.w500),
                    ),
                    const Spacer(),
                    Text(
                      _formatPrice(price),
                      style: TextStyle(
                        color: AppColors.warning,
                        fontWeight: FontWeight.w800,
                        fontSize: 13,
                      ),
                    ),
                    if (sold > 0)
                      Text('$sold sold', style: TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _heroBanner(BuildContext context) {
    final auth = AuthServiceProvider.of(context);
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
      child: Container(
        height: 240,
        decoration: BoxDecoration(
          gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [const Color(0xFF0F766E), const Color(0xFF0D9488), const Color(0xFF14B8A6)]),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [BoxShadow(color: const Color(0xFF0F766E).withValues(alpha: 0.18), blurRadius: 20, offset: const Offset(0, 6))],
        ),
        child: Row(children: [
          Expanded(
            flex: 5,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(24, 20, 16, 20),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [
                Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5), decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.14), borderRadius: BorderRadius.circular(999), border: Border.all(color: Colors.white.withValues(alpha: 0.22))), child: const Text('✓ Trusted by 10,000+ buyers • COD nationwide', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 0.2))),
                const SizedBox(height: 10),
                Text(auth.isLoggedIn ? 'Welcome back, ${auth.user?.firstName ?? 'there'}!' : 'Invoiz — Curated for you', style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w800, height: 1.05, letterSpacing: -0.6)),
                const SizedBox(height: 8),
                const Text('Premium essentials at honest prices. Vouchers & verified sellers.', style: TextStyle(color: Colors.white, fontSize: 12.5, height: 1.4)),
                const SizedBox(height: 14),
                Row(children: [
                  ElevatedButton.icon(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ProductListScreen())), icon: const Icon(Icons.storefront, size: 16), label: const Text('Shop collection', style: TextStyle(fontSize: 13)), style: ElevatedButton.styleFrom(backgroundColor: Colors.white, foregroundColor: Color(0xFF0F766E), padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)))),
                  const SizedBox(width: 8),
                  OutlinedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ProductListScreen())), style: OutlinedButton.styleFrom(foregroundColor: Colors.white, side: const BorderSide(color: Colors.white, width: 1.2), padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999))), child: const Text('View deals', style: TextStyle(fontSize: 13))),
                ]),
              ]),
            ),
          ),
          Expanded(
            flex: 4,
            child: Container(
              margin: const EdgeInsets.all(14),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 14, offset: const Offset(0, 6))]),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Stack(children: [
                  Positioned.fill(child: Container(color: const Color(0xFFF8FAFC), child: Icon(Icons.shopping_bag_rounded, size: 110, color: const Color(0xFF0F766E).withValues(alpha: 0.07)))),
                  Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Container(width: 64, height: 64, decoration: BoxDecoration(color: const Color(0xFF0F766E), borderRadius: BorderRadius.circular(16)), child: const Icon(Icons.local_mall, color: Colors.white, size: 30)), const SizedBox(height: 8), const Text('Invoiz', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))), const Text('Desktop Store', style: TextStyle(fontSize: 10, color: Color(0xFF64748B), fontWeight: FontWeight.w600, letterSpacing: 1)) ])),
                  Positioned(bottom: 10, left: 10, right: 10, child: Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8), decoration: BoxDecoration(color: const Color(0xFF0F172A), borderRadius: BorderRadius.circular(12)), child: Row(children: [const Icon(Icons.local_offer, color: Color(0xFFF59E0B), size: 14), const SizedBox(width: 6), const Expanded(child: Text('WELCOME10 • SAVE15', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700))), Container(padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3), decoration: BoxDecoration(color: const Color(0xFFF59E0B), borderRadius: BorderRadius.circular(999)), child: const Text('COD', style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w800)))]))),
                ]),
              ),
            ),
          ),
        ]),
      ),
    );
  }

  Widget _categoryChip(Map<String, dynamic> cat) {
    final selected = cat['id'] == _selectedCategory;
    return _HoverLift(
      lift: 4,
      child: GestureDetector(
        onTap: () => _selectCategory(cat['id'] as int),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          width: 108, padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
          decoration: BoxDecoration(
            color: selected ? AppColors.primary : AppColors.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: selected ? AppColors.primary : AppColors.border, width: 1),
            boxShadow: selected ? [BoxShadow(color: AppColors.primary.withValues(alpha: 0.18), blurRadius: 14, offset: const Offset(0, 6))] : [BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10, offset: const Offset(0, 3))],
          ),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Container(width: 36, height: 36, decoration: BoxDecoration(color: selected ? Colors.white.withValues(alpha: 0.16) : AppColors.accent, borderRadius: BorderRadius.circular(12)), child: Icon(_categoryIcon(cat['name'] as String), color: selected ? Colors.white : AppColors.primary, size: 20)),
            const SizedBox(height: 8),
            Padding(padding: const EdgeInsets.symmetric(horizontal: 4), child: Text(cat['name'] as String, maxLines: 2, overflow: TextOverflow.ellipsis, textAlign: TextAlign.center, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, height: 1.15, color: selected ? Colors.white : AppColors.textPrimary))),
          ]),
        ),
      ),
    );
  }

  Widget _productCard(Map<String, dynamic> p) {
    final price = double.tryParse('${p['price']}') ?? 0;
    final rating = p['rating'] != null ? double.tryParse('${p['rating']}') : null;
    final sold = p['sold'] is int ? (p['sold'] as int) : 0;
    final shop = p['shop'];
    final shopName = shop is Map<String, dynamic> ? (shop['business_name'] as String? ?? 'Invoiz Store') : null;
    final shopSellerId = shop is Map<String, dynamic> ? (shop['seller_id'] as int?) : null;
    return _HoverLift(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () async {
            await Navigator.push(context, MaterialPageRoute(builder: (_) => ProductDetailScreen(productId: p['id'] as int)));
            if (mounted) { final recent = await RecentlyViewedService.load(); setState(() => _recent = recent); }
          },
          child: Container(
            decoration: BoxDecoration(color: AppColors.card, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppColors.border)),
            clipBehavior: Clip.antiAlias,
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Container(height: 170, width: double.infinity, color: AppColors.surfaceSoft, child: Stack(children: [
                Positioned.fill(child: _productColorBox(p['name'] as String)),
                if (p['stock'] is int && (p['stock'] as int) == 0) Positioned.fill(child: Container(color: Colors.black.withValues(alpha: 0.38), alignment: Alignment.center, child: Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(999)), child: const Text('SOLD OUT', style: TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.w800, fontSize: 11, letterSpacing: 1.2))))),
                if (rating != null) Positioned(top: 10, left: 10, child: Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(999), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 8, offset: const Offset(0, 2))]), child: Row(children: [Icon(Icons.star_rounded, color: AppColors.gold, size: 14), const SizedBox(width: 3), Text(rating.toStringAsFixed(1), style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700))]))),
                if (sold > 0) Positioned(top: 10, right: 10, child: Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: const Color(0xFF0F172A), borderRadius: BorderRadius.circular(999)), child: Text('$sold sold', style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.w700)))),
                Positioned(bottom: 10, right: 10, child: Container(width: 32, height: 32, decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(999), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 8, offset: const Offset(0, 2))]), child: Icon(Icons.add_shopping_cart, size: 16, color: AppColors.primary))),
              ])),
              Expanded(child: Padding(padding: const EdgeInsets.fromLTRB(12, 12, 12, 10), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(p['name'] as String, maxLines: 2, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 13.5, height: 1.3, fontWeight: FontWeight.w700, letterSpacing: -0.1, color: _colorForName(p['name'] as String))),
                const SizedBox(height: 4),
                Row(children: [
                  for (int i = 0; i < 3; i++) Padding(padding: EdgeInsets.only(right: 4), child: Container(width: 14, height: 14, decoration: BoxDecoration(color: [Color(0xFF0F766E), Color(0xFFDB2777), Color(0xFFF59E0B), Color(0xFF1D4ED8), Color(0xFF16A34A)][(p['name'].hashCode + i) % 5], shape: BoxShape.circle, border: Border.all(color: Colors.white, width: 1.5), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 4, offset: Offset(0, 1))]))),
                  const SizedBox(width: 4),
                  Text('3 colors', style: TextStyle(fontSize: 10, color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
                ]),
                const SizedBox(height: 6),
                Text(_formatPrice(price), style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w800, fontSize: 16, letterSpacing: -0.3)),
                const SizedBox(height: 4),
                if (shopName != null && shopSellerId != null) GestureDetector(onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => SellerStoreScreen(sellerId: shopSellerId))), child: Row(children: [Icon(Icons.storefront_rounded, size: 12, color: AppColors.textSecondary), const SizedBox(width: 4), Expanded(child: Text(shopName, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 11, color: AppColors.textSecondary, fontWeight: FontWeight.w600))) ])),
                const Spacer(),
                Row(children: [
                  Container(padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3), decoration: BoxDecoration(color: AppColors.accent, borderRadius: BorderRadius.circular(999)), child: Row(children: [Icon(Icons.local_shipping_outlined, size: 11, color: AppColors.primary), const SizedBox(width: 3), Text('COD', style: TextStyle(fontSize: 10, color: AppColors.primary, fontWeight: FontWeight.w700))])),
                  const Spacer(),
                  Text('${p['stock']} left', style: TextStyle(fontSize: 11, color: AppColors.textSecondary, fontWeight: FontWeight.w500)),
                ]),
              ]))),
            ]),
          ),
        ),
      ),
    );
  }

  Color _colorForName(String name) {
    final h = name.codeUnits.fold<int>(0, (a, c) => a + c);
    const palette = [Color(0xFF0F766E), Color(0xFF1D4ED8), Color(0xFF7C3AED), Color(0xFFDB2777), Color(0xFFEA580C), Color(0xFF16A34A), Color(0xFF0891B2), Color(0xFF4F46E5), Color(0xFFDC2626), Color(0xFF475569)];
    return palette[h % palette.length];
  }
  String _initialsFor(String name) {
    final p = name.trim().split(RegExp(r'\s+'));
    if (p.length >= 2) return (p[0][0] + p[1][0]).toUpperCase();
    if (p[0].length >= 2) return p[0].substring(0, 2).toUpperCase();
    return p[0].substring(0, 1).toUpperCase();
  }
  Widget _productColorBox(String name, {double fontSize = 15}) {
    final c = _colorForName(name);
    return Container(
      color: c,
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Container(width: 44, height: 44, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.22), borderRadius: BorderRadius.circular(12)), child: Center(child: Text(_initialsFor(name), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)))),
            const SizedBox(height: 8),
            Text(name, textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis, style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: fontSize, height: 1.2)),
          ]),
        ),
      ),
    );
  }

  // Keep for fallback but now picture is color with name only (user request)
  Widget _productImage(String? url) => _productColorBox('Product');
  String _imageUrl(String path) => AppConfig.storageUrl(path);

  String _formatPrice(double value) => '₱${value.toStringAsFixed(2)}';

  IconData _categoryIcon(String name) {
    switch (name.toLowerCase()) {
      case 'fashion':
        return Icons.checkroom;
      case 'electronics':
        return Icons.devices;
      case 'home & living':
        return Icons.chair;
      case 'beauty & health':
        return Icons.spa;
      case 'sports & outdoors':
        return Icons.sports_basketball;
      case 'toys & hobbies':
        return Icons.toys;
      case 'groceries':
        return Icons.local_grocery_store;
      case 'books':
        return Icons.menu_book;
      default:
        return Icons.category;
    }
  }

  // --- RESPONSIVE + HOVER LIFT ---
  int _gridCount(BuildContext context) {
    final w = MediaQuery.of(context).size.width;
    if (w >= 1400) return 5;
    if (w >= 1100) return 4;
    if (w >= 800) return 3;
    if (w >= 600) return 2;
    return 2;
  }
  double _gridAspect(BuildContext context) {
    final w = MediaQuery.of(context).size.width;
    if (w >= 1100) return 0.72;
    return 0.68;
  }
}

class _HoverLift extends StatefulWidget {
  final Widget child;
  final double lift;
  const _HoverLift({required this.child, this.lift = 6});
  @override
  State<_HoverLift> createState() => _HoverLiftState();
}
class _HoverLiftState extends State<_HoverLift> {
  bool _hover = false;
  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      onEnter: (_) => setState(() => _hover = true),
      onExit: (_) => setState(() => _hover = false),
      cursor: SystemMouseCursors.click,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        curve: Curves.easeOut,
        transform: _hover ? (Matrix4.identity()..translate(0, -widget.lift, 0)) : Matrix4.identity(),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          decoration: BoxDecoration(boxShadow: _hover ? [BoxShadow(color: Colors.black.withValues(alpha: 0.12), blurRadius: 18, offset: const Offset(0, 8))] : [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 14, offset: const Offset(0, 4))]),
          child: widget.child,
        ),
      ),
    );
  }
}
