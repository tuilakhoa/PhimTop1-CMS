import 'package:flutter/material.dart';
import '../api/cms_api.dart';
import '../models/models.dart';

class DetailProvider with ChangeNotifier {
  bool isLoading = true;
  String? error;
  
  MovieDetail? movie;
  List<Episode> episodes = [];
  String domain = "";
  MovieImages? images;
  List<PersonItem>? peoples;
  
  int currentEpisodeIndex = 0;
  int currentServerIndex = 0;

  Future<void> fetchDetail(String slug, {String? token}) async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final response = await cmsApi.getMovieDetail(slug);
      final data = response.data;
      if (data != null) {
        movie = data.movie;
        episodes = data.episodes ?? [];
        domain = data.domain;
        images = data.images;
        peoples = data.peoples;
        currentEpisodeIndex = 0;
        currentServerIndex = 0;

        if (token != null) {
          try {
            final historyRes = await cmsApi.getHistory(token);
            if (historyRes.data != null) {
              final match = historyRes.data!.firstWhere(
                (item) => item.movieSlug == slug,
                orElse: () => HistoryItem.fromJson({}),
              );
              if (match.id != 0 && match.episodeSlug.isNotEmpty) {
                for (int s = 0; s < episodes.length; s++) {
                  final idx = episodes[s].serverData.indexWhere((ep) => ep.slug == match.episodeSlug);
                  if (idx != -1) {
                    currentServerIndex = s;
                    currentEpisodeIndex = idx;
                    break;
                  }
                }
              }
            }
          } catch (_) {}
        }
      } else {
        error = "Không tìm thấy phim";
      }
    } catch (e) {
      error = e.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  bool isFollowing = false;
  List<CommentItem> comments = [];
  List<ReviewItem> reviews = [];
  double averageRating = 0;
  int totalReviews = 0;

  Future<void> fetchComments(String slug) async {
    try {
      final response = await cmsApi.getComments(slug);
      if (response.success && response.data != null) {
        comments = response.data!;
        notifyListeners();
      }
    } catch (e) {
      // ignore
    }
  }

  Future<void> fetchReviews(String slug) async {
    try {
      final response = await cmsApi.getReviews(slug);
      if (response.status == 'success') {
        reviews = response.data ?? [];
        averageRating = response.average;
        totalReviews = response.total;
        notifyListeners();
      }
    } catch (e) {
      // ignore
    }
  }

  void changeEpisode(int episodeIndex, int serverIndex) {
    currentEpisodeIndex = episodeIndex;
    currentServerIndex = serverIndex;
    notifyListeners();
  }

  Future<void> checkFollow(String token, String slug) async {
    try {
      final response = await cmsApi.checkFollow(token, slug);
      if (response.status == 'success') {
        isFollowing = response.isFollowing;
        notifyListeners();
      }
    } catch (e) {
      // ignore
    }
  }

  Future<void> toggleFollow(String token) async {
    if (movie == null) return;
    final thumb = movie!.thumbUrl ?? movie!.posterUrl ?? '';
    try {
      final response = await cmsApi.toggleFollow(token, {
        'item_slug': movie!.slug,
        'item_type': 'movie',
        'item_name': movie!.name,
        'thumb_url': thumb,
      });
      if (response.status == 'success') {
        isFollowing = response.action == 'added';
        notifyListeners();
      }
    } catch (e) {
      // ignore
    }
  }

  Future<bool> postComment(String slug, String content, {String? token, String? name}) async {
    try {
      final response = await cmsApi.postComment(slug, content, token: token, name: name);
      if (response.success) {
        // Optimistic update
        comments.insert(0, CommentItem.fromJson({
          'id': 0,
          'user_name': name != null && name.isNotEmpty ? name : 'Ẩn danh',
          'content': content,
          'time_ago': 'Vừa xong',
        }));
        notifyListeners();
        
        await fetchComments(slug);
        return true;
      }
    } catch (e) {
      // ignore
    }
    return false;
  }

  Future<bool> postReview(String token, String slug, int rating, String content) async {
    try {
      final success = await cmsApi.postReview(token, slug, rating, content);
      if (success) {
        await fetchReviews(slug);
        return true;
      }
    } catch (e) {
      // ignore
    }
    return false;
  }
}
