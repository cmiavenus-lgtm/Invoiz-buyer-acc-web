import 'package:flutter/material.dart';

// Copied design from C:\Users\admin\OneDrive\Desktop\invoiz\backend\resources\views\admin\layout.blade.php
// — Invoiz original: primary #16697A, background #F7F6F2, card #FFFFFF, 270px sidebar, 64px topbar

import '../screens/account_screen.dart';
import '../screens/cart_screen.dart';
import '../screens/chat_screen.dart';
import '../screens/favorites_screen.dart';
import '../screens/home_screen.dart';
import '../screens/login_screen.dart';
import '../screens/notifications_screen.dart';
import '../screens/orders_screen.dart';
import '../screens/product_list_screen.dart';
import '../screens/register_screen.dart';
import '../screens/seller_apply_screen.dart';
import '../screens/seller_home_screen.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../theme.dart';
import 'auth_service_provider.dart';
import 'invoiz_logo.dart';

class MainLayout extends StatelessWidget {
  final Widget child;
  final String title;
  final bool showAppBar;
  const MainLayout({super.key, required this.child, this.title = '', this.showAppBar = true});

  @override
  Widget build(BuildContext context) {
    if (!showAppBar) {
      return Scaffold(backgroundColor: AppColors.background, body: Center(child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 1280), child: child)));
    }
    // Website: sidebar 270 + topbar 64 + content (original invoiz design)
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: _buildTopBar(context),
      body: Row(children: [
        Container(
          width: 270,
          decoration: BoxDecoration(color: AppColors.card, border: Border(right: BorderSide(color: AppColors.border))),
          child: _SidebarOriginal(isDesktop: true),
        ),
        Expanded(
          child: Column(children: [
            Expanded(child: Center(child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 1280), child: child))),
            _WebsiteFooterOriginal(),
          ]),
        ),
      ]),
    );
  }

  PreferredSizeWidget _buildTopBar(BuildContext context) => AppBar(
        toolbarHeight: 64,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: const Color(0x0A101827),
        backgroundColor: AppColors.card,
        surfaceTintColor: Colors.transparent,
        titleSpacing: 14,
        title: Row(children: [
          Container(width: 40, height: 40, decoration: BoxDecoration(color: Colors.white, border: Border.all(color: AppColors.border), borderRadius: BorderRadius.circular(10)), child: const Icon(Icons.menu, size: 20, color: Color(0xFF16697A))),
          const SizedBox(width: 14),
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title.isEmpty ? 'Invoiz' : title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: -0.3)), Text(title.isEmpty ? 'Desktop Website • Buyer' : 'Invoiz Desktop', style: TextStyle(fontSize: 12, color: AppColors.textSecondary))]),
          const Spacer(),
          Builder(builder: (c) => IconButton(icon: const Icon(Icons.shopping_cart_outlined), onPressed: () => Navigator.push(c, MaterialPageRoute(builder: (_) => const CartScreen())))),
          const _NotificationBell(),
          const SizedBox(width: 8),
          _DesktopAuthAction(),
          const SizedBox(width: 8),
        ]),
        automaticallyImplyLeading: false,
      );
}

