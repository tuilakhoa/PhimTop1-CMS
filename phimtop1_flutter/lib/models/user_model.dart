class User {
  final String id;
  final String name;
  final String email;
  final String? avatar;
  final String? activeFrame;
  int coins;

  User.fromJson(Map<String, dynamic> json)
      : id = json['id']?.toString() ?? '',
        name = json['name'] ?? '',
        email = json['email'] ?? '',
        avatar = json['avatar'],
        activeFrame = json['active_frame'],
        coins = int.tryParse(json['coins']?.toString() ?? '0') ?? 0;

  User copyWith({
    String? id,
    String? name,
    String? email,
    String? avatar,
    String? activeFrame,
    int? coins,
  }) {
    return User.fromJson({
      'id': id ?? this.id,
      'name': name ?? this.name,
      'email': email ?? this.email,
      'avatar': avatar ?? this.avatar,
      'active_frame': activeFrame ?? this.activeFrame,
      'coins': coins ?? this.coins,
    });
  }
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
  final String? activeFrameUrl;

  CommentItem.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        userName = json['user_name'] ?? '',
        content = json['content'] ?? '',
        timeAgo = json['time_ago'] ?? '',
        activeFrameUrl = json['active_frame_url'];
}

class CommentResponse {
  final bool success;
  final String? message;
  final List<CommentItem>? data;

  CommentResponse.fromJson(Map<String, dynamic> json)
      : success = json['success'] ?? false,
        message = json['message'],
        data = (json['data'] as List?)?.map((e) => CommentItem.fromJson(e as Map<String, dynamic>)).toList();
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

class ReviewItem {
  final String userName;
  final int ratingScore;
  final String content;
  final String createdAt;

  ReviewItem.fromJson(Map<String, dynamic> json)
      : userName = json['user_name'] ?? '',
        ratingScore = int.tryParse(json['rating_score']?.toString() ?? '0') ?? 0,
        content = json['content'] ?? '',
        createdAt = json['created_at'] ?? '';
}

class ReviewResponse {
  final String status;
  final List<ReviewItem>? data;
  final double average;
  final int total;

  ReviewResponse.fromJson(Map<String, dynamic> json)
      : status = json['status'] ?? '',
        data = (json['data'] as List?)?.map((e) => ReviewItem.fromJson(e)).toList(),
        average = double.tryParse(json['average']?.toString() ?? '0') ?? 0,
        total = int.tryParse(json['total']?.toString() ?? '0') ?? 0;
}

class UserProfile {
  final int id;
  final String userEmail;
  final String profileName;
  final String avatarUrl;
  final bool isKidsMode;
  final bool hasPin;

  UserProfile.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        userEmail = json['user_email'] ?? '',
        profileName = json['profile_name'] ?? '',
        avatarUrl = json['avatar_url'] ?? '',
        isKidsMode = (json['is_kids_mode']?.toString() == '1'),
        hasPin = (json['has_pin']?.toString() == '1');
}

class AvatarFrame {
  final int id;
  final String name;
  final String imageUrl;
  final int price;
  final bool isOwned;
  final bool isActive;

  AvatarFrame.fromJson(Map<String, dynamic> json)
      : id = int.tryParse(json['id']?.toString() ?? '') ?? 0,
        name = json['name'] ?? '',
        imageUrl = json['image_url'] ?? '',
        price = int.tryParse(json['price']?.toString() ?? '0') ?? 0,
        isOwned = json['is_owned'] == true || json['is_owned'] == 1,
        isActive = json['is_active'] == true || json['is_active'] == 1;
}
