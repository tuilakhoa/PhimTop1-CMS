import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter/foundation.dart';
import 'package:provider/provider.dart';
import 'package:firebase_core/firebase_core.dart';
import 'core/theme.dart';
import 'core/router.dart';
import 'providers/home_provider.dart';

import 'providers/detail_provider.dart';
import 'providers/trending_provider.dart';
import 'providers/explore_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/playlist_provider.dart';
import 'providers/download_provider.dart';
import 'providers/theme_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:path_provider/path_provider.dart';
import 'package:flutter_cache_manager/flutter_cache_manager.dart';
import 'dart:io';
import 'services/tv_remote_service.dart';
import 'package:go_router/go_router.dart';
import 'package:app_links/app_links.dart';
import 'dart:async';

import 'firebase_options.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
  } catch (e) {
    print("Firebase init error (ignored on Linux): $e");
  }

  LicenseRegistry.addLicense(() async* {
    try {
      final license = await rootBundle.loadString('assets/LICENSE');
      yield LicenseEntryWithLineBreaks(['PhimTop1 (Mã nguồn mở)'], license);
    } catch (e) {
      print("Could not load license: $e");
    }
  });
  
  final prefs = await SharedPreferences.getInstance();
  final hasAgreed = prefs.getBool('has_agreed_terms') ?? false;
  final hasSeenOnboarding = prefs.getBool('has_seen_onboarding') ?? false;
  final hasAppLock = prefs.getString('app_lock_pin') != null;

  // Auto clear cache
  final autoClearDays = prefs.getInt('auto_clear_cache_days') ?? 0;
  if (autoClearDays > 0) {
    final lastClear = prefs.getInt('last_cache_clear_time') ?? 0;
    final now = DateTime.now().millisecondsSinceEpoch;
    final msInDay = 86400000; // 24 * 60 * 60 * 1000
    if (now - lastClear >= autoClearDays * msInDay) {
      try {
        final tempDir = await getTemporaryDirectory();
        if (await tempDir.exists()) {
          for (var entity in tempDir.listSync()) {
            if (entity is File) await entity.delete();
            else if (entity is Directory) await entity.delete(recursive: true);
          }
        }
        await DefaultCacheManager().emptyCache();
        await prefs.setInt('last_cache_clear_time', now);
        print("Auto cleared cache after $autoClearDays days.");
      } catch (e) {
        print("Auto clear cache error: $e");
      }
    }
  }

  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => HomeProvider()),
        ChangeNotifierProvider(create: (_) => DetailProvider()),
        ChangeNotifierProvider(create: (_) => TrendingProvider()),
        ChangeNotifierProvider(create: (_) => ExploreProvider()),
        ChangeNotifierProvider(create: (_) => DownloadProvider()),
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
        ChangeNotifierProxyProvider<AuthProvider, PlaylistProvider>(
          create: (context) => PlaylistProvider(authProvider: context.read<AuthProvider>()),
          update: (context, auth, previous) {
            final provider = previous ?? PlaylistProvider(authProvider: auth);
            if (auth.token != null && provider.playlists.isEmpty && !provider.isLoading) {
              provider.fetchPlaylists();
            }
            return provider;
          },
        ),
      ],
      child: MyApp(hasAgreed: hasAgreed, hasSeenOnboarding: hasSeenOnboarding, hasAppLock: hasAppLock),
    ),
  );
}

class MyApp extends StatefulWidget {
  final bool hasAgreed;
  final bool hasSeenOnboarding;
  final bool hasAppLock;
  const MyApp({super.key, required this.hasAgreed, required this.hasSeenOnboarding, required this.hasAppLock});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  late final GoRouter _router;
  late AppLinks _appLinks;
  StreamSubscription<Uri>? _linkSubscription;

  @override
  void initState() {
    super.initState();
    _router = createRouter(widget.hasAgreed, widget.hasSeenOnboarding, widget.hasAppLock);
    
    _initDeepLinks();
    
    // Listen for cast commands
    TvRemoteService().onPlayCommand.listen((slug) {
      if (mounted) {
        _router.push('/movie/$slug');
      }
    });

    TvRemoteService().onPlayDirectCommand.listen((data) {
      if (mounted) {
        _router.push('/watch_direct', extra: data);
      }
    });
  }

  void _initDeepLinks() {
    _appLinks = AppLinks();
    _linkSubscription = _appLinks.uriLinkStream.listen((uri) {
      if (mounted) {
        if (uri.scheme == 'phimtop1' && uri.host == 'movie' && uri.pathSegments.isNotEmpty) {
          final slug = uri.pathSegments.first;
          _router.push('/movie/$slug');
        }
      }
    });
  }

  @override
  void dispose() {
    _linkSubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<ThemeProvider>(
      builder: (context, themeProvider, child) {
        return MaterialApp.router(
          title: 'PhimTop1',
          theme: AppTheme.getTheme(brightness: Brightness.light, skin: themeProvider.currentSkin),
          darkTheme: AppTheme.getTheme(brightness: Brightness.dark, skin: themeProvider.currentSkin),
          themeMode: themeProvider.themeMode,
          routerConfig: _router,
          debugShowCheckedModeBanner: false,
        );
      }
    );
  }
}
