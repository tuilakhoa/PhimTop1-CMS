import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter/foundation.dart';
import 'dart:io';
import 'tv_dashboard_screen.dart';
import 'fluent_desktop_shell.dart';

class MainScreen extends StatelessWidget {
  final Widget child;
  
  const MainScreen({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final bool isLandscape = MediaQuery.of(context).orientation == Orientation.landscape;
    final bool isTvMode = isLandscape && size.width > 800;
    
    final bool isDesktop = !kIsWeb && (Platform.isWindows || Platform.isLinux);

    if (isDesktop) {
      return FluentDesktopShell(child: child);
    }

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
      body: child,
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          border: Border(top: BorderSide(color: Theme.of(context).dividerColor.withOpacity(0.1), width: 0.5)),
        ),
        child: BottomNavigationBar(
          currentIndex: _calculateSelectedIndex(context) > 4 ? 0 : _calculateSelectedIndex(context),
          onTap: (int index) => _onItemTapped(index, context),
          type: BottomNavigationBarType.fixed,
          items: const [
            BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Trang chủ'),
            BottomNavigationBarItem(icon: Icon(Icons.trending_up), label: 'BXH'),
            BottomNavigationBarItem(icon: Icon(Icons.explore), label: 'Khám phá'),
            BottomNavigationBarItem(icon: Icon(Icons.animation), label: 'Hoạt hình'),
            BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Cá nhân'),
          ],
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
