import 'dart:convert';
import 'package:http/http.dart' as http;
import '../core/constants.dart';

class WatchPartyService {
  static Future<Map<String, dynamic>> createParty(String movieSlug, String episodeName, String creatorName, {bool isPublic = false}) async {
    try {
      final response = await http.post(
        Uri.parse('${Constants.apiUrl.replaceAll('/v1/api', '')}/v1/watch_party.php?action=create'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'movie_slug': movieSlug,
          'episode_name': episodeName,
          'creator_name': creatorName,
          'is_public': isPublic ? 1 : 0,
        }),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 'error', 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> joinParty(String roomCode) async {
    try {
      final response = await http.get(
        Uri.parse('${Constants.apiUrl.replaceAll('/v1/api', '')}/v1/watch_party.php?action=join&room_code=$roomCode'),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 'error', 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> syncState(String roomCode, bool isPlaying, int currentTime) async {
    try {
      final response = await http.post(
        Uri.parse('${Constants.apiUrl.replaceAll('/v1/api', '')}/v1/watch_party.php?action=sync'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'room_code': roomCode,
          'is_playing': isPlaying ? 1 : 0,
          'current_time': currentTime,
        }),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 'error', 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> getState(String roomCode) async {
    try {
      final response = await http.get(
        Uri.parse('${Constants.apiUrl.replaceAll('/v1/api', '')}/v1/watch_party.php?action=state&room_code=$roomCode'),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 'error', 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> getPublicParties(String movieSlug) async {
    try {
      final response = await http.get(
        Uri.parse('${Constants.apiUrl.replaceAll('/v1/api', '')}/v1/watch_party.php?action=list_public&movie_slug=$movieSlug'),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {'status': 'error', 'message': e.toString()};
    }
  }
}
