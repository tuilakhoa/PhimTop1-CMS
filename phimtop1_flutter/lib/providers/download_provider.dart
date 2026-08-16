import 'dart:io';
import 'dart:async';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:dio/dio.dart';
import 'package:flutter_background/flutter_background.dart';
import '../models/download_task.dart';

class DownloadProvider extends ChangeNotifier {
  final List<DownloadTask> _tasks = [];
  List<DownloadTask> get tasks => _tasks;

  static const String _prefsKey = 'download_tasks_v1';
  bool _isDownloading = false;
  final Dio _dio = Dio();
  CancelToken? _cancelToken;

  DownloadProvider() {
    _loadTasks();
  }

  Future<void> _loadTasks() async {
    final prefs = await SharedPreferences.getInstance();
    final tasksJson = prefs.getStringList(_prefsKey) ?? [];
    _tasks.clear();
    for (var jsonStr in tasksJson) {
      try {
        final task = DownloadTask.fromJson(jsonStr);
        if (task.status == DownloadStatus.downloading || task.status == DownloadStatus.pending) {
          task.status = DownloadStatus.failed;
        }
        _tasks.add(task);
      } catch (e) {
        debugPrint("Error parsing download task: $e");
      }
    }
    notifyListeners();
  }

  Future<void> _saveTasks() async {
    final prefs = await SharedPreferences.getInstance();
    final tasksJson = _tasks.map((t) => t.toJson()).toList();
    await prefs.setStringList(_prefsKey, tasksJson);
    notifyListeners();
  }

  DownloadTask? getTask(String id) {
    try {
      return _tasks.firstWhere((t) => t.id == id);
    } catch (e) {
      return null;
    }
  }

  Future<void> startDownload({
    required String movieSlug,
    required String movieName,
    required String episodeSlug,
    required String episodeName,
    required String thumbUrl,
    required String m3u8Url,
    required double totalDurationSeconds,
  }) async {
    final id = '${movieSlug}_$episodeSlug';
    
    if (getTask(id) != null && getTask(id)!.status == DownloadStatus.completed) {
      return;
    }

    final directory = await getApplicationDocumentsDirectory();
    final savePath = '${directory.path}/$id/index.m3u8';

    final task = DownloadTask(
      id: id,
      movieSlug: movieSlug,
      movieName: movieName,
      episodeSlug: episodeSlug,
      episodeName: episodeName,
      thumbUrl: thumbUrl,
      m3u8Url: m3u8Url,
      savePath: savePath,
      status: DownloadStatus.pending,
    );

    _tasks.removeWhere((t) => t.id == id);
    _tasks.add(task);
    await _saveTasks();

    _processQueue();
  }

  String _formatBytes(int bytes) {
    if (bytes <= 0) return "0 B";
    const suffixes = ["B", "KB", "MB", "GB", "TB"];
    int i = (log(bytes) / log(1024)).floor();
    return '${(bytes / pow(1024, i)).toStringAsFixed(1)} ${suffixes[i]}';
  }

