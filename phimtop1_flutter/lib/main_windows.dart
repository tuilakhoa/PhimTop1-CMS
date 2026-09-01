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

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Top Bar
        Container(
          height: 60,
          padding: const EdgeInsets.symmetric(horizontal: 24),
          decoration: const BoxDecoration(
            color: Color(0xFF0A0A12),
            border: Border(bottom: BorderSide(color: Color(0xFF161623), width: 1)),
          ),
          child: Row(
            children: [
              const Icon(FluentIcons.play, color: Color(0xFF6B48FF), size: 24),
              const SizedBox(width: 8),
              const Text('PhimTop1', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20, color: Colors.white)),
              
              const SizedBox(width: 48), // Gap before search box
              // Search box
              SizedBox(
                width: 400,
                height: 36,
                child: TextBox(
                  placeholder: 'Tìm kiếm...',
                  prefix: const Padding(padding: EdgeInsets.only(left: 12.0, right: 8.0), child: Icon(FluentIcons.search, size: 14)),
                  suffix: const Padding(padding: EdgeInsets.only(right: 8.0), child: Icon(FluentIcons.cancel, size: 12)),
                  decoration: WidgetStateProperty.all(
                    BoxDecoration(color: const Color(0xFF161623), borderRadius: BorderRadius.circular(20))
                  ),
                  onSubmitted: (value) {
                    if (value.isNotEmpty) {
                      context.read<ExploreProvider>().setFilters(searchKeyword: value);
                      setState(() => _currentIndex = 1);
                    }
                  },
                ),
              ),
              const SizedBox(width: 12),
              // Nút Tìm kiếm
              FilledButton(
                style: ButtonStyle(
                  backgroundColor: WidgetStateProperty.all(const Color(0xFF6B48FF)),
                  padding: WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 20, vertical: 8)),
                ),
                onPressed: () {
                  setState(() => _currentIndex = 1);
                },
                child: const Text('Tìm kiếm', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
              
              const Spacer(),
              const IconButton(icon: Icon(FluentIcons.ringer, size: 16), onPressed: null),
              const SizedBox(width: 12),
              Container(
                width: 32, height: 32,
                decoration: const BoxDecoration(color: Color(0xFF6B48FF), shape: BoxShape.circle),
                alignment: Alignment.center,
                child: const Icon(FluentIcons.contact, size: 16, color: Colors.white),
              ),
            ],
          ),
        ),
        // Main Content
        Expanded(
          child: FluentTheme(
            data: FluentTheme.of(context).copyWith(
              navigationPaneTheme: const NavigationPaneThemeData(
                backgroundColor: Color(0xFF0A0A12), // Same as app background
                itemHeaderTextStyle: const TextStyle(fontSize: 10, color: Colors.grey, fontWeight: FontWeight.bold),
              ),
            ),
            child: NavigationView(
              pane: NavigationPane(
                size: const NavigationPaneSize(openWidth: 200),
                selected: _currentIndex,
                onChanged: (i) {
                  setState(() => _currentIndex = i);
                  if (i == 2) { // Phim Mới
                    context.read<ExploreProvider>().setFilters(type: 'phim-moi-cap-nhat', genre: '', country: '');
                    context.read<ExploreProvider>().fetchMovies();
                  } else if (i == 3) { // Phim Bộ
                    context.read<ExploreProvider>().setFilters(type: 'phim-bo', genre: '', country: '');
                    context.read<ExploreProvider>().fetchMovies();
                  } else if (i == 4) { // Phim Lẻ
                    context.read<ExploreProvider>().setFilters(type: 'phim-le', genre: '', country: '');
                    context.read<ExploreProvider>().fetchMovies();
                  } else if (i == 5 || i == 6 || i == 7) {
                    context.read<ExploreProvider>().fetchMovies();
                  }
                },
                header: const SizedBox.shrink(),
                items: [
                  PaneItem(
                    icon: const Icon(FluentIcons.home),
                    title: const Text('Trang Chủ', style: TextStyle(fontSize: 13)),
                    body: const WindowsHomeScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.search),
                    title: const Text('Khám Phá', style: TextStyle(fontSize: 13)),
                    body: const WindowsSearchScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.video),
                    title: const Text('Phim Mới', style: TextStyle(fontSize: 13)),
                    body: const WindowsSearchScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.play),
                    title: const Text('Phim Bộ', style: TextStyle(fontSize: 13)),
                    body: const WindowsSearchScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.my_movies_t_v),
                    title: const Text('Phim Lẻ', style: TextStyle(fontSize: 13)),
                    body: const WindowsSearchScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.list),
                    title: const Text('Thể Loại', style: TextStyle(fontSize: 13)),
                    body: const WindowsSearchScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.globe),
                    title: const Text('Quốc Gia', style: TextStyle(fontSize: 13)),
                    body: const WindowsSearchScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.medal),
                    title: const Text('Top IMDb', style: TextStyle(fontSize: 13)),
                    body: const WindowsSearchScreen(),
                  ),
                  
                  PaneItemHeader(header: const Text('DANH SÁCH CỦA TÔI')),
                  
                  PaneItem(
                    icon: const Icon(FluentIcons.heart),
                    title: const Text('Yêu Thích', style: TextStyle(fontSize: 13)),
                    body: const WindowsFollowScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.history),
                    title: const Text('Xem Sau', style: TextStyle(fontSize: 13)),
                    body: const WindowsFollowScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.recent),
                    title: const Text('Lịch Sử', style: TextStyle(fontSize: 13)),
                    body: const WindowsHistoryScreen(),
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.download),
                    title: const Text('Tải Xuống', style: TextStyle(fontSize: 13)),
                    body: const WindowsDownloadsScreen(),
                  ),
                ],
                footerItems: [
                  PaneItemAction(
                    icon: const Icon(FluentIcons.shopping_cart_solid, color: Color(0xFFFFA500)),
                    title: const Text('Cửa hàng vật phẩm', style: TextStyle(color: Color(0xFFFFA500), fontSize: 13)),
                    onTap: () {},
                  ),
                  PaneItem(
                    icon: const Icon(FluentIcons.settings),
                    title: const Text('Cài đặt', style: TextStyle(fontSize: 13)),
                    body: const WindowsSettingsScreen(),
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
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
