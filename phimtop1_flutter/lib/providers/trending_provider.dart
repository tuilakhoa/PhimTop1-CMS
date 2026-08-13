import 'package:flutter/material.dart';
import '../models/models.dart';
import '../api/cms_api.dart';

class TrendingProvider with ChangeNotifier {
  List<MovieItem> _movies = [];
  bool _isLoading = false;
  String? _error;
  String _domain = '';
  int _currentPage = 1;
  bool _hasMore = true;

  List<MovieItem> get movies => _movies;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String get domain => _domain;
  bool get hasMore => _hasMore;

  TrendingProvider() {
    fetchTrending(refresh: true);
  }

  Future<void> fetchTrending({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _movies.clear();
      _hasMore = true;
      _error = null;
    }

    if (!_hasMore || _isLoading) return;

    _isLoading = true;
    notifyListeners();

    try {
      final response = await cmsApi.fetchTrending(page: _currentPage);
      if (response.status == 'success' && response.data != null) {
        final newItems = response.data!.items;
        if (refresh) {
          _movies = newItems;
        } else {
          _movies.addAll(newItems);
        }
        
        _domain = response.data!.domain;
        
        if (newItems.isEmpty || newItems.length < 24) {
          _hasMore = false;
        } else {
          _currentPage++;
        }
      } else {
        _error = response.message ?? 'Unknown error';
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
