import 'package:flutter/material.dart';
import 'package:fluent_ui/fluent_ui.dart' as fluent;
import 'package:go_router/go_router.dart';
import 'package:window_manager/window_manager.dart';
import 'package:provider/provider.dart';
import '../providers/theme_provider.dart';

class FluentDesktopShell extends StatefulWidget {
  final Widget child;

  const FluentDesktopShell({super.key, required this.child});

  @override
  State<FluentDesktopShell> createState() => _FluentDesktopShellState();
}

class _FluentDesktopShellState extends State<FluentDesktopShell> {
  int _calculateSelectedIndex(BuildContext context) {
    final String location = GoRouterState.of(context).uri.path;
    if (location.startsWith('/trending')) return 1;
    if (location.startsWith('/explore')) return 2;
    if (location.startsWith('/cartoon')) return 3;
    if (location.startsWith('/profile')) return 4;
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

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return fluent.FluentTheme(
      data: fluent.FluentThemeData(
        brightness: isDark ? Brightness.dark : Brightness.light,
        accentColor: fluent.Colors.blue,
      ),
      child: fluent.NavigationView(
        pane: fluent.NavigationPane(
          header: DragToMoveArea(
            child: Container(
              alignment: Alignment.centerLeft,
              height: 48,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: const Text("PhimTop1", style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ),
          ),
          selected: _calculateSelectedIndex(context),
          onChanged: (index) => _onItemTapped(index, context),
          displayMode: fluent.PaneDisplayMode.auto,
          items: [
            fluent.PaneItem(
              icon: const fluent.Icon(fluent.FluentIcons.home),
              title: const Text('Trang chủ'),
              body: const SizedBox.shrink(),
            ),
            fluent.PaneItem(
              icon: const fluent.Icon(fluent.FluentIcons.chart),
              title: const Text('Bảng xếp hạng'),
              body: const SizedBox.shrink(),
            ),
            fluent.PaneItem(
              icon: const fluent.Icon(fluent.FluentIcons.explore_content),
              title: const Text('Khám phá'),
              body: const SizedBox.shrink(),
            ),
            fluent.PaneItem(
              icon: const fluent.Icon(fluent.FluentIcons.play),
              title: const Text('Hoạt hình'),
              body: const SizedBox.shrink(),
            ),
            fluent.PaneItem(
              icon: const fluent.Icon(fluent.FluentIcons.contact),
              title: const Text('Cá nhân'),
              body: const SizedBox.shrink(),
            ),
          ],
          footerItems: [
            fluent.PaneItem(
              icon: const fluent.Icon(fluent.FluentIcons.settings),
              title: const Text('Cài đặt'),
              body: const SizedBox.shrink(),
              onTap: () {
                context.push('/settings');
              }
            ),
          ],
        ),
        // Important: Wrap child with Material to provide Material contexts for Scaffold
        content: Material(
          color: Theme.of(context).scaffoldBackgroundColor,
          child: Column(
            children: [
              DragToMoveArea(
                child: SizedBox(
                  height: 40,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      fluent.Tooltip(
                        message: 'Tìm kiếm',
                        child: fluent.IconButton(
                          icon: const fluent.Icon(fluent.FluentIcons.search),
                          onPressed: () => context.push('/search'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      const WindowCaption(
                        brightness: Brightness.dark,
                        backgroundColor: Colors.transparent,
                      ),
                    ],
                  ),
                ),
              ),
              Expanded(child: widget.child),
            ],
          ),
        ),
      ),
    );
  }
}
