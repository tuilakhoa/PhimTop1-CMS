import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart' show ThemeMode, Material, MaterialApp, ThemeData;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import 'package:media_kit/media_kit.dart';
import 'package:media_kit_video/media_kit_video.dart';
import 'package:firebase_core/firebase_core.dart';

import 'firebase_options.dart';
import 'providers/home_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/theme_provider.dart';
import 'providers/detail_provider.dart';
import 'providers/explore_provider.dart';
import 'models/movie_model.dart';
import 'windows_detail_screen.dart';
import 'windows_search_screen.dart';
import 'windows_home_screen.dart';
import 'windows_history_screen.dart';
import 'windows_follow_screen.dart';
import 'windows_downloads_screen.dart';
import 'windows_settings_screen.dart';

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
        ChangeNotifierProvider(create: (_) => ExploreProvider()),
      ],
      child: const MyWindowsApp(),
    ),
  );
}

class MyWindowsApp extends StatelessWidget {
  const MyWindowsApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return FluentApp(
      title: 'PhimTop1 Windows',
      themeMode: ThemeMode.dark, // Force dark theme cho phong cách Premium
      darkTheme: FluentThemeData(
        brightness: Brightness.dark,
        scaffoldBackgroundColor: const Color(0xFF0A0A12),
        cardColor: const Color(0xFF161623),
        accentColor: Colors.purple,
      ),
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
  PaneDisplayMode _displayMode = PaneDisplayMode.compact;

  @override
  Widget build(BuildContext context) {
    return FluentTheme(
      data: FluentTheme.of(context).copyWith(
        navigationPaneTheme: const NavigationPaneThemeData(
          backgroundColor: Color(0xFF0A0A12), // Same as app background
          itemHeaderTextStyle: const TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.bold),
        ),
      ),
      child: NavigationView(
        titleBar: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
          child: Row(
            children: [
              const PaneToggleButton(),
              const SizedBox(width: 12),
              const Icon(FluentIcons.play, color: Color(0xFF6B48FF), size: 20),
              const SizedBox(width: 8),
              const Text('PhimTop1', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.white)),
              const Spacer(),
              SizedBox(
                width: 300,
                height: 32,
                child: TextBox(
                  placeholder: 'Tìm kiếm phim...',
                  prefix: const Padding(padding: EdgeInsets.only(left: 12.0, right: 8.0), child: Icon(FluentIcons.search, size: 14)),
                  decoration: WidgetStateProperty.all(
                    BoxDecoration(color: const Color(0xFF161623), borderRadius: BorderRadius.circular(16))
                  ),
                  onSubmitted: (value) {
                    if (value.isNotEmpty) {
                      context.read<ExploreProvider>().setFilters(searchKeyword: value);
                      setState(() => _currentIndex = 1);
                    }
                  },
                ),
              ),
              const SizedBox(width: 16),
              const IconButton(icon: Icon(FluentIcons.ringer, size: 16), onPressed: null),
              const SizedBox(width: 8),
              Container(
                width: 28, height: 28,
                decoration: const BoxDecoration(color: Color(0xFF6B48FF), shape: BoxShape.circle),
                alignment: Alignment.center,
                child: const Icon(FluentIcons.contact, size: 14, color: Colors.white),
              ),
            ],
          ),
        ),
        onDisplayModeChanged: (mode) => setState(() => _displayMode = mode),
        pane: NavigationPane(
          displayMode: _displayMode,
          size: const NavigationPaneSize(openWidth: 220, compactWidth: 50),
          selected: _currentIndex,
          indicator: const EndNavigationIndicator(),
          onChanged: (i) {
            setState(() => _currentIndex = i);
            if (i == 2) {
              context.read<ExploreProvider>().setFilters(type: 'phim-moi-cap-nhat', genre: '', country: '');
              context.read<ExploreProvider>().fetchMovies();
            } else if (i == 3) {
              context.read<ExploreProvider>().setFilters(type: 'phim-bo', genre: '', country: '');
              context.read<ExploreProvider>().fetchMovies();
            } else if (i == 4) {
              context.read<ExploreProvider>().setFilters(type: 'phim-le', genre: '', country: '');
              context.read<ExploreProvider>().fetchMovies();
            } else if (i == 5 || i == 6 || i == 7) {
              context.read<ExploreProvider>().fetchMovies();
            }
          },
          items: [
            PaneItem(
              icon: const Icon(FluentIcons.home),
              title: const Text('Trang Chủ', style: TextStyle(fontSize: 14)),
              body: const WindowsHomeScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.search),
              title: const Text('Khám Phá', style: TextStyle(fontSize: 14)),
              body: const WindowsSearchScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.video),
              title: const Text('Phim Mới', style: TextStyle(fontSize: 14)),
              body: const WindowsSearchScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.play),
              title: const Text('Phim Bộ', style: TextStyle(fontSize: 14)),
              body: const WindowsSearchScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.my_movies_t_v),
              title: const Text('Phim Lẻ', style: TextStyle(fontSize: 14)),
              body: const WindowsSearchScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.list),
              title: const Text('Thể Loại', style: TextStyle(fontSize: 14)),
              body: const WindowsSearchScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.globe),
              title: const Text('Quốc Gia', style: TextStyle(fontSize: 14)),
              body: const WindowsSearchScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.medal),
              title: const Text('Top IMDb', style: TextStyle(fontSize: 14)),
              body: const WindowsSearchScreen(),
            ),
            
            PaneItemHeader(header: const Text('DANH SÁCH CỦA TÔI')),
            
            PaneItem(
              icon: const Icon(FluentIcons.heart),
              title: const Text('Yêu Thích', style: TextStyle(fontSize: 14)),
              body: const WindowsFollowScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.history),
              title: const Text('Xem Sau', style: TextStyle(fontSize: 14)),
              body: const WindowsFollowScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.recent),
              title: const Text('Lịch Sử', style: TextStyle(fontSize: 14)),
              body: const WindowsHistoryScreen(),
            ),
            PaneItem(
              icon: const Icon(FluentIcons.download),
              title: const Text('Tải Xuống', style: TextStyle(fontSize: 14)),
              body: const WindowsDownloadsScreen(),
            ),
          ],
          footerItems: [
            PaneItemAction(
              icon: const Icon(FluentIcons.shopping_cart_solid, color: Color(0xFFFFA500)),
              title: const Text('Cửa hàng vật phẩm', style: TextStyle(color: Color(0xFFFFA500), fontSize: 14)),
              onTap: () {},
            ),
            PaneItem(
              icon: const Icon(FluentIcons.settings),
              title: const Text('Cài đặt', style: TextStyle(fontSize: 14)),
              body: const WindowsSettingsScreen(),
            ),
          ],
        ),
      ),
    );
  }
}

