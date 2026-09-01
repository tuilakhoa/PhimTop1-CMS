import 'package:flutter/material.dart' show ThemeMode, Material, Brightness, CrossAxisAlignment, MainAxisAlignment, BorderRadius;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import 'windows_detail_screen.dart';
import 'dart:async';

class WindowsHistoryScreen extends StatefulWidget {
  const WindowsHistoryScreen({Key? key}) : super(key: key);

  @override
  State<WindowsHistoryScreen> createState() => _WindowsHistoryScreenState();
}

class _WindowsHistoryScreenState extends State<WindowsHistoryScreen> {
  bool _isLoading = true;
  String? _error;
  List<HistoryItem> _history = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchHistory();
    });
  }

  Future<void> _fetchHistory() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _error = "Bạn cần đăng nhập để xem lịch sử";
        });
      }
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await cmsApi.getHistory(token);
      if (mounted) {
        setState(() {
          _history = response.data ?? [];
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

  Future<void> _clearHistory() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;

    try {
      final success = await cmsApi.clearHistory(token);
      if (success) {
        _fetchHistory();
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
      return Center(child: Text(_error!, style: const TextStyle(color: Color(0xFFF44336))));
    }

    if (_history.isEmpty) {
      return const Center(child: Text('Chưa có lịch sử xem phim', style: TextStyle(color: Colors.white)));
    }

    return ScaffoldPage(
      header: PageHeader(
        title: const Text('Lịch Sử Xem Phim', style: TextStyle(color: Colors.white)),
        commandBar: Button(
          onPressed: _history.isNotEmpty ? _clearHistory : null,
          child: const Text('Xóa tất cả'),
        ),
      ),
      content: GridView.builder(
        padding: const EdgeInsets.all(24.0),
        gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
          maxCrossAxisExtent: 200,
          mainAxisSpacing: 16,
          crossAxisSpacing: 16,
          childAspectRatio: 0.65,
        ),
        itemCount: _history.length,
        itemBuilder: (context, index) {
          final item = _history[index];
          final imageUrl = item.thumbUrl.isNotEmpty ? item.thumbUrl : 'https://via.placeholder.com/150';

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
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(item.movieName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis),
                          Text(item.episodeName, style: const TextStyle(color: Colors.grey, fontSize: 12)),
                        ],
                      ),
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
