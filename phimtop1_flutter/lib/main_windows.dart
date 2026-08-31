import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart' show ThemeMode;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import 'package:media_kit/media_kit.dart';
import 'package:firebase_core/firebase_core.dart';

import 'firebase_options.dart';
import 'providers/home_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/theme_provider.dart';
import 'providers/detail_provider.dart';
import 'models/movie.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  MediaKit.ensureInitialized();
  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
  } catch (e) {
    debugPrint("Firebase init error: $e");
  }

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => HomeProvider()),
        ChangeNotifierProvider(create: (_) => DetailProvider()),
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
      ],
      child: const MyWindowsApp(),
    ),
  );
}

class MyWindowsApp extends StatelessWidget {
  const MyWindowsApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final themeMode = context.watch<ThemeProvider>().themeMode;
    return FluentApp(
      title: 'PhimTop1',
      themeMode: themeMode,
      theme: FluentThemeData(brightness: Brightness.light),
      darkTheme: FluentThemeData(brightness: Brightness.dark),
      home: const WindowsHomeLayout(),
      debugShowCheckedModeBanner: false,
    );
  }
}

class WindowsHomeLayout extends StatefulWidget {
  const WindowsHomeLayout({Key? key}) : super(key: key);

  @override
  State<WindowsHomeLayout> createState() => _WindowsHomeLayoutState();
}

class _WindowsHomeLayoutState extends State<WindowsHomeLayout> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    return NavigationView(
      appBar: const NavigationAppBar(
        title: Text('PhimTop1', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        automaticallyImplyLeading: false,
      ),
      pane: NavigationPane(
        selected: _currentIndex,
        onChanged: (i) => setState(() => _currentIndex = i),
        displayMode: PaneDisplayMode.open,
        items: [
          PaneItem(
            icon: const Icon(FluentIcons.home),
            title: const Text('Trang Chủ'),
            body: const WindowsHomeScreen(),
          ),
          PaneItem(
            icon: const Icon(FluentIcons.search),
            title: const Text('Tìm Kiếm'),
            body: const Center(child: Text('Tìm Kiếm (Đang phát triển cho Desktop)')),
          ),
          PaneItem(
            icon: const Icon(FluentIcons.library),
            title: const Text('Tủ Phim'),
            body: const Center(child: Text('Tủ Phim (Đang phát triển cho Desktop)')),
          ),
        ],
      ),
    );
  }
}

class WindowsHomeScreen extends StatefulWidget {
  const WindowsHomeScreen({Key? key}) : super(key: key);

  @override
  State<WindowsHomeScreen> createState() => _WindowsHomeScreenState();
}

class _WindowsHomeScreenState extends State<WindowsHomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<HomeProvider>().fetchHomeData();
    });
  }

  @override
  Widget build(BuildContext context) {
    final homeProvider = context.watch<HomeProvider>();

    if (homeProvider.isLoading && homeProvider.latestMovies.isEmpty) {
      return const Center(child: ProgressRing());
    }

    return ScaffoldPage.scrollable(
      header: const PageHeader(title: Text('Phim Mới Cập Nhật')),
      children: [
        Wrap(
          spacing: 16,
          runSpacing: 16,
          children: homeProvider.latestMovies.map((movie) => _buildMovieCard(context, movie)).toList(),
        ),
      ],
    );
  }

  Widget _buildMovieCard(BuildContext context, Movie movie) {
    return HoverButton(
      onPressed: () {
        showDialog(
          context: context,
          builder: (ctx) => ContentDialog(
            title: Text(movie.name),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(movie.originName),
                const SizedBox(height: 10),
                Text('Năm: ${movie.year}'),
              ],
            ),
            actions: [
              Button(
                child: const Text('Đóng'),
                onPressed: () => Navigator.pop(ctx),
              ),
            ],
          ),
        );
      },
      builder: (context, states) {
        return Container(
          width: 150,
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: states.isHovered ? FluentTheme.of(context).cardColor : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.network(
                  movie.getPosterUrl(),
                  width: 150,
                  height: 220,
                  fit: BoxFit.cover,
                  errorBuilder: (c, e, s) => const Icon(FluentIcons.error, size: 50),
                ),
              ),
              const SizedBox(height: 8),
              Text(movie.name, style: const TextStyle(fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis),
              Text(movie.year.toString(), style: TextStyle(color: FluentTheme.of(context).resources.textFillColorTertiary)),
            ],
          ),
        );
      },
    );
  }
}
