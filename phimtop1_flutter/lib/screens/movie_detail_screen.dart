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
import '../services/mini_player_service.dart';
import 'package:share_plus/share_plus.dart';
import '../services/tv_remote_service.dart';
import '../providers/download_provider.dart';
import '../models/download_task.dart';
import '../widgets/error_view.dart';
import '../widgets/movie_detail_modals.dart';

class MovieDetailScreen extends StatefulWidget {
  final String slug;
  
  const MovieDetailScreen({super.key, required this.slug});

  @override
  State<MovieDetailScreen> createState() => _MovieDetailScreenState();
}

class _MovieDetailScreenState extends State<MovieDetailScreen> {
  Color get _textColor => Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black87;
  Color get _subtitleColor => Theme.of(context).brightness == Brightness.dark ? Colors.white70 : Colors.black54;
  Color get _iconColor => Theme.of(context).brightness == Brightness.dark ? Colors.white54 : Colors.black45;
  Color get _bgOpacity => Theme.of(context).brightness == Brightness.dark ? Colors.white.withOpacity(0.05) : Colors.black.withOpacity(0.03);
  Color get __textColor => Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black87;
  Color get __subtitleColor => Theme.of(context).brightness == Brightness.dark ? Colors.white70 : Colors.black54;
  Color get __iconColor => Theme.of(context).brightness == Brightness.dark ? Colors.white54 : Colors.black45;
  Color get __bgOpacity => Theme.of(context).brightness == Brightness.dark ? Colors.white.withOpacity(0.05) : Colors.black.withOpacity(0.03);

