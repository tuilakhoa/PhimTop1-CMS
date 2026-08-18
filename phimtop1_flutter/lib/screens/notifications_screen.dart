import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../providers/auth_provider.dart';
import '../widgets/error_view.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _isLoading = true;
  String? _error;
  List<NotificationItem> _notifications = [];

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      setState(() {
        _isLoading = false;
        _error = "Bạn cần đăng nhập để xem thông báo";
      });
      return;
    }

    try {
      final response = await cmsApi.getNotifications(token);
      setState(() {
        _notifications = response.data ?? [];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _markAsRead(NotificationItem item) async {
    if (item.isRead) return;
    
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    
    final success = await cmsApi.markNotificationRead(token, item.id);
    if (success) {
      setState(() {
        _notifications = _notifications.map((n) {
          if (n.id == item.id) {
            return NotificationItem.fromJson({
              'id': n.id,
              'title': n.title,
              'message': n.message,
              'url': n.url,
              'is_read': true,
              'created_at': n.createdAt,
            });
          }
          return n;
        }).toList();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Thông báo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
      if (_error == "Bạn cần đăng nhập để xem thông báo") {
        return Center(child: Text(_error!, style: const TextStyle(color: Colors.red)));
      }
      return ErrorView(error: _error!, onRetry: _fetchNotifications);
    }
    if (_notifications.isEmpty) {
      return const Center(child: Text("Không có thông báo nào", style: TextStyle(color: Colors.white)));
    }

    return ListView.builder(
      itemCount: _notifications.length,
      itemBuilder: (context, index) {
        final item = _notifications[index];
        return ListTile(
          leading: Icon(
            item.isRead ? Icons.notifications_none : Icons.notifications_active,
            color: item.isRead ? Colors.grey : Theme.of(context).primaryColor,
          ),
          title: Text(
            item.title, 
            style: TextStyle(
              color: Colors.white, 
              fontWeight: item.isRead ? FontWeight.normal : FontWeight.bold
            )
          ),
          subtitle: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 4),
              Text(item.message, style: const TextStyle(color: Colors.grey, fontSize: 14)),
              const SizedBox(height: 4),
              Text(item.createdAt, style: const TextStyle(color: Colors.grey, fontSize: 10)),
            ],
          ),
          onTap: () {
            _markAsRead(item);
            // Optionally handle URL navigation if item.url is present
          },
        );
      },
    );
  }
}
