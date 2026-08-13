import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:firebase_core/firebase_core.dart';
import 'core/theme.dart';
import 'core/router.dart';
import 'providers/home_provider.dart';

import 'providers/detail_provider.dart';
import 'providers/explore_provider.dart';
import 'providers/auth_provider.dart';
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

  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
  ]);

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => HomeProvider()),
        ChangeNotifierProvider(create: (_) => DetailProvider()),
        ChangeNotifierProvider(create: (_) => ExploreProvider()),
      ],
      child: MyApp(hasAgreed: hasAgreed),
    ),
  );
}

class MyApp extends StatefulWidget {
  final bool hasAgreed;
  const MyApp({super.key, required this.hasAgreed});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  late final GoRouter _router;

  @override
  void initState() {
    super.initState();
    _router = createRouter(widget.hasAgreed);
    
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
    return MaterialApp.router(
      title: 'PhimTop1',
      theme: AppTheme.darkTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.dark,
      routerConfig: _router,
      debugShowCheckedModeBanner: false,
    );
  }
}
