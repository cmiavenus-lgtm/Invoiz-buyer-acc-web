import 'package:flutter/material.dart';
import '../config.dart';
import '../models/cart.dart';
import '../services/api_service.dart';
import '../theme.dart';
import '../widgets/auth_service_provider.dart';
import '../widgets/main_layout.dart';
import 'checkout_screen.dart';
import 'login_screen.dart';
import 'product_detail_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final _api = ApiService();
  Cart? _cart;
  bool _loading = true;
  final Set<int> _selected = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final data = await _api.get('cart');
      final cart = Cart.fromJson(data);
      setState(() {
        _cart = cart;
        _selected.clear();
        _selected.addAll(cart.items.map((i) => i.id));
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  double get _selectedSubtotal {
    if (_cart == null) return 0;
    return _cart!.items
        .where((i) => _selected.contains(i.id))
        .fold(0.0, (s, i) => s + i.lineTotal);
  }

  Future<void> _updateQty(CartItem item, int qty) async {
    if (qty < 1) return;
    try {
      await _api.put('cart/${item.id}', {'quantity': qty});
      _load();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> _remove(CartItem item) async {
    try {
      await _api.delete('cart/${item.id}');
      _load();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthServiceProvider.of(context);
    if (!auth.isLoggedIn) {
      return MainLayout(
        title: 'My Cart',
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.lock_outline, size: 60, color: AppColors.textSecondary),
              const SizedBox(height: 12),
              const Text('Please log in to view your cart.'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())),
                child: const Text('Login'),
              ),
            ],
          ),
        ),
      );
    }

    return MainLayout(
      title: 'My Cart',
      child: _loading
          ? const Center(child: CircularProgressIndicator())
          : _cart == null || _cart!.items.isEmpty
              ? const Center(child: Text('Your cart is empty.'))
              : Column(
                  children: [
                    Expanded(
                      child: ListView.separated(
                        padding: const EdgeInsets.all(12),
                        itemCount: _cart!.items.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (context, i) => _cartItemTile(_cart!.items[i]),
                      ),
                    ),
                    _checkoutBar(context),
                  ],
                ),
    );
  }

  Color _colorFor(String name) {
    final hash = name.codeUnits.fold<int>(0, (a, c) => a + c);
    const palette = [
      Color(0xFF0F766E), Color(0xFF0E7A6B), Color(0xFF1D4ED8), Color(0xFF7C3AED),
      Color(0xFFDB2777), Color(0xFFEA580C), Color(0xFFCA8A04), Color(0xFF16A34A),
      Color(0xFF0891B2), Color(0xFF475569),
    ];
    return palette[hash % palette.length];
  }

  String _initials(String name) {
    final parts = name.trim().split(RegExp(r'\s+'));
    if (parts.length == 1) return parts[0].substring(0, parts[0].length >= 2 ? 2 : 1).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }

  Widget _coloredThumb(String name, String? url) {
    final bg = _colorFor(name);
    // If has real image, try to show it with colored fallback
    return Container(
      width: 72, height: 72,
      decoration: BoxDecoration(color: bg.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10), border: Border.all(color: bg.withValues(alpha: 0.18))),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(10),
        child: Stack(alignment: Alignment.center, children: [
          // Colored initials background
          Container(color: bg, child: Center(child: Text(_initials(name), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18)))),
          // Try image on top, fallback to initials if fails
          if (url != null && url.isNotEmpty)
            Positioned.fill(child: _imageWithFallback(url, bg, name)),
        ]),
      ),
    );
  }

  Widget _imageWithFallback(String url, Color bg, String name) {
    final src = AppConfig.storageUrl(url);
    return Image.network(src, fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Container(color: bg, child: Center(child: Text(_initials(name), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18)))),
    );
  }

  Widget _cartItemTile(CartItem item) {
    final c = _colorFor(item.product.name);
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border(left: BorderSide(color: c, width: 4), top: BorderSide(color: AppColors.border), right: BorderSide(color: AppColors.border), bottom: BorderSide(color: AppColors.border)),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      padding: const EdgeInsets.fromLTRB(6, 10, 10, 10),
      child: Row(children: [
        Checkbox(value: _selected.contains(item.id), activeColor: AppColors.primary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)), onChanged: (v) => setState(() { if (v == true) _selected.add(item.id); else _selected.remove(item.id); })),
        GestureDetector(
          onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ProductDetailScreen(productId: item.productId))),
          child: _coloredThumb(item.product.name, item.product.image),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Container(padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3), decoration: BoxDecoration(color: c.withValues(alpha: 0.10), borderRadius: BorderRadius.circular(999), border: Border.all(color: c.withValues(alpha: 0.18))), child: Text((item.product.brand?.isNotEmpty == true ? item.product.brand! : 'Invoiz'), style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: c))),
            const SizedBox(width: 6),
            Expanded(child: Text(item.product.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, letterSpacing: -0.1))),
          ]),
          const SizedBox(height: 4),
          Text(item.product.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
          if (item.variantLabel.isNotEmpty) Padding(padding: const EdgeInsets.only(top: 2), child: Container(padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2), decoration: BoxDecoration(color: AppColors.surfaceSoft, borderRadius: BorderRadius.circular(6)), child: Text(item.variantLabel, style: TextStyle(fontSize: 11, color: AppColors.textSecondary)))),
          const SizedBox(height: 8),
          Row(children: [
            Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: c, borderRadius: BorderRadius.circular(999)), child: Text(_fmt(item.unitPrice), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 12))),
            const Spacer(),
            _qtyCtrl(item),
          ]),
        ])),
      ]),
    );
  }

  Widget _qtyCtrl(CartItem item) {
    return Row(
      children: [
        InkWell(
          onTap: () => _updateQty(item, item.quantity - 1),
          child: Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(border: Border.all(color: AppColors.border)),
            child: const Icon(Icons.remove, size: 16),
          ),
        ),
        Container(
          width: 34,
          height: 28,
          alignment: Alignment.center,
          decoration: BoxDecoration(border: Border.all(color: AppColors.border)),
          child: Text('${item.quantity}', style: const TextStyle(fontSize: 13)),
        ),
        InkWell(
          onTap: () => _updateQty(item, item.quantity + 1),
          child: Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(border: Border.all(color: AppColors.border)),
            child: const Icon(Icons.add, size: 16),
          ),
        ),
        IconButton(
          icon: Icon(Icons.delete_outline, size: 20, color: AppColors.textSecondary),
          onPressed: () => _remove(item),
        ),
      ],
    );
  }

  Widget _checkoutBar(BuildContext context) {
    return Container(
      color: AppColors.card,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: SafeArea(
        child: Row(
          children: [
            Text('Total: ', style: const TextStyle(fontSize: 14)),
            Text(
              _fmt(_selectedSubtotal),
              style: TextStyle(color: AppColors.primary, fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const Spacer(),
            ElevatedButton(
              onPressed: _selected.isEmpty
                  ? null
                  : () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                      ),
              style: ElevatedButton.styleFrom(minimumSize: const Size(140, 44)),
              child: const Text('Checkout'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _image(String? url) {
    if (url == null || url.isEmpty) return const Icon(Icons.image_not_supported_outlined, color: Colors.grey, size: 24);
    final src = AppConfig.storageUrl(url);
    return Image.network(src, fit: BoxFit.cover,
        errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported_outlined, color: Colors.grey, size: 24));
  }

  String _fmt(double v) => '₱${v.toStringAsFixed(2)}';
}
