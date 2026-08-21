import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/auth_provider.dart';
import '../providers/playlist_provider.dart';
import '../providers/detail_provider.dart';
import '../providers/download_provider.dart';
import '../models/download_task.dart';
import '../services/tv_remote_service.dart';

void showPlaylistModal(BuildContext context, String movieSlug, String movieName, String thumbUrl) {
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
                  padding: EdgeInsets.all(16),
                  decoration: BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white12))),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text("Danh sách phát", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                      IconButton(icon: Icon(Icons.close, color: Colors.grey), onPressed: () => Navigator.pop(context)),
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
                                  title: Text(pl.name, style: TextStyle(color: Colors.white)),
                                  trailing: hasMovie
                                      ? Icon(Icons.check_circle, color: Colors.green)
                                      : ElevatedButton(
                                          style: ElevatedButton.styleFrom(backgroundColor: Theme.of(context).primaryColor, foregroundColor: Colors.white),
                                          onPressed: () async {
                                            final success = await provider.addToPlaylist(pl.id, movieSlug, movieName, thumbUrl);
                                            if (success) {
                                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã thêm vào danh sách')));
                                              Navigator.pop(context);
                                            }
                                          },
                                          child: Text("Thêm"),
                                        ),
                                );
                              },
                            ),
                ),
                Container(
                  padding: EdgeInsets.all(16),
                  decoration: BoxDecoration(border: Border(top: BorderSide(color: Colors.white12))),
                  child: Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: TextEditingController(),
                          style: TextStyle(color: Colors.white),
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

void showDownloadModal(BuildContext context, DetailProvider provider) {
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
                  padding: EdgeInsets.all(16),
                  decoration: BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white12))),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text("Tải xuống ngoại tuyến", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                      IconButton(icon: Icon(Icons.close, color: Colors.grey), onPressed: () => Navigator.pop(context)),
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
                              icon: Icon(Icons.download, color: Colors.white),
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
                                  if (message != null && context.mounted) {
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
                                trailing = Icon(Icons.check_circle, color: Colors.green);
                              } else if (task.status == DownloadStatus.downloading) {
                                trailing = Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text('${(task.progress * 100).toStringAsFixed(0)}%', style: TextStyle(color: Colors.amber)),
                                    const SizedBox(width: 8),
                                    IconButton(
                                      icon: Icon(Icons.stop_circle, color: Colors.red),
                                      onPressed: () => downloadProvider.cancelDownload(taskId),
                                    ),
                                  ],
                                );
                              } else if (task.status == DownloadStatus.pending) {
                                trailing = Text('Chờ tải...', style: TextStyle(color: Colors.grey));
                              } else if (task.status == DownloadStatus.failed) {
                                trailing = IconButton(
                                  icon: Icon(Icons.refresh, color: Colors.red),
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
                                    if (message != null && context.mounted) {
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
                              title: Text(ep.name, style: TextStyle(color: Colors.white)),
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

void showRemoteControlModal(BuildContext context, String title) {
  final isDark = Theme.of(context).brightness == Brightness.dark;
  final textColor = isDark ? Colors.white : Colors.black87;
  final subtitleColor = isDark ? Colors.white70 : Colors.black54;

  showModalBottomSheet(
    context: context,
    backgroundColor: Colors.grey[900],
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
    builder: (context) {
      return Padding(
        padding: EdgeInsets.symmetric(horizontal: 24.0, vertical: 32.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40,
              height: 4,
              margin: EdgeInsets.only(bottom: 24),
              decoration: BoxDecoration(
                color: Colors.grey[700],
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Icon(Icons.cast_connected, color: Theme.of(context).primaryColor, size: 48),
            const SizedBox(height: 16),
            Text(
              "Đang chiếu trên TV",
              style: TextStyle(color: subtitleColor, fontSize: 14),
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: TextStyle(color: textColor, fontSize: 20, fontWeight: FontWeight.bold),
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
                  icon: Icon(Icons.replay_10, color: textColor),
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
                  icon: Icon(Icons.forward_10, color: textColor),
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
                foregroundColor: textColor,
                minimumSize: const Size(double.infinity, 56),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              icon: Icon(Icons.stop_circle_outlined, size: 28),
              label: Text("Dừng Phát", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
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

void showReviewModal(BuildContext context, DetailProvider provider) {
  int rating = 5;
  final isDark = Theme.of(context).brightness == Brightness.dark;
  final textColor = isDark ? Colors.white : Colors.black87;

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
                Text("Đánh giá phim", style: TextStyle(color: textColor, fontSize: 20, fontWeight: FontWeight.bold)),
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
                      if (success && context.mounted) {
                        provider.fetchReviews(provider.movie!.slug); // Refresh average rating
                        Navigator.pop(context);
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cảm ơn bạn đã đánh giá!')));
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lỗi gửi đánh giá.')));
                      }
                    },
                    child: Text("GỬI ĐÁNH GIÁ", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
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
