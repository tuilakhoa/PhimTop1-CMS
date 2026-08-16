import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:firebase_analytics/firebase_analytics.dart';
import '../screens/main_screen.dart';
import '../screens/home_screen.dart';
import '../screens/trending_screen.dart';
import '../screens/explore_screen.dart';
import '../screens/shorts_screen.dart';
import '../screens/movie_detail_screen.dart';
import '../screens/search_screen.dart';
import '../screens/login_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/follow_screen.dart';
import '../screens/history_screen.dart';
import '../screens/notifications_screen.dart';
import '../screens/settings_screen.dart';
import '../screens/terms_screen.dart';
import '../screens/register_screen.dart';
import '../screens/policy_screen.dart';
import '../screens/watch_movie_screen.dart';
import '../screens/playlist_screen.dart';
import '../screens/onboarding_screen.dart';
import '../screens/downloads_screen.dart';
import '../screens/profiles_screen.dart';

import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';

final GlobalKey<NavigatorState> _rootNavigatorKey = GlobalKey<NavigatorState>(debugLabel: 'root');

final GlobalKey<NavigatorState> _shellNavigatorKey = GlobalKey<NavigatorState>(debugLabel: 'shell');

FirebaseAnalytics? get analytics {
  try {
    return Firebase.apps.isNotEmpty ? FirebaseAnalytics.instance : null;
  } catch (_) {
    return null;
  }
}

GoRouter createRouter(bool hasAgreed, bool hasSeenOnboarding) {
  final currentAnalytics = analytics;
  return GoRouter(
    navigatorKey: _rootNavigatorKey,
    initialLocation: hasAgreed ? (hasSeenOnboarding ? '/' : '/onboarding') : '/terms',
    observers: currentAnalytics != null ? [FirebaseAnalyticsObserver(analytics: currentAnalytics)] : [],
  routes: <RouteBase>[
    ShellRoute(
      navigatorKey: _shellNavigatorKey,
      builder: (BuildContext context, GoRouterState state, Widget child) {
        return MainScreen(child: child);
      },
      routes: <RouteBase>[
        GoRoute(
          path: '/',
          builder: (BuildContext context, GoRouterState state) => const HomeScreen(),
        ),
        GoRoute(
          path: '/trending',
          builder: (BuildContext context, GoRouterState state) => const TrendingScreen(),
        ),
        GoRoute(
          path: '/explore',
          builder: (BuildContext context, GoRouterState state) => const ExploreScreen(),
        ),
        GoRoute(
          path: '/shorts',
          builder: (BuildContext context, GoRouterState state) => const ShortsScreen(),
        ),
        GoRoute(
          path: '/profile',
          builder: (BuildContext context, GoRouterState state) => const ProfileScreen(),
        ),
        GoRoute(
          path: '/search',
          builder: (BuildContext context, GoRouterState state) => const SearchScreen(),
        ),
      ],
    ),
    GoRoute(
      path: '/movie/:slug',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) {
        final slug = state.pathParameters['slug']!;
        return MovieDetailScreen(slug: slug);
      },
    ),
    GoRoute(
      path: '/watch_direct',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) {
        final data = state.extra as Map<String, dynamic>? ?? {};
        return WatchMovieScreen(
          m3u8Link: data['m3u8Link'] ?? '',
          title: data['title'] ?? '',
        );
      },
    ),

    GoRoute(
      path: '/login',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const LoginScreen(),
    ),
    GoRoute(
      path: '/register',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const RegisterScreen(),
    ),
    GoRoute(
      path: '/policy',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const PolicyScreen(),
    ),
    GoRoute(
      path: '/follow',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const FollowScreen(),
    ),
    GoRoute(
      path: '/playlists',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const PlaylistScreen(),
    ),
    GoRoute(
      path: '/history',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const HistoryScreen(),
    ),
    GoRoute(
      path: '/notifications',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const NotificationsScreen(),
    ),
    GoRoute(
      path: '/settings',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const SettingsScreen(),
    ),
    GoRoute(
      path: '/terms',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const TermsScreen(),
    ),
    GoRoute(
      path: '/onboarding',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const OnboardingScreen(),
    ),
    GoRoute(
      path: '/downloads',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const DownloadsScreen(),
    ),
    GoRoute(
      path: '/select_profile',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (BuildContext context, GoRouterState state) => const ProfilesScreen(),
    ),
  ],
);
}

