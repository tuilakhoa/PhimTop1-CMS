import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/config.dart';

class ChatMessage {
  final String text;
  final bool isUser;
  final List<dynamic>? movies;
  final bool isLoading;

  ChatMessage({required this.text, required this.isUser, this.movies, this.isLoading = false});
}

class AIChatScreen extends StatefulWidget {
  const AIChatScreen({super.key});

  @override
  State<AIChatScreen> createState() => _AIChatScreenState();
}

class _AIChatScreenState extends State<AIChatScreen> {
  final TextEditingController _controller = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<ChatMessage> _messages = [
    ChatMessage(text: 'Xin chào! Em có thể giúp anh/chị tìm phim gì hôm nay? (VD: "Tìm phim hành động Mỹ năm 2023 điểm cao")', isUser: false)
  ];
  final Dio _dio = Dio(BaseOptions(baseUrl: AppConfig.baseUrl));

  void _sendMessage() async {
    final text = _controller.text.trim();
    if (text.isEmpty) return;

    setState(() {
      _messages.add(ChatMessage(text: text, isUser: true));
      _messages.add(ChatMessage(text: '...', isUser: false, isLoading: true));
    });
    _controller.clear();
    _scrollToBottom();

    try {
      final response = await _dio.get('api/v1/ai_chat.php', queryParameters: {'q': text, 'is_app': '1'});
      final data = response.data;
      
      setState(() {
        _messages.removeLast(); // remove loading
        if (data['status'] == 'success') {
          _messages.add(ChatMessage(
            text: data['reply'],
            isUser: false,
            movies: data['movies'],
          ));
        } else {
          _messages.add(ChatMessage(text: 'Xin lỗi, hệ thống AI đang gặp sự cố: ${data['message']}', isUser: false));
        }
      });
    } catch (e) {
      setState(() {
        _messages.removeLast();
        _messages.add(ChatMessage(text: 'Xin lỗi, không thể kết nối tới server.', isUser: false));
      });
    }
    _scrollToBottom();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Row(
          children: [
            Icon(Icons.smart_toy, color: Colors.cyanAccent),
            SizedBox(width: 8),
            Text('Trợ lý AI PhimTop1'),
          ],
        ),
        backgroundColor: Colors.blueGrey[900],
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final msg = _messages[index];
                return _buildMessageBubble(msg);
              },
            ),
          ),
          _buildInputArea(),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(ChatMessage msg) {
    final bool isUser = msg.isUser;
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        mainAxisAlignment: isUser ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (!isUser)
            const CircleAvatar(
              backgroundColor: Colors.cyan,
              child: Icon(Icons.smart_toy, color: Colors.white, size: 20),
            ),
          const SizedBox(width: 8),
          Flexible(
            child: Column(
              crossAxisAlignment: isUser ? CrossAxisAlignment.end : CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    color: isUser ? Colors.cyan[700] : Colors.grey[800],
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(16),
                      topRight: const Radius.circular(16),
                      bottomLeft: isUser ? const Radius.circular(16) : Radius.zero,
                      bottomRight: isUser ? Radius.zero : const Radius.circular(16),
                    ),
                  ),
                  child: msg.isLoading
                      ? const SizedBox(
                          width: 40,
                          height: 20,
                          child: Center(child: LinearProgressIndicator(color: Colors.cyanAccent)),
                        )
                      : Text(
                          msg.text,
                          style: const TextStyle(color: Colors.white, fontSize: 15),
                        ),
                ),
                if (msg.movies != null && msg.movies!.isNotEmpty)
                  Container(
                    height: 160,
                    margin: const EdgeInsets.only(top: 8),
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: msg.movies!.length,
                      itemBuilder: (context, i) {
                        final movie = msg.movies![i];
                        final String thumb = movie['thumb_url'] ?? '';
                        final fullUrl = thumb.startsWith('http') ? thumb : '${AppConfig.baseUrl}${thumb.startsWith('/') ? '' : '/'}$thumb';
                        return GestureDetector(
                          onTap: () => context.push('/movie/${movie['slug']}'),
                          child: Container(
                            width: 100,
                            margin: const EdgeInsets.only(right: 8),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child: CachedNetworkImage(
                                      imageUrl: fullUrl,
                                      fit: BoxFit.cover,
                                      width: 100,
                                      errorWidget: (context, url, error) => Container(color: Colors.grey),
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  movie['name'] ?? '',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                ),
                                if (movie['year'] != null)
                                  Text(
                                    movie['year'].toString(),
                                    style: const TextStyle(fontSize: 10, color: Colors.grey),
                                  ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          if (isUser)
            const CircleAvatar(
              backgroundColor: Colors.grey,
              child: Icon(Icons.person, color: Colors.white, size: 20),
            ),
        ],
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      color: Colors.grey[900],
      child: SafeArea(
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _controller,
                style: const TextStyle(color: Colors.white),
                decoration: InputDecoration(
                  hintText: 'Nhập yêu cầu...',
                  hintStyle: const TextStyle(color: Colors.grey),
                  filled: true,
                  fillColor: Colors.grey[800],
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide.none,
                  ),
                ),
                onSubmitted: (_) => _sendMessage(),
              ),
            ),
            const SizedBox(width: 8),
            CircleAvatar(
              backgroundColor: Colors.cyan[600],
              child: IconButton(
                icon: const Icon(Icons.send, color: Colors.white, size: 20),
                onPressed: _sendMessage,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
