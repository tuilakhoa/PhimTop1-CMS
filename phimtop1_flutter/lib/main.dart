import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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
import 'services/tv_remote_service.dart';
import 'package:go_router/go_router.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp();
  } catch (e) {
    print("Firebase init error (ignored on Linux): $e");
  }
  
  final prefs = await SharedPreferences.getInstance();
  final hasAgreed = prefs.getBool('has_agreed_terms') ?? false;
  final hasSeenOnboarding = prefs.getBool('has_seen_onboarding') ?? false;
  final hasAppLock = prefs.getString('app_lock_pin') != null;

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

  @override
  void initState() {
    super.initState();
    _router = createRouter(widget.hasAgreed, widget.hasSeenOnboarding, widget.hasAppLock);
    
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
