import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'tv_dashboard_screen.dart';

class MainScreen extends StatelessWidget {
  final Widget child;
  
  const MainScreen({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final bool isLandscape = MediaQuery.of(context).orientation == Orientation.landscape;
    final bool isTvMode = isLandscape && size.width > 800;

    if (isTvMode) {
      return TvDashboardScreen(child: child);
    }

    if (isLandscape) {
      return Scaffold(
        body: Row(
          children: [
            NavigationRail(
              selectedIndex: _calculateSelectedIndex(context),
              onDestinationSelected: (int index) => _onItemTapped(index, context),
              labelType: NavigationRailLabelType.all,
              destinations: const [
                NavigationRailDestination(icon: Icon(Icons.home), label: Text('Trang chủ')),
                NavigationRailDestination(icon: Icon(Icons.trending_up), label: Text('BXH')),
                NavigationRailDestination(icon: Icon(Icons.explore), label: Text('Khám phá')),
                NavigationRailDestination(icon: Icon(Icons.animation), label: Text('Hoạt hình')),
                NavigationRailDestination(icon: Icon(Icons.person), label: Text('Cá nhân')),
              ],
            ),
            const VerticalDivider(thickness: 1, width: 1),
            Expanded(child: child),
          ],
        ),
      );
    }

    return Scaffold(
      extendBody: true,
      body: child,
      bottomNavigationBar: SafeArea(
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.2),
                blurRadius: 10,
                spreadRadius: 2,
              )
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(24),
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 12.0, sigmaY: 12.0),
              child: Container(
                decoration: BoxDecoration(
                  color: Theme.of(context).scaffoldBackgroundColor.withOpacity(0.8),
                  border: Border.all(color: Colors.white.withOpacity(0.1), width: 0.5),
                  borderRadius: BorderRadius.circular(24),
                ),
                child: NavigationBarTheme(
                  data: NavigationBarThemeData(
                    indicatorColor: Colors.amber.withOpacity(0.2),
                    labelTextStyle: WidgetStateProperty.resolveWith((states) {
                      if (states.contains(WidgetState.selected)) {
                        return const TextStyle(color: Colors.amber, fontSize: 12, fontWeight: FontWeight.bold);
                      }
                      return TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 12);
                    }),
                    iconTheme: WidgetStateProperty.resolveWith((states) {
                      if (states.contains(WidgetState.selected)) {
                        return const IconThemeData(color: Colors.amber);
                      }
                      return IconThemeData(color: Colors.white.withOpacity(0.7));
                    }),
                  ),
                  child: NavigationBar(
                    height: 64,
                    elevation: 0,
                    backgroundColor: Colors.transparent,
                    selectedIndex: _calculateSelectedIndex(context) > 4 ? 0 : _calculateSelectedIndex(context),
                    onDestinationSelected: (int index) => _onItemTapped(index, context),
                    destinations: const [
                      NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Trang chủ'),
                      NavigationDestination(icon: Icon(Icons.trending_up_outlined), selectedIcon: Icon(Icons.trending_up), label: 'BXH'),
                      NavigationDestination(icon: Icon(Icons.explore_outlined), selectedIcon: Icon(Icons.explore), label: 'Khám phá'),
                      NavigationDestination(icon: Icon(Icons.animation_outlined), selectedIcon: Icon(Icons.animation), label: 'Hoạt hình'),
                      NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Cá nhân'),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  static int _calculateSelectedIndex(BuildContext context) {
    final String location = GoRouterState.of(context).uri.path;
    if (location.startsWith('/trending')) return 1;
    if (location.startsWith('/explore')) return 2;
    if (location.startsWith('/cartoon')) return 3;
    if (location.startsWith('/profile')) return 4;
    if (location.startsWith('/search')) return 5;
    return 0; // home
  }

  void _onItemTapped(int index, BuildContext context) {
    switch (index) {
      case 0:
        context.go('/');
        break;
      case 1:
        context.go('/trending');
        break;
      case 2:
        context.go('/explore');
        break;
      case 3:
        context.go('/cartoon');
        break;
      case 4:
        context.go('/profile');
        break;
    }
  }
}
