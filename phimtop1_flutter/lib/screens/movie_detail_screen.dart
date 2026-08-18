import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/detail_provider.dart';
import '../providers/auth_provider.dart';
import '../providers/playlist_provider.dart';
import '../models/models.dart';
import '../api/cms_api.dart';
import '../core/config.dart';
import 'watch_movie_screen.dart';
import 'watch_embed_screen.dart';
import 'package:share_plus/share_plus.dart';
import '../services/tv_remote_service.dart';
import '../providers/download_provider.dart';
import '../models/download_task.dart';
import '../widgets/error_view.dart';

class MovieDetailScreen extends StatefulWidget {
  final String slug;
  
  const MovieDetailScreen({super.key, required this.slug});

  @override
  State<MovieDetailScreen> createState() => _MovieDetailScreenState();
}

class _MovieDetailScreenState extends State<MovieDetailScreen> {
  final TextEditingController _commentController = TextEditingController();
  final TextEditingController _episodeSearchController = TextEditingController();
  String _episodeSearchQuery = "";

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<DetailProvider>();
      final token = context.read<AuthProvider>().token;
      provider.fetchDetail(widget.slug, token: token).then((_) {
        provider.fetchComments(widget.slug);
        provider.fetchReviews(widget.slug);
      });
      if (token != null) {
        provider.checkFollow(token, widget.slug);
      }
    });
  }

  @override
  void dispose() {
    _commentController.dispose();
    _episodeSearchController.dispose();
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
              episodeSlug: episode.slug,
              thumbUrl: thumbUrl
            );
          }

          if (m3u8Link.isNotEmpty) {
            final movie = provider.movie!;
            final thumbUrl = movie.thumbUrl ?? movie.posterUrl ?? '';
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => WatchMovieScreen(
                  m3u8Link: m3u8Link,
                  title: provider.movie?.name ?? "",
                  movieSlug: provider.movie?.slug ?? "",
                  episodeName: episode.name,
                  episodeSlug: episode.slug,
                  thumbUrl: thumbUrl,
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

  void _showPlaylistModal(BuildContext context, String movieSlug, String movieName, String thumbUrl) {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Vui lòng đăng nhập để dùng danh sách phát')));
      return;
    }

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) {
        return StatefulBuilder(builder: (context, setStateModal) {
          final provider = context.watch<PlaylistProvider>();
          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
            child: SizedBox(
              height: MediaQuery.of(context).size.height * 0.6,
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white12))),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text("Danh sách phát", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                        IconButton(icon: const Icon(Icons.close, color: Colors.grey), onPressed: () => Navigator.pop(context)),
                      ],
                    ),
                  ),
                  Expanded(
                    child: provider.isLoading && provider.playlists.isEmpty
                        ? Center(child: CircularProgressIndicator(color: Theme.of(context).primaryColor))
                        : provider.playlists.isEmpty
                            ? const Center(child: Text("Bạn chưa có danh sách phát nào.", style: TextStyle(color: Colors.grey)))
                            : ListView.builder(
                                itemCount: provider.playlists.length,
                                itemBuilder: (context, index) {
                                  final pl = provider.playlists[index];
                                  final hasMovie = (pl.items ?? []).any((item) => item.movieSlug == movieSlug);
                                  return ListTile(
                                    title: Text(pl.name, style: const TextStyle(color: Colors.white)),
                                    trailing: hasMovie
                                        ? const Icon(Icons.check_circle, color: Colors.green)
                                        : ElevatedButton(
                                            style: ElevatedButton.styleFrom(backgroundColor: Theme.of(context).primaryColor, foregroundColor: Colors.white),
                                            onPressed: () async {
                                              final success = await provider.addToPlaylist(pl.id, movieSlug, movieName, thumbUrl);
                                              if (success) {
                                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã thêm vào danh sách')));
                                                Navigator.pop(context);
                                              }
                                            },
                                            child: const Text("Thêm"),
                                          ),
                                  );
                                },
                              ),
                  ),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(border: Border(top: BorderSide(color: Colors.white12))),
                    child: Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: TextEditingController(),
                            style: const TextStyle(color: Colors.white),
                            decoration: const InputDecoration(hintText: 'Tên danh sách mới', hintStyle: TextStyle(color: Colors.grey)),
                            onSubmitted: (val) async {
                              if (val.trim().isNotEmpty) {
                                await provider.createPlaylist(val.trim());
                              }
                            },
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        });
      },
    );
  }

  void _showDownloadModal(BuildContext context, DetailProvider provider) {
    final movie = provider.movie;
    if (movie == null) return;
    
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) {
        return StatefulBuilder(builder: (context, setStateModal) {
          final downloadProvider = context.watch<DownloadProvider>();
          final serverData = provider.episodes.isNotEmpty ? provider.episodes[provider.currentServerIndex].serverData : [];
          
          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
            child: SizedBox(
              height: MediaQuery.of(context).size.height * 0.6,
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white12))),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text("Tải xuống ngoại tuyến", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                        IconButton(icon: const Icon(Icons.close, color: Colors.grey), onPressed: () => Navigator.pop(context)),
                      ],
                    ),
                  ),
                  Expanded(
                    child: serverData.isEmpty
                        ? const Center(child: Text("Không có tập phim nào.", style: TextStyle(color: Colors.grey)))
                        : ListView.builder(
                            itemCount: serverData.length,
                            itemBuilder: (context, index) {
                              final ep = serverData[index];
                              final taskId = '${movie.slug}_${ep.slug}';
                              final task = downloadProvider.getTask(taskId);
                              
                              Widget trailing = IconButton(
                                icon: const Icon(Icons.download, color: Colors.white),
                                onPressed: () async {
                                  if (ep.linkM3u8.isNotEmpty) {
                                    final thumbUrl = movie.thumbUrl ?? movie.posterUrl ?? '';
                                    final message = await downloadProvider.startDownload(
                                      movieSlug: movie.slug,
                                      movieName: movie.name,
                                      episodeSlug: ep.slug,
                                      episodeName: ep.name,
                                      thumbUrl: thumbUrl.startsWith('http') ? thumbUrl : '${provider.domain}$thumbUrl',
                                      m3u8Url: ep.linkM3u8,
                                      totalDurationSeconds: 7200, // Estimate 2 hours
                                    );
                                    if (message != null && mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                        content: Text(message),
                                        backgroundColor: message.contains('thất bại') || message.contains('CẢNH BÁO') ? Colors.red : null,
                                      ));
                                    }
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tập này không hỗ trợ tải')));
                                  }
                                },
                              );

                              if (task != null) {
                                if (task.status == DownloadStatus.completed) {
                                  trailing = const Icon(Icons.check_circle, color: Colors.green);
                                } else if (task.status == DownloadStatus.downloading) {
                                  trailing = Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text('${(task.progress * 100).toStringAsFixed(0)}%', style: const TextStyle(color: Colors.amber)),
                                      const SizedBox(width: 8),
                                      IconButton(
                                        icon: const Icon(Icons.stop_circle, color: Colors.red),
                                        onPressed: () => downloadProvider.cancelDownload(taskId),
                                      ),
                                    ],
                                  );
                                } else if (task.status == DownloadStatus.pending) {
                                  trailing = const Text('Chờ tải...', style: TextStyle(color: Colors.grey));
                                } else if (task.status == DownloadStatus.failed) {
                                  trailing = IconButton(
                                    icon: const Icon(Icons.refresh, color: Colors.red),
                                    onPressed: () async {
                                      final message = await downloadProvider.startDownload(
                                        movieSlug: task.movieSlug,
                                        movieName: task.movieName,
                                        episodeSlug: task.episodeSlug,
                                        episodeName: task.episodeName,
                                        thumbUrl: task.thumbUrl,
                                        m3u8Url: task.m3u8Url,
                                        totalDurationSeconds: 7200,
                                      );
                                      if (message != null && mounted) {
                                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                          content: Text(message),
                                          backgroundColor: message.contains('thất bại') || message.contains('CẢNH BÁO') ? Colors.red : null,
                                        ));
                                      }
                                    },
                                  );
                                }
                              }

                              return ListTile(
                                title: Text(ep.name, style: const TextStyle(color: Colors.white)),
                                trailing: trailing,
                              );
                            },
                          ),
                  ),
                ],
              ),
            ),
          );
        });
      },
    );
  }

  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800 && size.shortestSide >= 500;
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
            return ErrorView(
              error: provider.error!,
              onRetry: () {
                final token = context.read<AuthProvider>().token;
                provider.fetchDetail(widget.slug, token: token);
                provider.fetchComments(widget.slug);
              },
            );
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
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text("Chọn tập:", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                          SizedBox(
                            width: 280,
                            height: 48,
                            child: TextField(
                              controller: _episodeSearchController,
                              style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w500),
                              textAlignVertical: TextAlignVertical.center,
                              decoration: InputDecoration(
                                hintText: "Tìm kiếm tập phim...",
                                hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 15),
                                prefixIcon: Icon(Icons.search_rounded, color: Theme.of(context).primaryColor, size: 22),
                                suffixIcon: _episodeSearchQuery.isNotEmpty 
                                    ? IconButton(
                                        icon: const Icon(Icons.close_rounded, color: Colors.white54, size: 20),
                                        onPressed: () {
                                          _episodeSearchController.clear();
                                          setState(() {
                                            _episodeSearchQuery = "";
                                          });
                                        },
                                      ) 
                                    : null,
                                filled: true,
                                fillColor: Colors.white.withOpacity(0.1),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 20),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(24),
                                  borderSide: BorderSide(color: Colors.white.withOpacity(0.15), width: 1),
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(24),
                                  borderSide: BorderSide(color: Colors.white.withOpacity(0.15), width: 1),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(24),
                                  borderSide: BorderSide(color: Theme.of(context).primaryColor, width: 2),
                                ),
                              ),
                              onChanged: (val) {
                                setState(() {
                                  _episodeSearchQuery = val.trim().toLowerCase();
                                });
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Expanded(
                        child: Builder(builder: (context) {
                          final serverData = provider.episodes[provider.currentServerIndex].serverData;
                          final filteredEps = serverData.asMap().entries.where((e) => 
                            _episodeSearchQuery.isEmpty || e.value.name.toLowerCase().contains(_episodeSearchQuery)
                          ).toList();
                          
                          return GridView.builder(
                            gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                              maxCrossAxisExtent: 120,
                              childAspectRatio: 2.2,
                              crossAxisSpacing: 12,
                              mainAxisSpacing: 12,
                            ),
                            itemCount: filteredEps.length,
                            itemBuilder: (context, i) {
                              final index = filteredEps[i].key;
                              final ep = filteredEps[i].value;
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
                                        alignment: Alignment.center,
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
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    );
                                  }
                                ),
                              );
                            },
                          );
                        }),
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
    final imageUrl = (movie.posterUrl ?? movie.thumbUrl ?? '').startsWith('http') 
        ? (movie.posterUrl ?? movie.thumbUrl!) 
        : '${provider.domain}${movie.posterUrl ?? movie.thumbUrl}';

    return CustomScrollView(
      slivers: [
        SliverAppBar(
          expandedHeight: MediaQuery.of(context).size.height * 0.45,
          pinned: true,
          backgroundColor: Theme.of(context).scaffoldBackgroundColor,
          elevation: 0,
          flexibleSpace: FlexibleSpaceBar(
            background: Stack(
              fit: StackFit.expand,
              children: [
                if (imageUrl.isNotEmpty)
                  CachedNetworkImage(
                    imageUrl: imageUrl,
                    fit: BoxFit.cover,
                  ),
                Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Colors.black.withOpacity(0.2),
                        Colors.black.withOpacity(0.0),
                        Theme.of(context).scaffoldBackgroundColor,
                      ],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      stops: const [0.0, 0.4, 1.0],
                    ),
                  ),
                ),
                Positioned(
                  bottom: 20,
                  left: 0,
                  right: 0,
                  child: Center(
                    child: GestureDetector(
                      onTap: () => _watchMovie(provider),
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Theme.of(context).primaryColor.withOpacity(0.8),
                          boxShadow: [
                            BoxShadow(
                              color: Theme.of(context).primaryColor.withOpacity(0.5),
                              blurRadius: 20,
                              spreadRadius: 2,
                            )
                          ]
                        ),
                        child: const Icon(Icons.play_arrow_rounded, size: 56, color: Colors.white),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  movie.name,
                  style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.white, height: 1.2),
                ),
                if (movie.originName != null) ...[
                  const SizedBox(height: 6),
                  Text(
                    movie.originName!,
                    style: const TextStyle(fontSize: 16, color: Colors.grey, fontStyle: FontStyle.italic),
                  ),
                ],
                const SizedBox(height: 24),
                
                // Action Row
                Container(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.05),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.white.withOpacity(0.05)),
                  ),
                  child: Row(
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
                          color: provider.isFollowing ? Theme.of(context).primaryColor : Colors.white,
                        ),
                      ),
                      GestureDetector(
                        onTap: () {
                          _showPlaylistModal(context, movie.slug, movie.name, imageUrl);
                        },
                        child: _buildActionButton(Icons.playlist_add_rounded, "Thêm"),
                      ),
                      GestureDetector(
                        onTap: () {
                          _showDownloadModal(context, provider);
                        },
                        child: _buildActionButton(Icons.download_rounded, "Tải về"),
                      ),
                      GestureDetector(
                        onTap: () {
                          Share.share('${AppConfig.baseUrl}phim/${movie.slug}');
                        },
                        child: _buildActionButton(Icons.ios_share_rounded, "Chia sẻ"),
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
                                  const SnackBar(content: Text('Chưa kết nối với TV. Vui lòng bấm biểu tượng Cast để kết nối.')),
                                );
                              }
                            },
                            child: _buildActionButton(
                              isConnected ? Icons.cast_connected_rounded : Icons.cast_rounded,
                              "Chiếu TV",
                              color: isConnected ? Colors.greenAccent : Colors.white,
                            ),
                          );
                        }
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 32),
                
                // Content
                const Text("Nội dung phim", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                const SizedBox(height: 12),
                Text(
                  movie.content?.replaceAll(RegExp(r'<[^>]*>|&[^;]+;'), '') ?? "Đang cập nhật...",
                  style: const TextStyle(color: Colors.white70, height: 1.6, fontSize: 15),
                ),
                
                const SizedBox(height: 32),

                // Actors
                if (provider.peoples != null && provider.peoples!.isNotEmpty) ...[
                  const Text("Diễn viên", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                  const SizedBox(height: 16),
                  SizedBox(
                    height: 140,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: provider.peoples!.length,
                      itemBuilder: (context, index) {
                        final person = provider.peoples![index];
                        return Container(
                          width: 80,
                          margin: const EdgeInsets.only(right: 16),
                          child: Column(
                            children: [
                              Container(
                                width: 70,
                                height: 70,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Colors.grey[800],
                                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 8)],
                                  image: person.profilePath.isNotEmpty 
                                      ? DecorationImage(
                                          image: CachedNetworkImageProvider("https://image.tmdb.org/t/p/w185${person.profilePath}"),
                                          fit: BoxFit.cover,
                                        ) 
                                      : null,
                                ),
                                child: person.profilePath.isEmpty ? const Icon(Icons.person, color: Colors.white54, size: 32) : null,
                              ),
                              const SizedBox(height: 12),
                              Text(
                                person.name,
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                                textAlign: TextAlign.center,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 12),
                ] else if (movie.actor != null && movie.actor!.isNotEmpty) ...[
                  const Text("Diễn viên", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                  const SizedBox(height: 16),
                  SizedBox(
                    height: 120,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: movie.actor!.length,
                      itemBuilder: (context, index) {
                        return Container(
                          width: 80,
                          margin: const EdgeInsets.only(right: 16),
                          child: Column(
                            children: [
                              Container(
                                width: 70,
                                height: 70,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Colors.grey[900],
                                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 8)],
                                ),
                                child: const Icon(Icons.person, color: Colors.white54, size: 32),
                              ),
                              const SizedBox(height: 12),
                              Text(
                                movie.actor![index],
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                                textAlign: TextAlign.center,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 12),
                ],

                // Backdrops
                if (provider.images != null && provider.images!.backdrops.isNotEmpty) ...[
                  const Text("Hình ảnh", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                  const SizedBox(height: 16),
                  SizedBox(
                    height: 160,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: provider.images!.backdrops.length,
                      itemBuilder: (context, index) {
                        final imgPath = provider.images!.backdrops[index].filePath;
                        return Container(
                          width: 280,
                          margin: const EdgeInsets.only(right: 16),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 10)],
                            image: DecorationImage(
                              image: CachedNetworkImageProvider("https://image.tmdb.org/t/p/w780$imgPath"),
                              fit: BoxFit.cover,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 32),
                ],

                // Episodes
                if (provider.episodes.isNotEmpty) ...[
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text("Chọn tập", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                      SizedBox(
                        width: 180,
                        height: 40,
                        child: TextField(
                          controller: _episodeSearchController,
                          style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w500),
                          textAlignVertical: TextAlignVertical.center,
                          decoration: InputDecoration(
                            hintText: "Tìm kiếm tập...",
                            hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 13),
                            prefixIcon: Icon(Icons.search_rounded, color: Theme.of(context).primaryColor, size: 20),
                            suffixIcon: _episodeSearchQuery.isNotEmpty 
                                ? IconButton(
                                    icon: const Icon(Icons.close_rounded, color: Colors.white54, size: 16),
                                    onPressed: () {
                                      _episodeSearchController.clear();
                                      setState(() {
                                        _episodeSearchQuery = "";
                                      });
                                    },
                                  ) 
                                : null,
                            filled: true,
                            fillColor: Colors.white.withOpacity(0.08),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(20),
                              borderSide: BorderSide(color: Colors.white.withOpacity(0.1), width: 1),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(20),
                              borderSide: BorderSide(color: Colors.white.withOpacity(0.1), width: 1),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(20),
                              borderSide: BorderSide(color: Theme.of(context).primaryColor, width: 1.5),
                            ),
                          ),
                          onChanged: (val) {
                            setState(() {
                              _episodeSearchQuery = val.trim().toLowerCase();
                            });
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Builder(builder: (context) {
                    final serverData = provider.episodes[provider.currentServerIndex].serverData;
                    final filteredEps = serverData.asMap().entries.where((e) => 
                      _episodeSearchQuery.isEmpty || e.value.name.toLowerCase().contains(_episodeSearchQuery)
                    ).toList();
                    
                    return ConstrainedBox(
                      constraints: const BoxConstraints(maxHeight: 300),
                      child: GridView.builder(
                        padding: EdgeInsets.zero,
                        shrinkWrap: true,
                        gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                          maxCrossAxisExtent: 100,
                          childAspectRatio: 2.0,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                        ),
                        itemCount: filteredEps.length,
                        itemBuilder: (context, i) {
                          final index = filteredEps[i].key;
                          final ep = filteredEps[i].value;
                          final isSelected = provider.currentEpisodeIndex == index;
                          return GestureDetector(
                            onTap: () {
                              provider.changeEpisode(index, provider.currentServerIndex);
                              _watchMovie(provider);
                            },
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 300),
                              alignment: Alignment.center,
                              decoration: BoxDecoration(
                                gradient: isSelected 
                                  ? LinearGradient(colors: [Theme.of(context).primaryColor, Theme.of(context).primaryColor.withOpacity(0.8)]) 
                                  : LinearGradient(colors: [Colors.white.withOpacity(0.1), Colors.white.withOpacity(0.05)]),
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: isSelected 
                                  ? [BoxShadow(color: Theme.of(context).primaryColor.withOpacity(0.4), blurRadius: 8, spreadRadius: 1)]
                                  : [],
                              ),
                              child: Text(
                                ep.name,
                                style: TextStyle(
                                  color: isSelected ? Colors.white : Colors.white70,
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          );
                        },
                      ),
                    );
                  }),
                ],
                
                const SizedBox(height: 40),
                // Reviews Section
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Text("Đánh giá phim", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                        const SizedBox(width: 8),
                        if (provider.averageRating > 0)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.amber.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.amber.withOpacity(0.5)),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.star_rounded, color: Colors.amber, size: 16),
                                const SizedBox(width: 4),
                                Text(provider.averageRating.toString(), style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ),
                      ],
                    ),
                    TextButton.icon(
                      onPressed: () => _showReviewModal(context, provider),
                      icon: const Icon(Icons.star_half_rounded, color: Colors.amber),
                      label: const Text("Đánh giá", style: TextStyle(color: Colors.amber)),
                    ),
                  ],
                ),
                
                const SizedBox(height: 40),
                const Text("Bình luận", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                const SizedBox(height: 20),
                
                // Comment Input Form
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _commentController,
                        style: const TextStyle(color: Colors.white),
                        maxLines: 3,
                        minLines: 1,
                        decoration: InputDecoration(
                          hintText: "Nhập bình luận...",
                          hintStyle: const TextStyle(color: Colors.white54),
                          filled: true,
                          fillColor: Colors.white.withOpacity(0.05),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide.none,
                          ),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Container(
                      decoration: BoxDecoration(
                        color: Theme.of(context).primaryColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: IconButton(
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
                        icon: Icon(Icons.send_rounded, color: Theme.of(context).primaryColor),
                        padding: const EdgeInsets.all(12),
                      ),
                    )
                  ],
                ),
                const SizedBox(height: 32),
                
                if (provider.comments.isEmpty)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.symmetric(vertical: 32.0),
                      child: Text("Chưa có bình luận nào. Hãy là người đầu tiên!", style: TextStyle(color: Colors.grey)),
                    ),
                  )
                else
                  ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: provider.comments.length,
                    itemBuilder: (context, index) {
                      final c = provider.comments[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 24.0),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            CircleAvatar(
                              radius: 20,
                              backgroundColor: Colors.white.withOpacity(0.1),
                              child: Text(
                                c.userName.isNotEmpty ? c.userName[0].toUpperCase() : "?", 
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)
                              ),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Text(c.userName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15)),
                                      const SizedBox(width: 12),
                                      Text(c.timeAgo, style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 12)),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  Text(c.content, style: const TextStyle(color: Colors.white70, fontSize: 14, height: 1.4)),
                                ],
                              ),
                            )
                          ],
                        ),
                      );
                    },
                  ),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ],
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
              Icon(Icons.cast_connected, color: Theme.of(context).primaryColor, size: 48),
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
                      color: Theme.of(context).primaryColor.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: Row(
                      children: [
                        IconButton(
                          iconSize: 48,
                          icon: Icon(Icons.play_arrow, color: Theme.of(context).primaryColor),
                          onPressed: () => TvRemoteService().sendPlayerControl('play'),
                        ),
                        IconButton(
                          iconSize: 48,
                          icon: Icon(Icons.pause, color: Theme.of(context).primaryColor),
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
                  backgroundColor: Theme.of(context).primaryColor,
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

  void _showReviewModal(BuildContext context, DetailProvider provider) {
    int rating = 5;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.grey[900],
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setStateModal) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
                left: 16, right: 16, top: 24,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text("Đánh giá phim", style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(5, (index) {
                      return IconButton(
                        icon: Icon(
                          index < rating ? Icons.star_rounded : Icons.star_border_rounded,
                          color: Colors.amber,
                          size: 48,
                        ),
                        onPressed: () {
                          setStateModal(() {
                            rating = index + 1;
                          });
                        },
                      );
                    }),
                  ),
                  const SizedBox(height: 32),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.amber,
                        foregroundColor: Colors.black,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: () async {
                        final token = context.read<AuthProvider>().token;
                        if (token == null) {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Vui lòng đăng nhập để đánh giá')));
                          return;
                        }
                        final success = await provider.postReview(token, provider.movie!.slug, rating, "");
                        if (success && mounted) {
                          provider.fetchReviews(provider.movie!.slug); // Refresh average rating
                          Navigator.pop(context);
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cảm ơn bạn đã đánh giá!')));
                        } else {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lỗi gửi đánh giá.')));
                        }
                      },
                      child: const Text("GỬI ĐÁNH GIÁ", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                    ),
                  ),
                  const SizedBox(height: 32),
                ],
              ),
            );
          }
        );
      },
    );
  }
}
