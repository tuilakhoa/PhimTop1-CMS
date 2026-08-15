import 'dart:io';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:ffmpeg_kit_flutter_min/ffmpeg_kit.dart';
import 'package:ffmpeg_kit_flutter_min/ffmpeg_kit_config.dart';
import 'package:ffmpeg_kit_flutter_min/return_code.dart';
import 'package:ffmpeg_kit_flutter_min/session.dart';
import 'package:ffmpeg_kit_flutter_min/statistics.dart';
import '../models/download_task.dart';

class DownloadProvider extends ChangeNotifier {
  final List<DownloadTask> _tasks = [];
  List<DownloadTask> get tasks => _tasks;

  static const String _prefsKey = 'download_tasks_v1';
  bool _isDownloading = false;

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
        // Reset downloading states if app was killed
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
    required double totalDurationSeconds, // Needed for progress
  }) async {
    final id = '${movieSlug}_$episodeSlug';
    
    if (getTask(id) != null && getTask(id)!.status == DownloadStatus.completed) {
      return; // Already downloaded
    }

    final directory = await getApplicationDocumentsDirectory();
    final savePath = '${directory.path}/$id.mp4';

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

    // Remove old task if exists
    _tasks.removeWhere((t) => t.id == id);
    _tasks.add(task);
    await _saveTasks();

    _processQueue(totalDurationSeconds);
  }

  Future<void> _processQueue(double totalDurationSeconds) async {
    if (_isDownloading) return;

    final pendingTask = _tasks.firstWhere(
      (t) => t.status == DownloadStatus.pending,
      orElse: () => DownloadTask(id: '', movieSlug: '', movieName: '', episodeSlug: '', episodeName: '', thumbUrl: '', m3u8Url: '', savePath: ''),
    );

    if (pendingTask.id.isEmpty) return; // No pending tasks

    _isDownloading = true;
    pendingTask.status = DownloadStatus.downloading;
    pendingTask.progress = 0.0;
    notifyListeners();

    try {
      // Ensure no existing file
      final file = File(pendingTask.savePath);
      if (await file.exists()) {
        await file.delete();
      }

      // FFmpeg command to copy m3u8 stream to mp4
      final command = '-i "${pendingTask.m3u8Url}" -c copy -bsf:a aac_adtstoasc "${pendingTask.savePath}"';

      await FFmpegKit.executeAsync(
        command,
        (Session session) async {
          final returnCode = await session.getReturnCode();
          if (ReturnCode.isSuccess(returnCode)) {
            pendingTask.status = DownloadStatus.completed;
            pendingTask.progress = 1.0;
          } else if (ReturnCode.isCancel(returnCode)) {
            pendingTask.status = DownloadStatus.canceled;
            if (await file.exists()) await file.delete();
          } else {
            pendingTask.status = DownloadStatus.failed;
            if (await file.exists()) await file.delete();
            final logs = await session.getLogsAsString();
            debugPrint("FFmpeg Error: $logs");
          }
          
          _isDownloading = false;
          await _saveTasks();
          _processQueue(totalDurationSeconds); // Process next
        },
        (Log log) {
          // print(log.getMessage());
        },
        (Statistics statistics) {
          // Calculate progress based on time
          if (totalDurationSeconds > 0) {
            final timeInMilliseconds = statistics.getTime();
            if (timeInMilliseconds > 0) {
              final progress = (timeInMilliseconds / 1000.0) / totalDurationSeconds;
              if (progress > pendingTask.progress) {
                pendingTask.progress = progress > 1.0 ? 1.0 : progress;
                notifyListeners();
              }
            }
          }
        },
      );
    } catch (e) {
      pendingTask.status = DownloadStatus.failed;
      _isDownloading = false;
      await _saveTasks();
      _processQueue(totalDurationSeconds);
    }
  }

  Future<void> cancelDownload(String id) async {
    final task = getTask(id);
    if (task != null && task.status == DownloadStatus.downloading) {
      FFmpegKit.cancel(); // Cancels all running sessions
    } else if (task != null) {
      deleteDownload(id);
    }
  }

  Future<void> deleteDownload(String id) async {
    final task = getTask(id);
    if (task != null) {
      final file = File(task.savePath);
      if (await file.exists()) {
        await file.delete();
      }
      _tasks.remove(task);
      await _saveTasks();
    }
  }
}
