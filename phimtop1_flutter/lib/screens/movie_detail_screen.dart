import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/detail_provider.dart';
import '../providers/auth_provider.dart';
import '../models/models.dart';
import '../api/cms_api.dart';
import '../core/config.dart';
import 'watch_movie_screen.dart';
import 'watch_embed_screen.dart';
import 'package:share_plus/share_plus.dart';
import '../services/tv_remote_service.dart';

class MovieDetailScreen extends StatefulWidget {
  final String slug;
  
  const MovieDetailScreen({super.key, required this.slug});

  @override
  State<MovieDetailScreen> createState() => _MovieDetailScreenState();
}

class _MovieDetailScreenState extends State<MovieDetailScreen> {
  final TextEditingController _commentController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<DetailProvider>();
      provider.fetchDetail(widget.slug).then((_) {
        provider.fetchComments(widget.slug);
      });
      final token = context.read<AuthProvider>().token;
      if (token != null) {
        provider.checkFollow(token, widget.slug);
      }
    });
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  void _watchMovie(DetailProvider provider) {
    if (provider.episodes.isNotEmpty) {
      final serverData = provider.episodes[provider.currentServerIndex].serverData;
      if (serverData.isNotEmpty) {
        final episode = serverData[provider.currentEpisodeIndex];
        final m3u8Link = episode.linkM3u8;
        final embedLink = episode.linkEmbed;
        
        if (m3u8Link.isNotEmpty || embedLink.isNotEmpty) {
          // Log history
          final token = context.read<AuthProvider>().token;
          if (token != null && provider.movie != null) {
            final movie = provider.movie!;
            final thumbUrl = movie.thumbUrl ?? movie.posterUrl ?? '';
            cmsApi.addHistory(
              token, 
              movie.slug, 
              movie.name, 
              episode.name,
              thumbUrl: thumbUrl
            );
          }

          if (m3u8Link.isNotEmpty) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => WatchMovieScreen(
                  m3u8Link: m3u8Link,
                  title: provider.movie?.name ?? "",
                  movieSlug: provider.movie?.slug ?? "",
                  episodeName: episode.name,
                ),
              ),
            );
          } else if (embedLink.isNotEmpty) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => WatchEmbedScreen(
                  embedUrl: embedLink,
                  title: provider.movie?.name ?? "",
                ),
              ),
            );
          }
        }
      }
    }
  }

  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: Consumer<DetailProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (provider.error != null) {
            return Center(child: Text(provider.error!, style: const TextStyle(color: Colors.red)));
          }

          final movie = provider.movie;
          if (movie == null) return const SizedBox.shrink();

          if (_isTvMode(context)) {
            return _buildTvLayout(context, provider);
          }
          return _buildMobileLayout(context, provider);
        },
      ),
    );
  }

  Widget _buildTvLayout(BuildContext context, DetailProvider provider) {
    final movie = provider.movie!;
    final imageUrl = (movie.posterUrl ?? movie.thumbUrl!).startsWith('http') 
        ? (movie.posterUrl ?? movie.thumbUrl!) 
        : '${provider.domain}${movie.posterUrl ?? movie.thumbUrl}';

    return Stack(
      fit: StackFit.expand,
      children: [
        CachedNetworkImage(
          imageUrl: imageUrl,
          fit: BoxFit.cover,
        ),
        Container(
          color: Colors.black.withOpacity(0.85),
        ),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 320,
              padding: const EdgeInsets.all(32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: CachedNetworkImage(
                      imageUrl: imageUrl,
                      fit: BoxFit.cover,
                      height: 380,
                      width: double.infinity,
                    ),
                  ),
                  const SizedBox(height: 32),
                  Focus(
                    autofocus: true,
                    child: Builder(
                      builder: (context) {
                        final hasFocus = Focus.of(context).hasFocus;
                        return InkWell(
                          onTap: () => _watchMovie(provider),
                          borderRadius: BorderRadius.circular(12),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 24),
                            decoration: BoxDecoration(
                              color: hasFocus ? Colors.white : Theme.of(context).primaryColor,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.play_arrow, color: hasFocus ? Colors.black : Colors.white),
                                const SizedBox(width: 8),
                                Text("Xem Phim", style: TextStyle(color: hasFocus ? Colors.black : Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ),
                        );
                      }
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.only(top: 32, right: 32, bottom: 32),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      movie.name,
                      style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    if (movie.originName != null) ...[
                      const SizedBox(height: 8),
                      Text(
                        movie.originName!,
                        style: const TextStyle(fontSize: 24, color: Colors.grey),
                      ),
                    ],
                    const SizedBox(height: 24),
                    Text(
                      movie.content?.replaceAll(RegExp(r'<[^>]*>|&[^;]+;'), '') ?? "Đang cập nhật...",
                      style: const TextStyle(color: Colors.white70, height: 1.5, fontSize: 18),
                      maxLines: 4,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 32),
                    if (provider.episodes.isNotEmpty) ...[
                      const Text("Chọn tập:", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                      const SizedBox(height: 16),
                      Expanded(
                        child: SingleChildScrollView(
                          child: Wrap(
                            spacing: 12,
                            runSpacing: 12,
                            children: List.generate(
                              provider.episodes[provider.currentServerIndex].serverData.length,
                              (index) {
                                final ep = provider.episodes[provider.currentServerIndex].serverData[index];
                                return Focus(
                                  child: Builder(
                                    builder: (context) {
                                      final hasFocus = Focus.of(context).hasFocus;
                                      return InkWell(
                                        onTap: () {
                                          provider.changeEpisode(index, provider.currentServerIndex);
                                          _watchMovie(provider);
                                        },
                                        borderRadius: BorderRadius.circular(8),
                                        child: AnimatedContainer(
                                          duration: const Duration(milliseconds: 200),
                                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                                          decoration: BoxDecoration(
                                            color: hasFocus ? Colors.white : Colors.white.withOpacity(0.1),
                                            borderRadius: BorderRadius.circular(8),
                                            border: Border.all(
                                              color: hasFocus ? Colors.white : Colors.transparent,
                                              width: 2,
                                            ),
                                          ),
                                          child: Text(
                                            ep.name,
                                            style: TextStyle(
                                              color: hasFocus ? Colors.black : Colors.white,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 16,
                                            ),
                                          ),
                                        ),
                                      );
                                    }
                                  ),
                                );
                              },
                            ),
                          ),
                        ),
                      )
                    ]
                  ],
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildMobileLayout(BuildContext context, DetailProvider provider) {
    final movie = provider.movie!;
    return SafeArea(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Poster & Play Button
                Container(
                  height: MediaQuery.of(context).size.width * 9 / 16,
                  color: Colors.black,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      if (movie.posterUrl != null || movie.thumbUrl != null)
                        CachedNetworkImage(
                          imageUrl: (movie.posterUrl ?? movie.thumbUrl!).startsWith('http') 
                              ? (movie.posterUrl ?? movie.thumbUrl!) 
                              : '${provider.domain}${movie.posterUrl ?? movie.thumbUrl}',
                          fit: BoxFit.cover,
                        ),
                      Container(
                        color: Colors.black.withOpacity(0.4),
                      ),
                      Center(
                        child: IconButton(
                          icon: const Icon(Icons.play_circle_fill, size: 64, color: Colors.white),
                          onPressed: () => _watchMovie(provider),
                        ),
                      ),
                    ],
                  ),
                ),
                
                // Movie Info & Episodes
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          movie.name,
                          style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.white),
                        ),
                        if (movie.originName != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            movie.originName!,
                            style: const TextStyle(fontSize: 16, color: Colors.grey),
                          ),
                        ],
                        const SizedBox(height: 16),
                        
                        // Action Row
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            GestureDetector(
                              onTap: () {
                                final token = context.read<AuthProvider>().token;
                                if (token != null) {
                                  provider.toggleFollow(token);
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Vui lòng đăng nhập để yêu thích')),
                                  );
                                }
                              },
                              child: _buildActionButton(
                                provider.isFollowing ? Icons.favorite : Icons.favorite_border,
                                provider.isFollowing ? "Đã thích" : "Yêu thích",
                                color: provider.isFollowing ? Colors.red : Colors.white,
                              ),
                            ),
                            _buildActionButton(Icons.comment, "Bình luận"),
                            GestureDetector(
                              onTap: () {
                                Share.share('${AppConfig.baseUrl}phim/${movie.slug}');
                              },
                              child: _buildActionButton(Icons.share, "Chia sẻ"),
                            ),
                            AnimatedBuilder(
                              animation: TvRemoteService(),
                              builder: (context, child) {
                                final isConnected = TvRemoteService().isClientConnected;
                                return GestureDetector(
                                  onTap: () {
                                    if (isConnected) {
                                      if (provider.episodes.isNotEmpty) {
                                        final serverData = provider.episodes[provider.currentServerIndex].serverData;
                                        if (serverData.isNotEmpty) {
                                          final episode = serverData[provider.currentEpisodeIndex];
                                          if (episode.linkM3u8.isNotEmpty) {
                                            TvRemoteService().castDirect(episode.linkM3u8, '${movie.name} - ${episode.name}');
                                            _showRemoteControl(context, '${movie.name} - ${episode.name}');
                                            return;
                                          }
                                        }
                                      }
                                      TvRemoteService().castToTv(movie.slug);
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('Đã gửi lệnh chiếu lên TV')),
                                      );
                                    } else {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('Chưa kết nối với TV. Vui lòng bấm biểu tượng Cast (góc trên) để kết nối.')),
                                      );
                                    }
                                  },
                                  child: _buildActionButton(
                                    isConnected ? Icons.cast_connected : Icons.cast,
                                    "Chiếu TV",
                                    color: isConnected ? Colors.green : Colors.white,
                                  ),
                                );
                              }
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),
                        
                        // Content
                        const Text("Nội dung", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                        const SizedBox(height: 8),
                        Text(
                          movie.content?.replaceAll(RegExp(r'<[^>]*>|&[^;]+;'), '') ?? "Đang cập nhật...",
                          style: const TextStyle(color: Colors.white70, height: 1.5),
                        ),
                        
                        const SizedBox(height: 24),
                        // Episodes
                        if (provider.episodes.isNotEmpty) ...[
                          const Text("Chọn tập", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                          const SizedBox(height: 12),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: List.generate(
                              provider.episodes[provider.currentServerIndex].serverData.length,
                              (index) {
                                final isSelected = provider.currentEpisodeIndex == index;
                                final ep = provider.episodes[provider.currentServerIndex].serverData[index];
                                return GestureDetector(
                                  onTap: () {
                                    provider.changeEpisode(index, provider.currentServerIndex);
                                    _watchMovie(provider);
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: isSelected ? Theme.of(context).primaryColor : Colors.grey[800],
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      ep.name,
                                      style: TextStyle(
                                        color: isSelected ? Colors.white : Colors.white70,
                                        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
                          )
                        ],
                        
                        const SizedBox(height: 32),
                        const Text("Bình luận", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                        const SizedBox(height: 16),
                        
                        // Comment Input Form
                        Row(
                          children: [
                            Expanded(
                              child: TextField(
                                controller: _commentController,
                                style: const TextStyle(color: Colors.white),
                                decoration: InputDecoration(
                                  hintText: "Nhập bình luận...",
                                  hintStyle: const TextStyle(color: Colors.white54),
                                  filled: true,
                                  fillColor: Colors.grey[850],
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(8),
                                    borderSide: BorderSide.none,
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            IconButton(
                              onPressed: () async {
                                final content = _commentController.text.trim();
                                if (content.isEmpty) return;
                                
                                final auth = context.read<AuthProvider>();
                                final success = await provider.postComment(
                                  widget.slug, 
                                  content,
                                  token: auth.token,
                                  name: auth.user?.name,
                                );
                                if (success) {
                                  _commentController.clear();
                                  if (mounted) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(content: Text('Bình luận thành công')),
                                    );
                                  }
                                } else {
                                  if (mounted) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(content: Text('Lỗi khi đăng bình luận')),
                                    );
                                  }
                                }
                              },
                              icon: const Icon(Icons.send, color: Colors.blueAccent),
                            )
                          ],
                        ),
                        const SizedBox(height: 24),
                        
                        if (provider.comments.isEmpty)
                          const Text("Chưa có bình luận nào.", style: TextStyle(color: Colors.grey))
                        else
                          ListView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: provider.comments.length,
                            itemBuilder: (context, index) {
                              final c = provider.comments[index];
                              return Padding(
                                padding: const EdgeInsets.only(bottom: 16.0),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    CircleAvatar(
                                      backgroundColor: Colors.grey[800],
                                      child: Text(c.userName.isNotEmpty ? c.userName[0].toUpperCase() : "?", style: const TextStyle(color: Colors.white)),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Text(c.userName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                                              const SizedBox(width: 8),
                                              Text(c.timeAgo, style: const TextStyle(color: Colors.grey, fontSize: 12)),
                                            ],
                                          ),
                                          const SizedBox(height: 4),
                                          Text(c.content, style: const TextStyle(color: Colors.white70)),
                                        ],
                                      ),
                                    )
                                  ],
                                ),
                              );
                            },
                          ),
                      ],
                    ),
                  ),
                )
              ],
            ),
          );
  }

  Widget _buildActionButton(IconData icon, String label, {Color color = Colors.white}) {
    return Column(
      children: [
        Icon(icon, color: color, size: 24),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 12)),
      ],
    );
  }

  void _showRemoteControl(BuildContext context, String title) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 32.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 24),
                decoration: BoxDecoration(
                  color: Colors.grey[700],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const Icon(Icons.cast_connected, color: Colors.blueAccent, size: 48),
              const SizedBox(height: 16),
              Text(
                "Đang chiếu trên TV",
                style: const TextStyle(color: Colors.white70, fontSize: 14),
              ),
              const SizedBox(height: 8),
              Text(
                title,
                style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 40),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  IconButton(
                    iconSize: 48,
                    icon: const Icon(Icons.replay_10, color: Colors.white),
                    onPressed: () {
                      TvRemoteService().sendPlayerControl('rewind');
                    },
                  ),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.blueAccent.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: Row(
                      children: [
                        IconButton(
                          iconSize: 48,
                          icon: const Icon(Icons.play_arrow, color: Colors.blueAccent),
                          onPressed: () => TvRemoteService().sendPlayerControl('play'),
                        ),
                        IconButton(
                          iconSize: 48,
                          icon: const Icon(Icons.pause, color: Colors.blueAccent),
                          onPressed: () => TvRemoteService().sendPlayerControl('pause'),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    iconSize: 48,
                    icon: const Icon(Icons.forward_10, color: Colors.white),
                    onPressed: () {
                      TvRemoteService().sendPlayerControl('forward');
                    },
                  ),
                ],
              ),
              const SizedBox(height: 40),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.red[700],
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 56),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                icon: const Icon(Icons.stop_circle_outlined, size: 28),
                label: const Text("Dừng Phát", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                onPressed: () {
                  TvRemoteService().sendPlayerControl('stop');
                  Navigator.pop(context);
                },
              )
            ],
          ),
        );
      },
    );
  }
}
