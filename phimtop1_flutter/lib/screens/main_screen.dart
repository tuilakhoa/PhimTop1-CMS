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
                NavigationRailDestination(icon: Icon(Icons.play_circle_fill_rounded), label: Text('Shorts')),
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
        decoration: const BoxDecoration(
          border: Border(top: BorderSide(color: Colors.white10, width: 0.5)),
        ),
        child: BottomNavigationBar(
          currentIndex: _calculateSelectedIndex(context) > 4 ? 0 : _calculateSelectedIndex(context),
          onTap: (int index) => _onItemTapped(index, context),
          type: BottomNavigationBarType.fixed,
          items: const [
            BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Trang chủ'),
            BottomNavigationBarItem(icon: Icon(Icons.trending_up), label: 'BXH'),
            BottomNavigationBarItem(icon: Icon(Icons.explore), label: 'Khám phá'),
            BottomNavigationBarItem(icon: Icon(Icons.play_circle_fill_rounded), label: 'Shorts'),
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
    if (location.startsWith('/shorts')) return 3;
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
        context.go('/shorts');
        break;
      case 4:
        context.go('/profile');
        break;
    }
  }
}
