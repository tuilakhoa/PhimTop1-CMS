import 'package:flutter/material.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import 'auth_provider.dart';

class PlaylistProvider with ChangeNotifier {
  final AuthProvider authProvider;
  List<Playlist> playlists = [];
  bool isLoading = false;
  String? error;

  PlaylistProvider({required this.authProvider});

  Future<void> fetchPlaylists() async {
    if (authProvider.token == null) return;
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final response = await cmsApi.getPlaylists(authProvider.token!);
      if (response.status == 'success') {
        playlists = response.data ?? [];
      } else {
        error = 'Lỗi khi tải danh sách phát';
      }
    } catch (e) {
      error = e.toString();
    }
    
    isLoading = false;
    notifyListeners();
  }

  Future<bool> createPlaylist(String name) async {
    if (authProvider.token == null) return false;
    
    final id = await cmsApi.createPlaylist(authProvider.token!, name);
    if (id != null) {
      await fetchPlaylists();
      return true;
    }
    return false;
  }

  Future<bool> deletePlaylist(int playlistId) async {
    if (authProvider.token == null) return false;
    
    final success = await cmsApi.deletePlaylist(authProvider.token!, playlistId);
    if (success) {
      playlists.removeWhere((p) => p.id == playlistId);
      notifyListeners();
      return true;
    }
    return false;
  }

  Future<bool> addToPlaylist(int playlistId, String movieSlug, String movieName, String thumbUrl) async {
    if (authProvider.token == null) return false;
    
    final success = await cmsApi.addToPlaylist(authProvider.token!, playlistId, movieSlug, movieName, thumbUrl);
    if (success) {
      await fetchPlaylists();
      return true;
    }
    return false;
  }

  Future<bool> removeFromPlaylist(int playlistId, String movieSlug) async {
    if (authProvider.token == null) return false;
    
    final success = await cmsApi.removeFromPlaylist(authProvider.token!, playlistId, movieSlug);
    if (success) {
      final playlist = playlists.firstWhere((p) => p.id == playlistId);
      playlist.items?.removeWhere((item) => item.movieSlug == movieSlug);
      notifyListeners();
      return true;
    }
    return false;
  }

  Future<List<int>> checkPlaylist(String slug) async {
    if (authProvider.token == null) return [];
    try {
      final res = await cmsApi.checkPlaylist(authProvider.token!, slug);
      if (res.status == 'success') {
        return res.inPlaylists ?? [];
      }
    } catch (e) {
      // ignore
    }
    return [];
  }
}
