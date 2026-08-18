import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import 'package:go_router/go_router.dart';
import '../widgets/error_view.dart';

class FollowScreen extends StatefulWidget {
  const FollowScreen({super.key});

  @override
  State<FollowScreen> createState() => _FollowScreenState();
}

class _FollowScreenState extends State<FollowScreen> {
  bool _isLoading = true;
  String? _error;
  List<FollowItem> _follows = [];

  @override
  void initState() {
    super.initState();
    _fetchFollows();
  }

  Future<void> _fetchFollows() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      setState(() {
        _isLoading = false;
        _error = "Bạn cần đăng nhập để xem danh sách";
      });
      return;
    }

    try {
      final response = await cmsApi.getFollows(token);
      setState(() {
        _follows = response.data ?? [];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Phim đã thích', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return Center(child: CircularProgressIndicator(color: Theme.of(context).primaryColor));
    }
    if (_error != null) {
      if (_error == "Bạn cần đăng nhập để xem danh sách") {
        return Center(child: Text(_error!, style: const TextStyle(color: Colors.red)));
      }
      return ErrorView(error: _error!, onRetry: _fetchFollows);
    }
    if (_follows.isEmpty) {
      return const Center(child: Text("Bạn chưa thích bộ phim nào", style: TextStyle(color: Colors.white)));
    }

    return ListView.builder(
      itemCount: _follows.length,
      itemBuilder: (context, index) {
        final item = _follows[index];
        final thumb = item.thumbUrl ?? "";
        final imageUrl = thumb.startsWith('http') 
            ? thumb 
            : 'https://phimimg.com/$thumb'; // Fallback to phimimg domain

        return ListTile(
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          leading: ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: SizedBox(
              width: 50,
              height: 75,
              child: CachedNetworkImage(
                imageUrl: imageUrl,
                fit: BoxFit.cover,
                placeholder: (context, url) => Container(color: Colors.grey[900]),
                errorWidget: (context, url, error) => Container(color: Colors.grey[900]),
              ),
            ),
          ),
          title: Text(item.itemName, style: const TextStyle(color: Colors.white)),
          subtitle: const Text("Phim", style: TextStyle(color: Colors.grey, fontSize: 12)),
          trailing: Icon(Icons.favorite, color: Theme.of(context).primaryColor, size: 20),
          onTap: () {
            context.push('/movie/${item.itemSlug}');
          },
        );
      },
    );
  }
}