class WindowsVideoPlayerScreen extends StatefulWidget {
  final String movieSlug;
  const WindowsVideoPlayerScreen({Key? key, required this.movieSlug}) : super(key: key);

  @override
  State<WindowsVideoPlayerScreen> createState() => _WindowsVideoPlayerScreenState();
}

class _WindowsVideoPlayerScreenState extends State<WindowsVideoPlayerScreen> {
  late final Player player;
  late final VideoController controller;
  bool _isPlaying = false;
  String _error = "";

  @override
  void initState() {
    super.initState();
    player = Player();
    controller = VideoController(player);
    _loadVideo();
  }

  Future<void> _loadVideo() async {
    final detailProvider = context.read<DetailProvider>();
    
    if (detailProvider.movie?.slug != widget.movieSlug) {
      await detailProvider.fetchDetail(widget.movieSlug);
    }

    if (detailProvider.episodes.isNotEmpty) {
      final serverIndex = detailProvider.currentServerIndex;
      final episodeIndex = detailProvider.currentEpisodeIndex;
      
      if (detailProvider.episodes.length > serverIndex && 
          detailProvider.episodes[serverIndex].serverData.length > episodeIndex) {
        
        final link = detailProvider.episodes[serverIndex].serverData[episodeIndex].linkM3u8;
        if (link.isNotEmpty) {
          player.open(Media(link));
          setState(() { _isPlaying = true; });
          return;
        }
      }
    }
    setState(() { _error = "Không tìm thấy link video (M3U8) cho tập phim này."; });
  }

  @override
  void dispose() {
    player.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final detailProvider = context.watch<DetailProvider>();
    final title = detailProvider.movie?.name ?? "Đang tải...";
    
    String epName = "";
    if (detailProvider.episodes.isNotEmpty) {
      final serverIndex = detailProvider.currentServerIndex;
      final episodeIndex = detailProvider.currentEpisodeIndex;
      if (detailProvider.episodes.length > serverIndex && 
          detailProvider.episodes[serverIndex].serverData.length > episodeIndex) {
        epName = " - " + detailProvider.episodes[serverIndex].serverData[episodeIndex].name;
      }
    }

    return NavigationView(
      content: ScaffoldPage(
        padding: EdgeInsets.zero,
        content: Stack(
          children: [
            Container(color: Colors.black),
            Center(
              child: detailProvider.isLoading && !_isPlaying
                  ? const ProgressRing()
                  : _error.isNotEmpty
                      ? Text(_error, style: TextStyle(color: Colors.red, fontSize: 18))
                      : _isPlaying
                          ? Material(
                              child: Video(
                                controller: controller,
                                controls: MaterialVideoControls,
                              ),
                            )
                          : const ProgressRing(),
            ),
            
            // Top Bar Overlay
            Positioned(
              top: 0, left: 0, right: 0,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Colors.black.withOpacity(0.8), Colors.transparent],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                ),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(FluentIcons.back, color: Colors.white, size: 20),
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 16),
                    Text(
                      title + epName,
                      style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
