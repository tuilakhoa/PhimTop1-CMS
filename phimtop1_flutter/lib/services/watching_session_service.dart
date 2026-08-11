import 'dart:async';
import 'dart:math';
import 'dart:io' show Platform;
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:phimtop1_flutter/core/config.dart';

class WatchingSessionService {
  static final WatchingSessionService _instance = WatchingSessionService._internal();
  factory WatchingSessionService() => _instance;
  WatchingSessionService._internal();

  Timer? _timer;
  final Dio _dio = Dio();
  
  // Stream to push commands to the UI
  final _commandController = StreamController<String>.broadcast();
  Stream<String> get onCommand => _commandController.stream;

  String _deviceId = '';

  Future<void> _initDeviceId() async {
    if (_deviceId.isNotEmpty) return;
    final prefs = await SharedPreferences.getInstance();
    _deviceId = prefs.getString('phimtop1_device_id') ?? '';
    if (_deviceId.isEmpty) {
      // Generate a simple unique ID
      final random = Random();
      _deviceId = 'app-${DateTime.now().millisecondsSinceEpoch}-${random.nextInt(100000)}';
      await prefs.setString('phimtop1_device_id', _deviceId);
    }
  }

  void startSession({
    required String movieSlug,
    required String movieName,
    required String episodeName,
    required String userName,
    required bool isLoggedIn,
    int Function()? getProgress,
  }) async {
    await _initDeviceId();
    
    // Stop any existing timer
    stopSession();

    // Determine platform
    String platformName = 'unknown';
    try {
      if (Platform.isAndroid) platformName = 'android';
      if (Platform.isIOS) platformName = 'ios';
      if (Platform.isWindows) platformName = 'windows';
      if (Platform.isMacOS) platformName = 'macos';
      if (Platform.isLinux) platformName = 'linux';
    } catch (_) {}

    final payload = {
      'device_id': _deviceId,
      'device_name': 'PhimTop1 App ($platformName)',
      'platform': platformName,
      'movie_slug': movieSlug,
      'movie_name': movieName,
      'episode_name': episodeName,
      'user_name': userName,
      'is_logged_in': isLoggedIn ? 1 : 0,
    };

    // Run immediately first time
    payload['progress'] = getProgress?.call() ?? 0;
    _sendHeartbeat(payload);

    // Then every 10 seconds
    _timer = Timer.periodic(const Duration(seconds: 10), (timer) {
      payload['progress'] = getProgress?.call() ?? 0;
      _sendHeartbeat(payload);
    });
  }

  Future<void> _sendHeartbeat(Map<String, dynamic> payload) async {
    try {
      String url = '${AppConfig.baseUrl}api/v1/watching_session.php?action=heartbeat&key=${AppConfig.apiKey}';
      
      final response = await _dio.post(
        url,
        data: payload,
        options: Options(
          headers: {'X-App-API-Key': AppConfig.apiKey},
        ),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data is Map && data['status'] == 'success' && data['command'] != null) {
          final command = data['command'] as String;
          if (command.isNotEmpty) {
            _commandController.add(command);
          }
        }
      }
    } catch (e) {
      // Ignore errors silently for heartbeat
    }
  }

  void stopSession() {
    _timer?.cancel();
    _timer = null;
  }
}