// Original sidebar — 270px, brand 42px, nav-link 13.5px, as in invoiz layout.blade.php
class _SidebarOriginal extends StatelessWidget {
  final bool isDesktop;
  const _SidebarOriginal({required this.isDesktop});
  @override
  Widget build(BuildContext context) {
    final auth = AuthServiceProvider.of(context);
    final isLoggedIn = auth.isLoggedIn;
    void go(Widget s) => Navigator.push(context, MaterialPageRoute(builder: (_) => s));
    return Column(children: [
      // Account
      Container(
        padding: const EdgeInsets.fromLTRB(14, 14, 14, 16),
        decoration: BoxDecoration(border: Border(bottom: BorderSide(color: AppColors.surfaceSoft))),
        child: Row(children: [
          Container(width: 42, height: 42, decoration: BoxDecoration(shape: BoxShape.circle, gradient: LinearGradient(colors: [AppColors.primary, AppColors.primaryDark])), child: Center(child: Text((auth.user?.firstName.isNotEmpty == true ? auth.user!.firstName[0] : 'B').toUpperCase(), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18)))),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isLoggedIn ? '${auth.user?.firstName ?? ''} ${auth.user?.lastName ?? ''}' : 'Guest', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800), maxLines: 1, overflow: TextOverflow.ellipsis), Text(isLoggedIn ? (auth.user?.email ?? '') : 'Browse freely', style: TextStyle(fontSize: 11, color: AppColors.textSecondary), maxLines: 1, overflow: TextOverflow.ellipsis)])),
        ]),
      ),
      // Brand
      Container(
        padding: const EdgeInsets.fromLTRB(14, 14, 14, 18),
        decoration: BoxDecoration(border: Border(bottom: BorderSide(color: AppColors.surfaceSoft))),
        child: Row(children: [
          ClipRRect(borderRadius: BorderRadius.circular(12), child: Image.asset('assets/logo.png', width: 42, height: 42, fit: BoxFit.cover, errorBuilder: (_,__,___) => Container(width: 42, height: 42, decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(12)), child: const Center(child: Text('I', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 20)))))),
          const SizedBox(width: 12),
          const Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Invoiz', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, letterSpacing: -0.4, color: Color(0xFF0E4A57))), Text('DESKTOP STORE', style: TextStyle(fontSize: 10, letterSpacing: 1.6, fontWeight: FontWeight.w700, color: Color(0xFF6E6E73)))]),
        ]),
      ),
      Expanded(
        child: ListView(padding: const EdgeInsets.fromLTRB(14, 12, 14, 0), children: [
          _NavHead('Overview'),
          _NavItemOriginal(icon: Icons.dashboard_outlined, label: 'Home', onTap: () => Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const HomeScreen()), (r) => false)),
          _NavHead('Shopping'),
          _NavItemOriginal(icon: Icons.category_outlined, label: 'Categories', onTap: () => go(const ProductListScreen())),
          _NavItemOriginal(icon: Icons.favorite_outline, label: 'Favorites', onTap: () => go(const FavoritesScreen())),
          if (isLoggedIn) ...[
            _NavHead('My Account'),
            _NavItemOriginal(icon: Icons.shopping_cart_outlined, label: 'My Cart', onTap: () => go(const CartScreen())),
            _NavItemOriginal(icon: Icons.receipt_long_outlined, label: 'My Orders', onTap: () => go(const OrdersScreen())),
            _NavItemOriginal(icon: Icons.chat_outlined, label: 'Messages', onTap: () => go(const ChatScreen())),
            _NavItemOriginal(icon: Icons.notifications_outlined, label: 'Notifications', onTap: () => go(const NotificationsScreen())),
            _NavItemOriginal(icon: Icons.person_outline, label: 'Account', onTap: () => go(const AccountScreen())),
            _NavHead('Seller'),
            if (auth.canActAsSeller) _NavItemOriginal(icon: Icons.storefront, label: 'Seller Center', onTap: () => go(const SellerHomeScreen())) else _NavItemOriginal(icon: Icons.storefront_outlined, label: auth.user?.seller != null ? 'Seller: ${auth.user!.seller!.approvalStatus}' : 'Apply as Seller', onTap: () => go(const SellerApplyScreen())),
          ] else ...[
            _NavHead('My Account'),
            _NavItemOriginal(icon: Icons.lock_outline, label: 'Sign in to order', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()))),
          ],
        ]),
      ),
      Container(
        padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
        decoration: BoxDecoration(border: Border(top: BorderSide(color: AppColors.surfaceSoft))),
        child: Column(children: [
          if (isLoggedIn)
            SizedBox(width: double.infinity, child: ElevatedButton.icon(onPressed: () { auth.logout(); Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const HomeScreen()), (r) => false); }, icon: const Icon(Icons.logout, size: 17), label: const Text('Logout'), style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFFDECEC), foregroundColor: const Color(0xFFB3261E), elevation: 0, padding: const EdgeInsets.symmetric(vertical: 10), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))))),
          const SizedBox(height: 10),
          Text('Invoiz • Website v1.0', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
        ]),
      ),
    ]);
  }
}

