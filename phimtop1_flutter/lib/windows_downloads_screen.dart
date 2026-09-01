import 'package:flutter/material.dart' show ThemeMode, Material, Brightness, CrossAxisAlignment, MainAxisAlignment, BorderRadius;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../providers/download_provider.dart';
import 'windows_detail_screen.dart';

class WindowsDownloadsScreen extends StatelessWidget {
  const WindowsDownloadsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Tải Xuống', style: TextStyle(color: Colors.white)),
      ),
      content: Consumer<DownloadProvider>(
        builder: (context, downloadProvider, child) {
          final tasks = downloadProvider.tasks;
          
          if (tasks.isEmpty) {
            return const Center(child: Text('Chưa có nội dung tải xuống', style: TextStyle(color: Colors.white)));
          }
          
          return ListView.builder(
            padding: const EdgeInsets.all(24.0),
            itemCount: tasks.length,
            itemBuilder: (context, index) {
              final task = tasks[index];
              final progress = task.progress;
              final isCompleted = task.status == DownloadStatus.completed;
              
              return Card(
                margin: const EdgeInsets.only(bottom: 16),
                backgroundColor: const Color(0xFF161623),
                child: ListTile(
                  leading: const Icon(FluentIcons.video, size: 32, color: Color(0xFF6B48FF)),
                  title: Text(task.movieName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(task.episodeName, style: const TextStyle(color: Colors.grey)),
                      const SizedBox(height: 8),
                      if (!isCompleted) ProgressBar(value: progress * 100),
                      if (!isCompleted) Text('${(progress * 100).toStringAsFixed(1)}%', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                      if (isCompleted) const Text('Đã tải xong', style: TextStyle(color: Colors.green, fontSize: 12)),
                    ],
                  ),
                  trailing: Row(
                    children: [
                      if (!isCompleted) IconButton(
                        icon: Icon(task.status == DownloadStatus.paused ? FluentIcons.play : FluentIcons.pause),
                        onPressed: () {
                          if (task.status == DownloadStatus.paused) {
                            downloadProvider.resumeDownload(task.id);
                          } else {
                            downloadProvider.pauseDownload(task.id);
                          }
                        },
                      ),
                      IconButton(
                        icon: const Icon(FluentIcons.delete, color: Colors.red),
                        onPressed: () => downloadProvider.deleteDownload(task.id),
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
