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
  
  int currentEpisodeIndex = 0;
  int currentServerIndex = 0;

  Future<void> fetchDetail(String slug) async {
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
        currentEpisodeIndex = 0;
        currentServerIndex = 0;
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
        await fetchComments(slug);
        return true;
      }
    } catch (e) {
      // ignore
    }
    return false;
  }
}
