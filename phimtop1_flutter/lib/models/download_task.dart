import 'dart:convert';

enum DownloadStatus { pending, downloading, completed, failed, canceled }

class DownloadTask {
  final String id; // e.g., movieSlug_episodeSlug
  final String movieSlug;
  final String movieName;
  final String episodeSlug;
  final String episodeName;
  final String thumbUrl;
  final String m3u8Url;
  String savePath;
  
  DownloadStatus status;
  double progress;
  String speed;
  String timeRemaining;

  DownloadTask({
    required this.id,
    required this.movieSlug,
    required this.movieName,
    required this.episodeSlug,
    required this.episodeName,
    required this.thumbUrl,
    required this.m3u8Url,
    required this.savePath,
    this.status = DownloadStatus.pending,
    this.progress = 0.0,
    this.speed = '',
    this.timeRemaining = '',
  });

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'movieSlug': movieSlug,
      'movieName': movieName,
      'episodeSlug': episodeSlug,
      'episodeName': episodeName,
      'thumbUrl': thumbUrl,
      'm3u8Url': m3u8Url,
      'savePath': savePath,
      'status': status.index,
      'progress': progress,
      'speed': speed,
      'timeRemaining': timeRemaining,
    };
  }

  factory DownloadTask.fromMap(Map<String, dynamic> map) {
    return DownloadTask(
      id: map['id'],
      movieSlug: map['movieSlug'],
      movieName: map['movieName'],
      episodeSlug: map['episodeSlug'],
      episodeName: map['episodeName'],
      thumbUrl: map['thumbUrl'],
      m3u8Url: map['m3u8Url'],
      savePath: map['savePath'],
      status: DownloadStatus.values[map['status']],
      progress: map['progress'],
      speed: map['speed'] ?? '',
      timeRemaining: map['timeRemaining'] ?? '',
    );
  }

  String toJson() => json.encode(toMap());

  factory DownloadTask.fromJson(String source) => DownloadTask.fromMap(json.decode(source));
}
