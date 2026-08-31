import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart' show ThemeMode, Material;
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
import 'models/movie_model.dart';

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
      title: 'PhimTop1 Windows',
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

  String _getThumb(MovieItem m) => (m.thumbUrl ?? '').startsWith('http') ? m.thumbUrl! : 'https://phimimg.com/${m.thumbUrl}';
  String _getPoster(MovieItem m) => (m.posterUrl ?? '').startsWith('http') ? m.posterUrl! : 'https://phimimg.com/${m.posterUrl}';

  @override
  Widget build(BuildContext context) {
    final homeProvider = context.watch<HomeProvider>();

    if (homeProvider.isLoading && homeProvider.items.isEmpty) {
      return const Center(child: ProgressRing());
    }

    return ScaffoldPage.scrollable(
      header: const PageHeader(title: Text('Phim Mới Cập Nhật')),
      children: [
        Wrap(
          spacing: 16,
          runSpacing: 16,
          children: homeProvider.items.map((movie) => _buildMovieCard(context, movie)).toList(),
        ),
      ],
    );
  }

  Widget _buildMovieCard(BuildContext context, MovieItem movie) {
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
                Text(movie.originName ?? ''),
                const SizedBox(height: 10),
                Text('Năm: ${movie.year ?? ''}'),
              ],
            ),
            actions: [
              Button(
                child: const Text('Đóng'),
                onPressed: () => Navigator.pop(ctx),
              ),
              FilledButton(
                child: const Text('Xem Phim'),
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.push(context, FluentPageRoute(builder: (_) => WindowsVideoPlayerScreen(movieSlug: movie.slug)));
                },
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
                  _getThumb(movie),
                  width: 150,
                  height: 220,
                  fit: BoxFit.cover,
                  errorBuilder: (c, e, s) => const Icon(FluentIcons.error, size: 50),
                ),
              ),
              const SizedBox(height: 8),
              Text(movie.name, style: const TextStyle(fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis),
              Text(movie.year?.toString() ?? '', style: TextStyle(color: FluentTheme.of(context).resources.textFillColorTertiary)),
            ],
          ),
        );
      },
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
    await detailProvider.fetchMovieDetail(widget.movieSlug);
    final data = detailProvider.movieDetailData;

    if (data?.episodes != null && data!.episodes!.isNotEmpty) {
      if (data.episodes![0].serverData.isNotEmpty) {
        final link = data.episodes![0].serverData[0].linkM3u8;
        if (link.isNotEmpty) {
          player.open(Media(link));
          setState(() { _isPlaying = true; });
          return;
        }
      }
    }
    setState(() { _error = "Không tìm thấy link video (M3U8) cho phim này."; });
  }

  @override
  void dispose() {
    player.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final detailProvider = context.watch<DetailProvider>();
    final title = detailProvider.movieDetailData?.movie?.name ?? "Đang tải...";

    return NavigationView(
      appBar: NavigationAppBar(
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
        leading: IconButton(
          icon: const Icon(FluentIcons.back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      content: ScaffoldPage(
        content: Center(
          child: detailProvider.isLoading
              ? const ProgressRing()
              : _error.isNotEmpty
                  ? Text(_error, style: const TextStyle(color: Colors.red))
                  : _isPlaying
                      ? AspectRatio(
                          aspectRatio: 16 / 9,
                          child: Material(
                            child: Video(controller: controller),
                          ),
                        )
                      : const ProgressRing(),
        ),
      ),
    );
  }
}
