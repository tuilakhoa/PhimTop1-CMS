import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/watch_party_provider.dart';
import '../providers/auth_provider.dart';

import 'package:media_kit/media_kit.dart';
import 'package:media_kit_video/media_kit_video.dart';
import 'package:go_router/go_router.dart';

class WatchPartyScreen extends StatefulWidget {
  final String partyCode;
  const WatchPartyScreen({super.key, required this.partyCode});

  @override
  State<WatchPartyScreen> createState() => _WatchPartyScreenState();
}

class _WatchPartyScreenState extends State<WatchPartyScreen> {
  final TextEditingController _chatCtrl = TextEditingController();
  late final Player _player = Player();
  late final VideoController _videoCtrl = VideoController(_player);
  bool _isLoading = true;
  String _lastState = 'paused';

  @override
  void initState() {
    super.initState();
    _initParty();
  }

  Future<void> _initParty() async {
    final partyProvider = context.read<WatchPartyProvider>();
    final authProvider = context.read<AuthProvider>();
    partyProvider.initUser(authProvider.token ?? 'guest_uid', authProvider.user?['name'] ?? 'Khách');
    
    final success = await partyProvider.joinParty(widget.partyCode);
    if (!success) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không tìm thấy phòng')));
        context.pop();
      }
      return;
    }

    // Dummy video URL for now, you would fetch the real m3u8 based on movie_slug
    await _player.open(Media('https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8'), play: false);
    
    // Add listener for sync if not host
    if (!partyProvider.isHost) {
      _player.stream.position.listen((_) => _guestVideoListener());
      _player.stream.playing.listen((_) => _guestVideoListener());
    } else {
      _player.stream.position.listen((_) => _hostVideoListener());
      _player.stream.playing.listen((_) => _hostVideoListener());
    }

    setState(() {
      _isLoading = false;
    });
  }

  void _hostVideoListener() {
    if (!mounted) return;
    final partyProvider = context.read<WatchPartyProvider>();
    final isPlaying = _player.state.playing;
    final state = isPlaying ? 'playing' : 'paused';
    final pos = _player.state.position.inSeconds;
    
    // Only update if state changes significantly to avoid spam
    if (state != _lastState) {
      _lastState = state;
      partyProvider.syncVideoState(state, pos);
    }
  }

  void _guestVideoListener() {
    if (!mounted) return;
    final partyProvider = context.read<WatchPartyProvider>();
    final partyData = partyProvider.partyData;
    if (partyData != null) {
      final hostState = partyData['state'];
      final hostPos = partyData['position'] ?? 0;
      
      final currentPos = _player.state.position.inSeconds;
      final isPlaying = _player.state.playing;
      
      if (hostState == 'playing' && !isPlaying) {
        _player.play();
      } else if (hostState == 'paused' && isPlaying) {
        _player.pause();
      }
      
      // Sync position if out of sync by more than 3 seconds
      if ((currentPos - hostPos).abs() > 3) {
        _player.seek(Duration(seconds: hostPos as int));
      }
    }
  }

  @override
  void dispose() {
    context.read<WatchPartyProvider>().leaveParty();
    _player.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Phòng Xem Chung: ${widget.partyCode}'),
      ),
      body: Column(
        children: [
          // Video Player
          AspectRatio(
            aspectRatio: 16 / 9,
            child: Video(controller: _videoCtrl),
          ),
          
          // Chat & Members section
          Expanded(
            child: Consumer<WatchPartyProvider>(
              builder: (context, provider, child) {
                final chat = provider.partyData?['chat'] as List<dynamic>? ?? [];
                
                return Column(
                  children: [
                    Expanded(
                      child: ListView.builder(
                        itemCount: chat.length,
                        itemBuilder: (context, index) {
                          final msg = chat[index];
                          return ListTile(
                            title: Text(msg['name'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.blueAccent)),
                            subtitle: Text(msg['text'], style: const TextStyle(color: Colors.white)),
                          );
                        },
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(8.0),
                      child: Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: _chatCtrl,
                              decoration: const InputDecoration(
                                hintText: 'Nhập tin nhắn...',
                                border: OutlineInputBorder(),
                                filled: true,
                              ),
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.send, color: Colors.blueAccent),
                            onPressed: () {
                              if (_chatCtrl.text.isNotEmpty) {
                                provider.sendMessage(_chatCtrl.text);
                                _chatCtrl.clear();
                              }
                            },
                          )
                        ],
                      ),
                    )
                  ],
                );
              },
            ),
          )
        ],
      ),
    );
  }
}
