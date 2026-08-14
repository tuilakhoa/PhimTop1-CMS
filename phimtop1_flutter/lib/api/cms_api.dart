import 'package:dio/dio.dart';
import '../core/config.dart';
import '../models/models.dart';

class CmsApiService {
  final Dio _dio;

  CmsApiService() : _dio = Dio(BaseOptions(baseUrl: AppConfig.baseUrl));

  Future<ApiResponse<AppInitData>> getAppInit() async {
    try {
      final response = await _dio.get('api/v1/app_init.php', queryParameters: {
        'key': AppConfig.apiKey,
      });
      return ApiResponse.fromJson(response.data, (data) => AppInitData.fromJson(data));
    } catch (e) {
      rethrow;
    }
  }

  Future<ApiResponse<HomeData>> getHome({int page = 1}) async {
    try {
      final response = await _dio.get('api/v1/home.php', queryParameters: {
        'key': AppConfig.apiKey,
        'page': page,
      });
      return ApiResponse.fromJson(response.data, (data) => HomeData.fromJson(data));
    } catch (e) {
      rethrow;
    }
  }

  Future<ApiResponse<MovieDetailData>> getMovieDetail(String slug) async {
    try {
      final response = await _dio.get('api/v1/movie.php', queryParameters: {
        'key': AppConfig.apiKey,
        'slug': slug,
      });
      return ApiResponse.fromJson(response.data, (data) => MovieDetailData.fromJson(data));
    } catch (e) {
      rethrow;
    }
  }

  Future<ApiResponse<CategoryData>> getCategories() async {
    try {
      final response = await _dio.get('api/v1/categories.php', queryParameters: {
        'key': AppConfig.apiKey,
      });
      return ApiResponse.fromJson(response.data, (data) => CategoryData.fromJson(data));
    } catch (e) {
      rethrow;
    }
  }

  Future<ApiResponse<HomeData>> getCategory(
    String type, 
    String slug, {
    int page = 1,
    String? category,
    String? country,
    String? year,
  }) async {
    try {
      final response = await _dio.get('api/v1/category.php', queryParameters: {
        'key': AppConfig.apiKey,
        'type': type,
        'slug': slug,
        'page': page,
        if (category != null && category.isNotEmpty) 'category': category,
        if (country != null && country.isNotEmpty) 'country': country,
        if (year != null && year.isNotEmpty) 'year': year,
      });
      return ApiResponse.fromJson(response.data, (data) => HomeData.fromJson(data));
    } catch (e) {
      rethrow;
    }
  }

  Future<ApiResponse<HomeData>> searchMovies(String keyword, {int page = 1}) async {
    try {
      final response = await _dio.get('api/v1/search.php', queryParameters: {
        'key': AppConfig.apiKey,
        'keyword': keyword,
        'page': page,
      });
      return ApiResponse.fromJson(response.data, (data) => HomeData.fromJson(data));
    } catch (e) {
      rethrow;
    }
  }

