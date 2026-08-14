class ApiResponse<T> {
  final String status;
  final String? message;
  final T? data;

  ApiResponse({required this.status, this.message, this.data});

  factory ApiResponse.fromJson(Map<String, dynamic> json, T Function(dynamic) fromJsonT) {
    return ApiResponse(
      status: json['status'] as String,
      message: json['message']?.toString(),
      data: json['data'] != null ? fromJsonT(json['data']) : null,
    );
  }
}

class AppInitData {
  final String siteName;
  final String logoUrl;
  final bool maintenance;
  final int appBannerEnabled;
  final String appDownloadUrl;
  final int enableComics;
  final bool? isComicPluginActive;
  final String version;

  AppInitData.fromJson(Map<String, dynamic> json)
      : siteName = json['siteName'] ?? '',
        logoUrl = json['logoUrl'] ?? '',
        maintenance = json['maintenance'] ?? false,
        appBannerEnabled = json['appBannerEnabled'] ?? 0,
        appDownloadUrl = json['appDownloadUrl'] ?? '',
        enableComics = json['enableComics'] ?? 0,
        isComicPluginActive = json['isComicPluginActive'],
        version = json['version'] ?? '';
}

class HomeData {
  final List<MovieItem> items;
  final String titlePage;
  final String domain;
  final List<MovieItem>? featuredMovies;
  final String? featuredStyle;

  HomeData.fromJson(Map<String, dynamic> json)
      : items = (json['items'] as List?)?.map((e) => MovieItem.fromJson(e)).toList() ?? [],
        titlePage = json['titlePage'] ?? '',
        domain = json['domain'] ?? '',
        featuredMovies = (json['featuredMovies'] as List?)?.map((e) => MovieItem.fromJson(e)).toList(),
        featuredStyle = json['featuredStyle'];
}

class CategoryData {
  final List<CategoryItem> items;
  final String titlePage;
  final String domain;

  CategoryData.fromJson(Map<String, dynamic> json)
      : items = (json['items'] as List?)?.map((e) => CategoryItem.fromJson(e)).toList() ?? [],
        titlePage = json['titlePage'] ?? '',
        domain = json['domain'] ?? '';
}

class CategoryItem {
  final String slug;
  final String name;
  final String type;

  CategoryItem.fromJson(Map<String, dynamic> json)
      : slug = json['slug'] ?? '',
        name = json['name'] ?? '',
        type = json['type'] ?? '';
}

class CategoryDto {
  final String? id;
  final String name;
  final String slug;

  CategoryDto.fromJson(Map<String, dynamic> json)
      : id = json['id']?.toString(),
        name = json['name'] ?? '',
        slug = json['slug'] ?? '';
}

class MovieItem {
  final String? id;
  final String name;
  final String slug;
  final String? originName;
  final String? thumbUrl;
  final String? posterUrl;
  final int? year;
  final int? view;
  final List<CategoryDto>? category;

  MovieItem.fromJson(Map<String, dynamic> json)
      : id = json['_id']?.toString(),
        name = json['name'] ?? '',
        slug = json['slug'] ?? '',
        originName = json['origin_name'],
        thumbUrl = json['thumb_url'],
        posterUrl = json['poster_url'],
        year = int.tryParse(json['year']?.toString() ?? ''),
        view = int.tryParse(json['view']?.toString() ?? '0'),
        category = (json['category'] as List?)?.map((e) => CategoryDto.fromJson(e)).toList();
}

class MovieDetailData {
  final String domain;
  final MovieDetail? movie;
  final List<Episode>? episodes;
  final MovieImages? images;

  MovieDetailData.fromJson(Map<String, dynamic> json)
      : domain = json['domain'] ?? '',
        movie = json['movie'] != null ? MovieDetail.fromJson(json['movie']) : null,
        episodes = (json['episodes'] as List?)?.map((e) => Episode.fromJson(e)).toList(),
        images = json['images'] != null ? MovieImages.fromJson(json['images']) : null;
}

class MovieDetail {
  final String? id;
  final String name;
  final String slug;
  final String? originName;
  final String? thumbUrl;
  final String? posterUrl;
  final int? year;
  final String? content;
  final List<String>? actor;
  final String? time;
  final String? episodeCurrent;

  MovieDetail.fromJson(Map<String, dynamic> json)
      : id = json['_id']?.toString(),
        name = json['name'] ?? '',
        slug = json['slug'] ?? '',
        originName = json['origin_name'],
        thumbUrl = json['thumb_url'],
        posterUrl = json['poster_url'],
        year = int.tryParse(json['year']?.toString() ?? ''),
        content = json['content'],
        actor = (json['actor'] as List?)?.map((e) => e.toString()).toList(),
        time = json['time'],
        episodeCurrent = json['episode_current'];
}

class MovieImageItem {
  final String filePath;

  MovieImageItem.fromJson(Map<String, dynamic> json)
      : filePath = json['file_path'] ?? '';
}

class MovieImages {
  final List<MovieImageItem> backdrops;
  final List<MovieImageItem> posters;

  MovieImages.fromJson(Map<String, dynamic> json)
      : backdrops = (json['backdrops'] as List?)?.map((e) => MovieImageItem.fromJson(e)).toList() ?? [],
        posters = (json['posters'] as List?)?.map((e) => MovieImageItem.fromJson(e)).toList() ?? [];
}

class Episode {
  final String serverName;
  final List<ServerData> serverData;