class _NavHead extends StatelessWidget {
  final String text; const _NavHead(this.text);
  @override
  Widget build(BuildContext context) => Padding(padding: const EdgeInsets.fromLTRB(10, 14, 10, 6), child: Text(text.toUpperCase(), style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 1.8, color: Color(0xFF9CA3AF))));
}
class _NavItemOriginal extends StatelessWidget {
  final IconData icon; final String label; final VoidCallback onTap;
  const _NavItemOriginal({required this.icon, required this.label, required this.onTap});
  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 2),
        child: Material(color: Colors.transparent, borderRadius: BorderRadius.circular(10), child: InkWell(borderRadius: BorderRadius.circular(10), onTap: onTap, child: Padding(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9), child: Row(children: [Icon(icon, size: 17, color: const Color(0xFF9CA3AF)), const SizedBox(width: 12), Expanded(child: Text(label, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: Color(0xFF4B5563))))])))),
      );
}
class _WebsiteFooterOriginal extends StatelessWidget {
  @override
  Widget build(BuildContext context) => Container(padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20), decoration: BoxDecoration(color: AppColors.card, border: Border(top: BorderSide(color: AppColors.border))), child: Center(child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 1280), child: Row(children: [const Text('Invoiz © 2026 • Desktop Website • Buyer mode', style: TextStyle(fontSize: 11, color: Color(0xFF6E6E73))), const Spacer(), Text('Privacy • Terms • Help', style: TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w600))]))));
}
class _GuestAction extends StatelessWidget {
  final IconData icon; final String label; final Color color; final Color background; final VoidCallback onTap;
  const _GuestAction({required this.icon, required this.label, required this.color, required this.background, required this.onTap});
  @override
  Widget build(BuildContext context) => Material(color: background, borderRadius: BorderRadius.circular(12), child: InkWell(borderRadius: BorderRadius.circular(12), onTap: onTap, child: Padding(padding: const EdgeInsets.symmetric(vertical: 10), child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(icon, color: color, size: 18), const SizedBox(width: 6), Text(label, style: TextStyle(color: color, fontSize: 14, fontWeight: FontWeight.w700))]))));
}
class _DesktopAuthAction extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final auth = AuthServiceProvider.of(context);
    if (auth.isLoggedIn) {
      return Row(children: [
        CircleAvatar(radius: 16, backgroundColor: AppColors.primary, child: Text((auth.user?.firstName.isNotEmpty == true ? auth.user!.firstName[0] : '?').toUpperCase(), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12))),
        const SizedBox(width: 8),
        Text('${auth.user?.firstName ?? ''}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
        PopupMenuButton<String>(icon: const Icon(Icons.arrow_drop_down, size: 20), onSelected: (v) { if (v == 'account') Navigator.push(context, MaterialPageRoute(builder: (_) => const AccountScreen())); if (v == 'orders') Navigator.push(context, MaterialPageRoute(builder: (_) => const OrdersScreen())); if (v == 'logout') { auth.logout(); Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const HomeScreen()), (r) => false); } }, itemBuilder: (_) => const [PopupMenuItem(value: 'account', child: Text('My Account')), PopupMenuItem(value: 'orders', child: Text('My Orders')), PopupMenuItem(value: 'logout', child: Text('Logout'))]),
      ]);
    }
    return Row(children: [OutlinedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())), child: const Text('Login')), const SizedBox(width: 8), ElevatedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RegisterScreen())), child: const Text('Register'))]);
  }
}
class Sidebar extends StatelessWidget {
  const Sidebar({super.key});
  @override
  Widget build(BuildContext context) => Drawer(width: 290, shape: const RoundedRectangleBorder(), child: _SidebarOriginal(isDesktop: false));
}
class _NotificationBell extends StatefulWidget {
  const _NotificationBell();
  @override
  State<_NotificationBell> createState() => _NotificationBellState();
}
class _NotificationBellState extends State<_NotificationBell> {
  final _api = ApiService(); int _unread = 0;
  @override
  void initState() { super.initState(); _load(); }
  Future<void> _load() async { try { final data = await _api.get('notifications'); if (!mounted) return; final items = (data['notifications'] as List).cast<Map<String, dynamic>>(); setState(() => _unread = items.where((n) => n['read'] == false).length); } catch (_) {} }
  @override
  Widget build(BuildContext context) {
    final auth = AuthServiceProvider.of(context);
    if (!auth.isLoggedIn) return const SizedBox.shrink();
    return Stack(clipBehavior: Clip.none, children: [IconButton(icon: const Icon(Icons.notifications_outlined), onPressed: () async { await Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationsScreen())); _load(); }), if (_unread > 0) Positioned(right: 4, top: 6, child: Container(padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1), decoration: BoxDecoration(color: AppColors.warning, borderRadius: BorderRadius.circular(999), border: Border.all(color: Colors.white, width: 1.5)), child: Text('$_unread', style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w700))))]);
  }
}
class _NavItem extends StatelessWidget {
  final IconData icon; final String label; final VoidCallback onTap; final bool destructive;
  const _NavItem({required this.icon, required this.label, required this.onTap, this.destructive = false});
  @override
  Widget build(BuildContext context) {
    final color = destructive ? AppColors.warning : AppColors.textPrimary;
    return ListTile(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)), leading: Icon(icon, color: color, size: 22), title: Text(label, style: TextStyle(fontSize: 14, color: color, fontWeight: FontWeight.w500)), onTap: onTap);
  }
}