  String _formatTime(int seconds) {
    if (seconds < 0) return "00:00";
    int m = seconds ~/ 60;
    int s = seconds % 60;
    if (m >= 60) {
      int h = m ~/ 60;
      m = m % 60;
      return '${h.toString().padLeft(2, '0')}:${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
    }
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  Future<void> _processQueue() async {
    if (_isDownloading) return;

    final pendingTask = _tasks.firstWhere(
      (t) => t.status == DownloadStatus.pending,
      orElse: () => DownloadTask(id: '', movieSlug: '', movieName: '', episodeSlug: '', episodeName: '', thumbUrl: '', m3u8Url: '', savePath: ''),
    );

    if (pendingTask.id.isEmpty) {
      if (FlutterBackground.isBackgroundExecutionEnabled) {
        await FlutterBackground.disableBackgroundExecution();
      }
      return;
    }

    _isDownloading = true;
    pendingTask.status = DownloadStatus.downloading;
    pendingTask.progress = 0.0;
    pendingTask.speed = 'Đang tính toán...';
    pendingTask.timeRemaining = '--:--';
    notifyListeners();

    _cancelToken = CancelToken();

    try {
      if (!FlutterBackground.isBackgroundExecutionEnabled) {
        bool initialized = await FlutterBackground.initialize(androidConfig: const FlutterBackgroundAndroidConfig(
          notificationTitle: "PhimTop1 Đang Tải",
          notificationText: "Quá trình tải phim đang diễn ra trong nền",
          notificationImportance: AndroidNotificationImportance.normal,
          notificationIcon: AndroidResource(name: 'ic_launcher', defType: 'mipmap'),
        ));
        if (initialized) {
          await FlutterBackground.enableBackgroundExecution();
        }
      }
    } catch (e) {
      debugPrint("Background execution enable failed: $e");
    }

    try {
      final directory = await getApplicationDocumentsDirectory();
      final movieDir = Directory('${directory.path}/${pendingTask.id}');
      if (await movieDir.exists()) {
        await movieDir.delete(recursive: true);
      }
      await movieDir.create(recursive: true);

      String m3u8Url = pendingTask.m3u8Url;
      Uri baseUri = Uri.parse(m3u8Url);

      Response response = await _dio.get(m3u8Url, cancelToken: _cancelToken);
      String m3u8Content = response.data.toString();

      // Check if it's a master playlist
      if (m3u8Content.contains('#EXT-X-STREAM-INF')) {
        final lines = m3u8Content.split('\n');
        for (int i = 0; i < lines.length; i++) {
          if (lines[i].startsWith('#EXT-X-STREAM-INF') && i + 1 < lines.length) {
            String subPlaylist = lines[i + 1].trim();
            baseUri = baseUri.resolve(subPlaylist);
            response = await _dio.get(baseUri.toString(), cancelToken: _cancelToken);
            m3u8Content = response.data.toString();
            break;
          }
        }
      }

      final lines = m3u8Content.split('\n');
      List<String> tsFiles = [];
      for (var line in lines) {
        if (!line.startsWith('#') && line.trim().isNotEmpty) {
          tsFiles.add(line.trim());
        }
      }

      if (tsFiles.isEmpty) {
        throw Exception("No TS files found in playlist");
      }

      List<String> newM3u8Lines = [];
      int downloaded = 0;
      int totalSegments = tsFiles.length;
      int totalBytesDownloaded = 0;
      DateTime downloadStartTime = DateTime.now();

      for (var line in lines) {
        if (!line.startsWith('#') && line.trim().isNotEmpty) {
          String tsUrl = line.trim();
          Uri tsUri = baseUri.resolve(tsUrl);
          
          String tsName = 'segment_$downloaded.ts';
          String tsPath = '${movieDir.path}/$tsName';

          int fileBytes = 0;
          DateTime lastUpdateTime = DateTime.now();

          await _dio.download(
            tsUri.toString(), 
            tsPath, 
            cancelToken: _cancelToken,
            onReceiveProgress: (received, total) {
              int delta = received - fileBytes;
              fileBytes = received;
              totalBytesDownloaded += delta;

              DateTime now = DateTime.now();
              if (now.difference(lastUpdateTime).inMilliseconds > 1000) {
                 lastUpdateTime = now;
                 double elapsedSeconds = now.difference(downloadStartTime).inMilliseconds / 1000.0;
                 if (elapsedSeconds > 0) {
                    double speedBps = totalBytesDownloaded / elapsedSeconds;
                    
                    double progress = (downloaded + (total > 0 ? (received / total) : 0)) / totalSegments;
                    
                    if (progress > 0) {
                      double estimatedTotalBytes = totalBytesDownloaded / progress;
                      double remainingBytes = estimatedTotalBytes - totalBytesDownloaded;
                      double remainingSeconds = remainingBytes / speedBps;
                      
                      pendingTask.speed = _formatBytes(speedBps.round()) + "/s";
                      pendingTask.timeRemaining = _formatTime(remainingSeconds.round());
                      pendingTask.progress = progress;
                      notifyListeners();
                    }
                 }
              }
            }
          );
          
          newM3u8Lines.add(tsName);
          downloaded++;
          pendingTask.progress = downloaded / totalSegments;
          notifyListeners();
        } else {
          newM3u8Lines.add(line);
        }
      }

      final localM3u8File = File('${movieDir.path}/index.m3u8');
      await localM3u8File.writeAsString(newM3u8Lines.join('\n'));

      pendingTask.savePath = localM3u8File.path;
      pendingTask.status = DownloadStatus.completed;
      pendingTask.progress = 1.0;
      pendingTask.speed = '';
      pendingTask.timeRemaining = '';

      _isDownloading = false;
      await _saveTasks();
      _processQueue();

    } catch (e) {
      if (e is DioException && CancelToken.isCancel(e)) {
        pendingTask.status = DownloadStatus.canceled;
      } else {
        pendingTask.status = DownloadStatus.failed;
        debugPrint("Download Error: $e");
      }
      _isDownloading = false;
      await _saveTasks();
      _processQueue();
    }
  }

  Future<void> cancelDownload(String id) async {
    final task = getTask(id);
    if (task != null && task.status == DownloadStatus.downloading) {
      _cancelToken?.cancel();
    } else if (task != null) {
      deleteDownload(id);
    }
  }

  Future<void> deleteDownload(String id) async {
    final task = getTask(id);
    if (task != null) {
      final directory = await getApplicationDocumentsDirectory();
      final movieDir = Directory('${directory.path}/$id');
      if (await movieDir.exists()) {
        await movieDir.delete(recursive: true);
      }
      _tasks.remove(task);
      await _saveTasks();
    }
  }
}
