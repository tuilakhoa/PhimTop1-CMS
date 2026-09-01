import 'package:flutter/material.dart' show ThemeMode, Material, Brightness, CrossAxisAlignment, MainAxisAlignment, BorderRadius;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import 'windows_detail_screen.dart';
import 'dart:async';

class WindowsFollowScreen extends StatefulWidget {
  const WindowsFollowScreen({Key? key}) : super(key: key);

  @override
  State<WindowsFollowScreen> createState() => _WindowsFollowScreenState();
}

class _WindowsFollowScreenState extends State<WindowsFollowScreen> {
  bool _isLoading = true;
  String? _error;
  List<FollowItem> _follows = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchFollows();
    });
  }

  Future<void> _fetchFollows() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _error = "Bạn cần đăng nhập để xem danh sách yêu thích";
        });
      }
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await cmsApi.getFollows(token);
      if (mounted) {
        setState(() {
          _follows = response.data ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _unfollow(String movieSlug) async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;

    try {
      final response = await cmsApi.toggleFollow(token, movieSlug);
      if (response.status == 'success') {
        _fetchFollows();
      }
    } catch (e) {
      // Error handling
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: ProgressRing());
    }

    if (_error != null) {
      return Center(child: Text(_error!, style: const TextStyle(color: Colors.red)));
    }

    if (_follows.isEmpty) {
      return const Center(child: Text('Chưa có phim yêu thích', style: TextStyle(color: Colors.white)));
    }

    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Phim Yêu Thích', style: TextStyle(color: Colors.white)),
      ),
      content: GridView.builder(
        padding: const EdgeInsets.all(24.0),
        gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
          maxCrossAxisExtent: 200,
          mainAxisSpacing: 16,
          crossAxisSpacing: 16,
          childAspectRatio: 0.65,
        ),
        itemCount: _follows.length,
        itemBuilder: (context, index) {
          final item = _follows[index];
          final imageUrl = item.posterUrl.isNotEmpty ? item.posterUrl : 'https://via.placeholder.com/150';

          return GestureDetector(
            onTap: () {
              Navigator.push(context, FluentPageRoute(builder: (_) => WindowsDetailScreen(movieSlug: item.movieSlug)));
            },
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                color: const Color(0xFF161623),
              ),
              clipBehavior: Clip.antiAlias,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Image.network(
                    imageUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (c, e, s) => Container(color: Colors.grey),
                  ),
                  Positioned(
                    bottom: 0, left: 0, right: 0,
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Colors.black.withOpacity(0.9), Colors.transparent],
                          begin: Alignment.bottomCenter,
                          end: Alignment.topCenter,
                        ),
                      ),
                      child: Text(item.movieName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold), maxLines: 2, overflow: TextOverflow.ellipsis),
                    ),
                  ),
                  Positioned(
                    top: 4, right: 4,
                    child: IconButton(
                      icon: const Icon(FluentIcons.heart_fill, color: Colors.red),
                      onPressed: () => _unfollow(item.movieSlug),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