  final TextEditingController _commentController = TextEditingController();
  final TextEditingController _episodeSearchController = TextEditingController();
  String _episodeSearchQuery = "";
  bool _isContentExpanded = false;

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
            MiniPlayerService().hideMiniPlayer();
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
            ).then((result) {
              if (result == 'next_episode') {
                if (provider.currentEpisodeIndex < provider.episodes[provider.currentServerIndex].serverData.length - 1) {
                  provider.changeEpisode(provider.currentEpisodeIndex + 1, provider.currentServerIndex);
                  _watchMovie(provider);
                }
              }
            });
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

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
              padding: EdgeInsets.all(32),
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
                            padding: EdgeInsets.symmetric(vertical: 16, horizontal: 24),
                            decoration: BoxDecoration(
                              color: hasFocus ? _textColor : Theme.of(context).primaryColor,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.play_arrow, color: hasFocus ? Colors.black : _textColor),
                                const SizedBox(width: 8),
                                Text("Xem Phim", style: TextStyle(color: hasFocus ? Colors.black : _textColor, fontSize: 18, fontWeight: FontWeight.bold)),
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
                padding: EdgeInsets.only(top: 32, right: 32, bottom: 32),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      movie.name,
                      style: TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: _textColor),
                    ),
                    if (movie.originName != null) ...[
                      const SizedBox(height: 8),
                      Text(
                        movie.originName!,
                        style: TextStyle(fontSize: 24, color: Colors.grey),
                      ),
                    ],
                    const SizedBox(height: 24),
                    GestureDetector(
                      onTap: () {
                        setState(() {
                          _isContentExpanded = !_isContentExpanded;
                        });
                      },
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          AnimatedSize(
                            duration: const Duration(milliseconds: 300),
                            curve: Curves.easeInOut,
                            child: Text(
                              movie.content?.replaceAll(RegExp(r'<[^>]*>|&[^;]+;'), '') ?? "Đang cập nhật...",
                              style: TextStyle(color: _subtitleColor, height: 1.5, fontSize: 18),
                              maxLines: _isContentExpanded ? null : 4,
                              overflow: _isContentExpanded ? TextOverflow.visible : TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _isContentExpanded ? "Thu gọn" : "Xem thêm",
                            style: TextStyle(color: Theme.of(context).primaryColor, fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 32),
                    if (provider.episodes.isNotEmpty) ...[
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text("Chọn tập:", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
                          SizedBox(
                            width: 280,
                            height: 48,
                            child: TextField(
                              controller: _episodeSearchController,
                              style: TextStyle(color: _textColor, fontSize: 16, fontWeight: FontWeight.w500),
                              textAlignVertical: TextAlignVertical.center,
                              decoration: InputDecoration(
                                hintText: "Tìm kiếm tập phim...",
                                hintStyle: TextStyle(color: _textColor.withOpacity(0.4), fontSize: 15),
                                prefixIcon: Icon(Icons.search_rounded, color: Theme.of(context).primaryColor, size: 22),
                                suffixIcon: _episodeSearchQuery.isNotEmpty 
                                    ? IconButton(
                                        icon: Icon(Icons.close_rounded, color: _iconColor, size: 20),
                                        onPressed: () {
                                          _episodeSearchController.clear();
                                          setState(() {
                                            _episodeSearchQuery = "";
                                          });
                                        },
                                      ) 
                                    : null,
                                filled: true,
                                fillColor: _bgOpacity,
                                contentPadding: EdgeInsets.symmetric(horizontal: 20),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(24),
                                  borderSide: BorderSide(color: _textColor.withOpacity(0.15), width: 1),
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(24),
                                  borderSide: BorderSide(color: _textColor.withOpacity(0.15), width: 1),
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
                                          color: hasFocus ? _textColor : _bgOpacity,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(
                                            color: hasFocus ? _textColor : Colors.transparent,
                                            width: 2,
                                          ),
                                        ),
                                        child: Text(
                                          ep.name,
                                          style: TextStyle(
                                            color: hasFocus ? Colors.black : _textColor,
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

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
                        padding: EdgeInsets.all(4),
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
                        child: Icon(Icons.play_arrow_rounded, size: 56, color: _textColor),
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
            padding: EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  movie.name,
                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: _textColor, height: 1.2),
                ),
                if (movie.originName != null) ...[
                  const SizedBox(height: 6),
                  Text(
                    movie.originName!,
                    style: TextStyle(fontSize: 16, color: Colors.grey, fontStyle: FontStyle.italic),
                  ),
                ],
                const SizedBox(height: 24),
                
                // Action Row
                Container(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  decoration: BoxDecoration(
                    color: _bgOpacity,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: _bgOpacity),
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
                          color: provider.isFollowing ? Theme.of(context).primaryColor : _textColor,
                        ),
                      ),
                      GestureDetector(
                        onTap: () {
                          showPlaylistModal(context, movie.slug, movie.name, imageUrl);
                        },
                        child: _buildActionButton(Icons.playlist_add_rounded, "Thêm"),
                      ),
                      GestureDetector(
                        onTap: () {
                          showDownloadModal(context, provider);
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
                                      showRemoteControlModal(context, '${movie.name} - ${episode.name}');
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
                              color: isConnected ? Colors.greenAccent : _textColor,
                            ),
                          );
                        }
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 32),
                
                // Content
                Text("Nội dung phim", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
                const SizedBox(height: 12),
                GestureDetector(
                  onTap: () {
                    setState(() {
                      _isContentExpanded = !_isContentExpanded;
                    });
                  },
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      AnimatedSize(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeInOut,
                        child: Text(
                          movie.content?.replaceAll(RegExp(r'<[^>]*>|&[^;]+;'), '') ?? "Đang cập nhật...",
                          style: TextStyle(color: _subtitleColor, height: 1.6, fontSize: 15),
                          maxLines: _isContentExpanded ? null : 4,
                          overflow: _isContentExpanded ? TextOverflow.visible : TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _isContentExpanded ? "Thu gọn" : "Xem thêm",
                        style: TextStyle(color: Theme.of(context).primaryColor, fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: 32),

                // Actors
                if (provider.peoples != null && provider.peoples!.isNotEmpty) ...[
                  Text("Diễn viên", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
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
                          margin: EdgeInsets.only(right: 16),
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
                                child: person.profilePath.isEmpty ? Icon(Icons.person, color: _iconColor, size: 32) : null,
                              ),
                              const SizedBox(height: 12),
                              Text(
                                person.name,
                                style: TextStyle(color: _textColor, fontSize: 12, fontWeight: FontWeight.w600),
                                textAlign: TextAlign.center,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              if (person.character.isNotEmpty)
                                Text(
                                  person.character,
                                  style: TextStyle(color: _iconColor, fontSize: 10),
                                  textAlign: TextAlign.center,
                                  maxLines: 1,
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
                  Text("Diễn viên", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
                  const SizedBox(height: 16),
                  SizedBox(
                    height: 120,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: movie.actor!.length,
                      itemBuilder: (context, index) {
                        return Container(
                          width: 80,
                          margin: EdgeInsets.only(right: 16),
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
                                child: Icon(Icons.person, color: _iconColor, size: 32),
                              ),
                              const SizedBox(height: 12),
                              Text(
                                movie.actor![index],
                                style: TextStyle(color: _textColor, fontSize: 12, fontWeight: FontWeight.w600),
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
                  Text("Hình ảnh", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
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
                          margin: EdgeInsets.only(right: 16),
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
                      Text("Chọn tập", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
                      SizedBox(
                        width: 180,
                        height: 40,
                        child: TextField(
                          controller: _episodeSearchController,
                          style: TextStyle(color: _textColor, fontSize: 14, fontWeight: FontWeight.w500),
                          textAlignVertical: TextAlignVertical.center,
                          decoration: InputDecoration(
                            hintText: "Tìm kiếm tập...",
                            hintStyle: TextStyle(color: _textColor.withOpacity(0.4), fontSize: 13),
                            prefixIcon: Icon(Icons.search_rounded, color: Theme.of(context).primaryColor, size: 20),
                            suffixIcon: _episodeSearchQuery.isNotEmpty 
                                ? IconButton(
                                    icon: Icon(Icons.close_rounded, color: _iconColor, size: 16),
                                    onPressed: () {
                                      _episodeSearchController.clear();
                                      setState(() {
                                        _episodeSearchQuery = "";
                                      });
                                    },
                                  ) 
                                : null,
                            filled: true,
                            fillColor: _bgOpacity,
                            contentPadding: EdgeInsets.symmetric(horizontal: 16),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(20),
                              borderSide: BorderSide(color: _textColor.withOpacity(0.1), width: 1),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(20),
                              borderSide: BorderSide(color: _textColor.withOpacity(0.1), width: 1),
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
                                color: isSelected ? null : _bgOpacity,
                                gradient: isSelected 
                                  ? LinearGradient(colors: [Theme.of(context).primaryColor, Theme.of(context).primaryColor.withOpacity(0.8)]) 
                                  : null,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: isSelected 
                                  ? [BoxShadow(color: Theme.of(context).primaryColor.withOpacity(0.4), blurRadius: 8, spreadRadius: 1)]
                                  : [],
                              ),
                              child: Text(
                                ep.name,
                                style: TextStyle(
                                  color: isSelected ? _textColor : _subtitleColor,
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
                        Text("Đánh giá phim", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
                        const SizedBox(width: 8),
                        if (provider.averageRating > 0)
                          Container(
                            padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.amber.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.amber.withOpacity(0.5)),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.star_rounded, color: Colors.amber, size: 16),
                                const SizedBox(width: 4),
                                Text(provider.averageRating.toString(), style: TextStyle(color: Colors.amber, fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ),
                      ],
                    ),
                    TextButton.icon(
                      onPressed: () => showReviewModal(context, provider),
                      icon: Icon(Icons.star_half_rounded, color: Colors.amber),
                      label: Text("Đánh giá", style: TextStyle(color: Colors.amber)),
                    ),
                  ],
                ),
                
                const SizedBox(height: 40),
                Text("Bình luận", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: _textColor)),
                const SizedBox(height: 20),
                
                // Comment Input Form
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _commentController,
                        style: TextStyle(color: _textColor),
                        maxLines: 3,
                        minLines: 1,
                        decoration: InputDecoration(
                          hintText: "Nhập bình luận...",
                          hintStyle: TextStyle(color: _iconColor),
                          filled: true,
                          fillColor: _bgOpacity,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide.none,
                          ),
                          contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 16),
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
                        padding: EdgeInsets.all(12),
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
                  Container(
                    constraints: const BoxConstraints(maxHeight: 400),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.02),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.white.withOpacity(0.05)),
                    ),
                    padding: const EdgeInsets.all(16),
                    child: ListView.builder(
                      shrinkWrap: true,
                      physics: const BouncingScrollPhysics(),
                      itemCount: provider.comments.length,
                      itemBuilder: (context, index) {
                        final c = provider.comments[index];
                        return Padding(
                          padding: EdgeInsets.only(bottom: index == provider.comments.length - 1 ? 0 : 24.0),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Stack(
                                alignment: Alignment.center,
                                children: [
                                  CircleAvatar(
                                    radius: 20,
                                    backgroundColor: Colors.white.withOpacity(0.1),
                                    child: Text(
                                      c.userName.isNotEmpty ? c.userName[0].toUpperCase() : "?", 
                                      style: TextStyle(color: _textColor, fontWeight: FontWeight.bold)
                                    ),
                                  ),
                                  if (c.activeFrameUrl != null && c.activeFrameUrl!.isNotEmpty)
                                    Positioned.fill(
                                      child: CachedNetworkImage(
                                        imageUrl: c.activeFrameUrl!,
                                        fit: BoxFit.cover,
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Text(c.userName, style: TextStyle(color: _textColor, fontWeight: FontWeight.bold, fontSize: 15)),
                                        const SizedBox(width: 12),
                                        Text(c.timeAgo, style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 12)),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Text(c.content, style: TextStyle(color: _subtitleColor, fontSize: 14, height: 1.4)),
                                  ],
                                ),
                              )
                            ],
                          ),
                        );
                      },
                    ),
                  ),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildActionButton(IconData icon, String label, {Color? color}) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final fallbackColor = isDark ? Colors.white : Colors.black87;
    final _subtitleColor = isDark ? Colors.white70 : Colors.black54;

    return Column(
      children: [
        Icon(icon, color: color ?? fallbackColor, size: 24),
        const SizedBox(height: 4),
        Text(label, style: TextStyle(color: _subtitleColor, fontSize: 12)),
      ],
    );
  }

}
