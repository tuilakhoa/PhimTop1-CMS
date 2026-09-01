import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter/foundation.dart';
import 'package:provider/provider.dart';
import 'package:media_kit/media_kit.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
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

import 'package:system_theme/system_theme.dart';
import 'package:window_manager/window_manager.dart';
import 'package:flutter_acrylic/flutter_acrylic.dart';
import 'package:tray_manager/tray_manager.dart';
import 'firebase_options.dart';

import 'providers/watch_party_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  MediaKit.ensureInitialized();
  
  try {
    if (!kIsWeb && (Platform.isWindows || Platform.isAndroid || Platform.isLinux)) {
      SystemTheme.fallbackColor = Colors.blue;
      await SystemTheme.accentColor.load();
    }
  } catch (e) {
    print("SystemTheme init error: $e");
  }

  if (!kIsWeb && (Platform.isWindows || Platform.isLinux)) {
    try {
      await windowManager.ensureInitialized();
      WindowOptions windowOptions = const WindowOptions(
        size: Size(1280, 720),
        center: true,
        backgroundColor: Colors.transparent,
        skipTaskbar: false,
        titleBarStyle: TitleBarStyle.hidden,
        backgroundColor: Colors.transparent,
      );
      await windowManager.setPreventClose(true);
      windowManager.waitUntilReadyToShow(windowOptions, () async {
        await windowManager.show();
        await windowManager.focus();
      });

      await Window.initialize();
      if (Platform.isWindows) {
        await Window.setEffect(
          effect: WindowEffect.mica,
          color: const Color(0xCC222222),
        );
      } else if (Platform.isLinux) {
        await Window.setEffect(
          effect: WindowEffect.transparent,
          color: const Color(0xCC222222),
        );
      }

      // Initialize tray
      await trayManager.setIcon(
        Platform.isWindows ? 'assets/logo.ico' : 'assets/logo.png',
      );
      Menu menu = Menu(
        items: [
          MenuItem(key: 'show_window', label: 'Show Window'),
          MenuItem.separator(),
          MenuItem(key: 'exit_app', label: 'Exit'),
        ],
      );
      await trayManager.setContextMenu(menu);

    } catch (e) {
      print("Window manager/acrylic init error: $e");
    }
  }

  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    
    // Request permission for push notifications
    FirebaseMessaging messaging = FirebaseMessaging.instance;
    await messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
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
  final hasAppLock = prefs.getString('app_lock_pin') != null || prefs.getBool('app_lock_biometric') == true;

  // Auto clear cache
  final autoClearDays = prefs.getInt('auto_clear_cache_days') ?? 0;
  if (autoClearDays > 0) {
    final lastClear = prefs.getInt('last_cache_clear_time') ?? 0;
    final now = DateTime.now().millisecondsSinceEpoch;
    final msInDay = 86400000; // 24 * 60 * 60 * 1000
    if (now - lastClear >= autoClearDays * msInDay) {
      try {
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
        ChangeNotifierProvider(create: (_) => WatchPartyProvider()),
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

class _MyAppState extends State<MyApp> with TrayListener, WindowListener {
  late final GoRouter _router;
  late AppLinks _appLinks;
  StreamSubscription<Uri>? _linkSubscription;

  @override
  void initState() {
    super.initState();
    _router = createRouter(widget.hasAgreed, widget.hasSeenOnboarding, widget.hasAppLock);
    
    _initDeepLinks();
    
    if (!kIsWeb && (Platform.isWindows || Platform.isLinux)) {
      trayManager.addListener(this);
      windowManager.addListener(this);
    }

    
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
    if (!kIsWeb && (Platform.isWindows || Platform.isLinux)) {
      trayManager.removeListener(this);
      windowManager.removeListener(this);
    }
    super.dispose();
  }


  @override
  void onTrayIconMouseDown() {
    windowManager.show();
    windowManager.focus();
  }

  @override
  void onTrayIconRightMouseDown() {
    trayManager.popUpContextMenu();
  }

  @override
  void onTrayMenuItemClick(MenuItem menuItem) {
    if (menuItem.key == 'show_window') {
      windowManager.show();
      windowManager.focus();
    } else if (menuItem.key == 'exit_app') {
      windowManager.destroy();
    }
  }

  @override
  void onWindowClose() async {
    final prefs = await SharedPreferences.getInstance();
    bool minimize = prefs.getBool('minimize_to_tray_on_close') ?? false;

    if (minimize) {
      bool isPreventClose = await windowManager.isPreventClose();
      if (isPreventClose) {
        windowManager.hide();
      }
    } else {
      windowManager.destroy();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<ThemeProvider>(
      builder: (context, themeProvider, child) {
        return MaterialApp.router(
          title: 'PhimTop1',
          theme: AppTheme.getTheme(brightness: Brightness.light, skin: themeProvider.currentSkin, useSystemAccent: themeProvider.useSystemAccent),
          darkTheme: AppTheme.getTheme(brightness: Brightness.dark, skin: themeProvider.currentSkin, useSystemAccent: themeProvider.useSystemAccent),
          themeMode: themeProvider.themeMode,

          builder: (context, child) {
            if (!kIsWeb && (Platform.isWindows || Platform.isLinux)) {
              return Scaffold(
                backgroundColor: Colors.transparent,
                body: Column(
                  children: [
                    const WindowCaption(
                      brightness: Brightness.dark,
                      backgroundColor: Colors.transparent,
                      title: Text('PhimTop1'),
                    ),
                    Expanded(child: child ?? const SizedBox()),
                  ],
                ),
              );
            }
            return child ?? const SizedBox();
          },
          routerConfig: _router,

          debugShowCheckedModeBanner: false,
        );
      }
    );
  }
}
