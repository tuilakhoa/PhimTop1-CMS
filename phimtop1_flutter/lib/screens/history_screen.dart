import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import 'package:go_router/go_router.dart';

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
      return Center(child: Text(_error!, style: const TextStyle(color: Colors.red)));
    }
    if (_history.isEmpty) {
      return const Center(child: Text("Chưa có lịch sử xem phim", style: TextStyle(color: Colors.white)));
    }

    return ListView.builder(
      itemCount: _history.length,
      itemBuilder: (context, index) {
        final item = _history[index];
        return ListTile(
          leading: const Icon(Icons.history, color: Colors.grey),
          title: Text(item.movieName, style: const TextStyle(color: Colors.white)),
          subtitle: Text(item.episodeName, style: const TextStyle(color: Colors.grey, fontSize: 12)),
          trailing: Text(
            item.updatedAt.split(' ').first,
            style: const TextStyle(color: Colors.grey, fontSize: 10),
          ),
          onTap: () {
            context.push('/movie/${item.movieSlug}');
          },
        );
      },
    );
  }
}
