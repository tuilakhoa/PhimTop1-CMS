import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import 'package:go_router/go_router.dart';
import '../services/widget_service.dart';
import '../widgets/error_view.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  bool _isLoading = true;
  String? _error;
  List<HistoryItem> _history = [];

  @override
  void initState() {
    super.initState();
    _fetchHistory();
  }

  Future<void> _fetchHistory() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      setState(() {
        _isLoading = false;
        _error = "Bạn cần đăng nhập để xem lịch sử";
      });
      return;
    }

    try {
      final response = await cmsApi.getHistory(token);
      setState(() {
        _history = response.data ?? [];
        _isLoading = false;
      });
      if (response.data != null) {
        WidgetService.updateContinueWatchingWidget(response.data!);
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _clearHistory() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;

    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Xóa lịch sử"),
        content: const Text("Bạn có chắc muốn xóa toàn bộ lịch sử xem phim?"),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text("Hủy")),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text("Xóa", style: TextStyle(color: Colors.red))),
        ],
      ),
    );

    if (confirm == true) {
      setState(() => _isLoading = true);
      final success = await cmsApi.clearHistory(token);
      if (success) {
        setState(() {
          _history.clear();
          _isLoading = false;
        });
        WidgetService.updateContinueWatchingWidget([]);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã xóa lịch sử')));
        }
      } else {
        setState(() => _isLoading = false);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không thể xóa lịch sử')));
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Lịch sử xem', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        actions: [
          if (_history.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.delete_outline, color: Colors.white),
              onPressed: _clearHistory,
            )
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: Colors.red));
    }
    if (_error != null) {
      if (_error == "Bạn cần đăng nhập để xem lịch sử") {
        return Center(child: Text(_error!, style: const TextStyle(color: Colors.red)));
      }
      return ErrorView(error: _error!, onRetry: _fetchHistory);
    }
    if (_history.isEmpty) {
      return const Center(child: Text("Chưa có lịch sử xem phim", style: TextStyle(color: Colors.white)));
    }

    return ListView.builder(
      itemCount: _history.length,
      itemBuilder: (context, index) {
        final item = _history[index];
        final progress = item.duration > 0 ? (item.currentTime / item.duration).clamp(0.0, 1.0) : 0.0;
        
        return InkWell(
          onTap: () {
            context.push('/movie/${item.movieSlug}');
          },
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: item.thumbUrl.isNotEmpty
                      ? Image.network(
                          item.thumbUrl.startsWith('http') ? item.thumbUrl : 'https://phimimg.com/${item.thumbUrl}',
                          width: 100,
                          height: 60,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(width: 100, height: 60, color: Colors.grey[800], child: const Icon(Icons.movie, color: Colors.grey)),
                        )
                      : Container(
                          width: 100,
                          height: 60,
                          color: Colors.grey[800],
                          child: const Icon(Icons.movie, color: Colors.grey),
                        ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item.movieName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 4),
                      Text(item.episodeName, style: const TextStyle(color: Colors.grey, fontSize: 12)),
                      if (item.duration > 0) ...[
                        const SizedBox(height: 8),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(2),
                          child: LinearProgressIndicator(
                            value: progress,
                            backgroundColor: Colors.white24,
                            color: Colors.red,
                            minHeight: 3,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  item.updatedAt.split(' ').first,
                  style: const TextStyle(color: Colors.white54, fontSize: 10),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
