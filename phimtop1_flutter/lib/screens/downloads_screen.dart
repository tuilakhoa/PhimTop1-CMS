import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/download_provider.dart';
import '../models/download_task.dart';
import 'watch_movie_screen.dart';

class DownloadsScreen extends StatelessWidget {
  const DownloadsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: const Text("Phim đã tải", style: TextStyle(color: Colors.white)),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: Consumer<DownloadProvider>(
        builder: (context, provider, child) {
          if (provider.tasks.isEmpty) {
            return const Center(
              child: Text(
                "Chưa có phim nào được tải xuống.",
                style: TextStyle(color: Colors.grey, fontSize: 16),
              ),
            );
          }

          return ListView.builder(
            itemCount: provider.tasks.length,
            itemBuilder: (context, index) {
              final task = provider.tasks[index];
              return _buildDownloadItem(context, task, provider);
            },
          );
        },
      ),
    );
  }

  Widget _buildDownloadItem(BuildContext context, DownloadTask task, DownloadProvider provider) {
    return Card(
      color: Colors.white.withOpacity(0.05),
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        contentPadding: const EdgeInsets.all(12),
        leading: ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: SizedBox(
            width: 80,
            height: 120,
            child: CachedNetworkImage(
              imageUrl: task.thumbUrl,
              fit: BoxFit.cover,
              errorWidget: (context, url, error) => const Icon(Icons.movie, color: Colors.grey),
            ),
          ),
        ),
        title: Text(task.movieName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(task.episodeName, style: const TextStyle(color: Colors.grey)),
            const SizedBox(height: 8),
            if (task.status == DownloadStatus.downloading)
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  LinearProgressIndicator(value: task.progress, backgroundColor: Colors.white12),
                  const SizedBox(height: 4),
                  Text('${(task.progress * 100).toStringAsFixed(1)}%', style: const TextStyle(color: Colors.amber, fontSize: 12)),
                ],
              )
            else if (task.status == DownloadStatus.completed)
              const Text("Đã tải xong", style: TextStyle(color: Colors.green, fontSize: 12))
            else if (task.status == DownloadStatus.failed)
              const Text("Tải lỗi", style: TextStyle(color: Colors.red, fontSize: 12))
            else if (task.status == DownloadStatus.pending)
              const Text("Đang chờ...", style: TextStyle(color: Colors.grey, fontSize: 12)),
          ],
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (task.status == DownloadStatus.completed)
              IconButton(
                icon: const Icon(Icons.play_circle_fill, color: Colors.white, size: 36),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => WatchMovieScreen(
                        m3u8Link: task.savePath,
                        title: task.movieName,
                        movieSlug: task.movieSlug,
                        episodeName: task.episodeName,
                        episodeSlug: task.episodeSlug,
                        thumbUrl: task.thumbUrl,
                      ),
                    ),
                  );
                },
              ),
            IconButton(
              icon: const Icon(Icons.delete, color: Colors.redAccent),
              onPressed: () {
                showDialog(
                  context: context,
                  builder: (context) => AlertDialog(
                    backgroundColor: Colors.grey[900],
                    title: const Text('Xóa phim', style: TextStyle(color: Colors.white)),
                    content: const Text('Bạn có chắc chắn muốn xóa bản tải xuống này?', style: TextStyle(color: Colors.white70)),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(context), child: const Text('Hủy', style: TextStyle(color: Colors.grey))),
                      TextButton(
                        onPressed: () {
                          provider.cancelDownload(task.id);
                          provider.deleteDownload(task.id);
                          Navigator.pop(context);
                        },
                        child: const Text('Xóa', style: TextStyle(color: Colors.red)),
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