  Future<CommentResponse> getComments(String slug) async {
    try {
      final response = await _dio.get('api/v1/comments.php', queryParameters: {
        'key': AppConfig.apiKey,
        'slug': slug,
      });
      return CommentResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<CommentResponse> postComment(String slug, String content, {String? token, String? name, bool anonymous = false}) async {
    try {
      final options = token != null ? Options(headers: {'Authorization': token}) : null;
      final response = await _dio.post('api/v1/comments.php', queryParameters: {
        'key': AppConfig.apiKey,
      }, data: {
        'slug': slug,
        'content': content,
        'name': name,
        'anonymous': anonymous,
      }, options: options);
      return CommentResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<CheckFollowResponse> checkFollow(String token, String slug) async {
    try {
      final response = await _dio.get('api/v1/follow.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'check',
        'slug': slug,
      }, options: Options(headers: {'Authorization': token}));
      return CheckFollowResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<ToggleFollowResponse> toggleFollow(String token, Map<String, dynamic> data) async {
    try {
      final response = await _dio.post('api/v1/follow.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'toggle',
      }, data: data, options: Options(headers: {'Authorization': token}));
      return ToggleFollowResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }
  Future<AuthResponse> login(String email, String password) async {
    try {
      final response = await _dio.post('api/v1/auth.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'login',
      }, data: {
        'email': email,
        'password': password,
      });
      return AuthResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<AuthResponse> register(String name, String email, String password) async {
    try {
      final response = await _dio.post('api/v1/auth.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'register',
      }, data: {
        'name': name,
        'email': email,
        'password': password,
      });
      return AuthResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<FollowListResponse> getFollows(String token, {String type = 'movie'}) async {
    try {
      final response = await _dio.get('api/v1/follow.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'list',
        'type': type,
      }, options: Options(headers: {'Authorization': token}));
      return FollowListResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<NotificationListResponse> getNotifications(String token) async {
    try {
      final response = await _dio.get('api/v1/notifications.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'list',
      }, options: Options(headers: {'Authorization': token}));
      return NotificationListResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<bool> markNotificationRead(String token, int notificationId) async {
    try {
      final response = await _dio.post('api/v1/notifications.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'mark_read',
      }, data: {
        'notification_id': notificationId,
      }, options: Options(headers: {'Authorization': token}));
      return response.data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }

  Future<HistoryResponse> getHistory(String token) async {
    try {
      final response = await _dio.get('api/v1/history.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'list',
      }, options: Options(headers: {'Authorization': token}));
      return HistoryResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<bool> addHistory(String token, String movieSlug, String movieName, String episodeName, {String episodeSlug = '', String thumbUrl = '', int currentTime = 0, int duration = 0}) async {
    try {
      final response = await _dio.post('api/v1/history.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'add',
      }, data: {
        'movie_slug': movieSlug,
        'movie_name': movieName,
        'episode_name': episodeName,
        'episode_slug': episodeSlug,
        'thumb_url': thumbUrl,
        'current_time': currentTime,
        'duration': duration,
      }, options: Options(headers: {'Authorization': token}));
      return response.data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }

  Future<bool> clearHistory(String token) async {
    try {
      final response = await _dio.post('api/v1/history.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'clear',
      }, options: Options(headers: {'Authorization': token}));
      return response.data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }

  // Playlist APIs
  Future<PlaylistResponse> getPlaylists(String token) async {
    try {
      final response = await _dio.get('api/v1/playlists.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'list',
      }, options: Options(headers: {'Authorization': token}));
      if (response.data == null || response.data is! Map<String, dynamic>) {
        throw Exception('Dữ liệu từ máy chủ không hợp lệ (null)');
      }
      return PlaylistResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<PlaylistCheckResponse> checkPlaylist(String token, String slug) async {
    try {
      final response = await _dio.get('api/v1/playlists.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'check',
        'slug': slug,
      }, options: Options(headers: {'Authorization': token}));
      return PlaylistCheckResponse.fromJson(response.data);
    } catch (e) {
      rethrow;
    }
  }

  Future<int?> createPlaylist(String token, String name) async {
    try {
      final response = await _dio.post('api/v1/playlists.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'create',
      }, data: {
        'name': name,
      }, options: Options(headers: {'Authorization': token}));
      if (response.data['status'] == 'success') {
        return int.tryParse(response.data['playlist_id']?.toString() ?? '');
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Future<bool> deletePlaylist(String token, int playlistId) async {
    try {
      final response = await _dio.post('api/v1/playlists.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'delete',
      }, data: {
        'playlist_id': playlistId,
      }, options: Options(headers: {'Authorization': token}));
      return response.data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }

  Future<bool> addToPlaylist(String token, int playlistId, String movieSlug, String movieName, String thumbUrl) async {
    try {
      final response = await _dio.post('api/v1/playlists.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'add_item',
      }, data: {
        'playlist_id': playlistId,
        'movie_slug': movieSlug,
        'movie_name': movieName,
        'thumb_url': thumbUrl,
      }, options: Options(headers: {'Authorization': token}));
      return response.data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }

  Future<bool> removeFromPlaylist(String token, int playlistId, String movieSlug) async {
    try {
      final response = await _dio.post('api/v1/playlists.php', queryParameters: {
        'key': AppConfig.apiKey,
        'action': 'remove_item',
      }, data: {
        'playlist_id': playlistId,
        'movie_slug': movieSlug,
      }, options: Options(headers: {'Authorization': token}));
      return response.data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }

  Future<ApiResponse<HomeData>> fetchTrending({int page = 1}) async {
    try {
      final response = await _dio.get('api/v1/trending.php', queryParameters: {
        'key': AppConfig.apiKey,
        'page': page,
      });
      return ApiResponse.fromJson(response.data, (data) => HomeData.fromJson(data));
    } catch (e) {
      rethrow;
    }
  }

  Future<bool> submitFeedback(String token, String message) async {
    try {
      final response = await _dio.post('api/v1/feedback.php', queryParameters: {
        'key': AppConfig.apiKey,
      }, data: {
        'message': message,
      }, options: Options(headers: {'Authorization': token}));
      return response.data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }
}

// Global instance
final cmsApi = CmsApiService();