  Episode.fromJson(Map<String, dynamic> json)
      : serverName = json['server_name'] ?? '',
        serverData = (json['server_data'] as List?)?.map((e) => ServerData.fromJson(e)).toList() ?? [];
}

class ServerData {
  final String name;
  final String slug;
  final String filename;
  final String linkEmbed;
  final String linkM3u8;

  ServerData.fromJson(Map<String, dynamic> json)
      : name = json['name'] ?? '',
        slug = json['slug'] ?? '',
        filename = json['filename'] ?? '',
        linkEmbed = json['link_embed'] ?? '',
        linkM3u8 = json['link_m3u8'] ?? '';
}

// User Models
class User {
  final String id;
  final String name;
  final String email;
  final String? avatar;

  User.fromJson(Map<String, dynamic> json)
      : id = json['id']?.toString() ?? '',
        name = json['name'] ?? '',
        email = json['email'] ?? '',
        avatar = json['avatar'];
}

class AuthResponse {
  final String status;
  final String? message;
  final String? token;
  final User? user;

  AuthResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        message = json['message'],
        token = json['token'],
        user = json['user'] != null ? User.fromJson(json['user']) : null;
}

class CommentItem {
  final int id;
  final String userName;
  final String content;
  final String timeAgo;

  CommentItem.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        userName = json['user_name'] ?? '',
        content = json['content'] ?? '',
        timeAgo = json['time_ago'] ?? '';
}

class CommentResponse {
  final bool success;
  final String? message;
  final List<CommentItem>? data;

  CommentResponse.fromJson(Map<String, dynamic> json)
      : success = json['success'] ?? false,
        message = json['message'],
        data = (json['data'] as List?)?.map((e) => CommentItem.fromJson(e)).toList();
}

class CheckFollowResponse {
  final String status;
  final bool isFollowing;

  CheckFollowResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        isFollowing = json['is_following'] ?? false;
}

class ToggleFollowResponse {
  final String status;
  final String action;

  ToggleFollowResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        action = json['action'] ?? '';
}

class HistoryItem {
  final int id;
  final String movieSlug;
  final String movieName;
  final String episodeName;
  final String episodeSlug;
  final int currentTime;
  final int duration;
  final String updatedAt;
  final String thumbUrl;

  HistoryItem.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        movieSlug = json['movie_slug'] ?? '',
        movieName = json['movie_name'] ?? '',
        episodeName = json['episode_name'] ?? '',
        episodeSlug = json['episode_slug'] ?? '',
        currentTime = int.tryParse(json['current_time']?.toString() ?? '0') ?? 0,
        duration = int.tryParse(json['duration']?.toString() ?? '0') ?? 0,
        updatedAt = json['updated_at'] ?? '',
        thumbUrl = json['thumb_url'] ?? '';
}

class HistoryResponse {
  final String status;
  final List<HistoryItem>? data;

  HistoryResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        data = (json['data'] as List?)?.map((e) => HistoryItem.fromJson(e)).toList();
}

class FollowItem {
  final int id;
  final String itemSlug;
  final String itemName;
  final String itemType;
  final String? thumbUrl;
  final String createdAt;

  FollowItem.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        itemSlug = json['item_slug'] ?? '',
        itemName = json['item_name'] ?? '',
        itemType = json['item_type'] ?? '',
        thumbUrl = json['thumb_url'],
        createdAt = json['created_at'] ?? '';
}

class FollowListResponse {
  final String status;
  final List<FollowItem>? data;

  FollowListResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        data = (json['data'] as List?)?.map((e) => FollowItem.fromJson(e)).toList();
}

class NotificationItem {
  final int id;
  final String title;
  final String message;
  final String? url;
  final bool isRead;
  final String createdAt;

  NotificationItem.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        title = json['title'] ?? '',
        message = json['message'] ?? '',
        url = json['url'],
        isRead = (json['is_read']?.toString() == '1' || json['is_read'] == true),
        createdAt = json['created_at'] ?? '';
}

class NotificationListResponse {
  final String status;
  final List<NotificationItem>? data;

  NotificationListResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        data = (json['data'] as List?)?.map((e) => NotificationItem.fromJson(e)).toList();
}

// Playlist Models
class PlaylistItem {
  final int id;
  final int playlistId;
  final String movieSlug;
  final String movieName;
  final String? thumbUrl;
  final String createdAt;

  PlaylistItem.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        playlistId = int.tryParse(json['playlist_id']?.toString() ?? '') ?? 0,
        movieSlug = json['movie_slug'] ?? '',
        movieName = json['movie_name'] ?? '',
        thumbUrl = json['thumb_url'],
        createdAt = json['created_at'] ?? '';
}

class Playlist {
  final int id;
  final String name;
  final String createdAt;
  final List<PlaylistItem>? items;

  Playlist.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        name = json['name'] ?? '',
        createdAt = json['created_at'] ?? '',
        items = (json['items'] as List?)?.map((e) => PlaylistItem.fromJson(e)).toList();
}

class PlaylistResponse {
  final String status;
  final List<Playlist>? data;

  PlaylistResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        data = (json['data'] as List?)?.map((e) => Playlist.fromJson(e)).toList();
}

class PlaylistCheckResponse {
  final String status;
  final List<int>? inPlaylists;

  PlaylistCheckResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        inPlaylists = (json['in_playlists'] as List?)?.map((e) => int.parse(e.toString())).toList();
}
