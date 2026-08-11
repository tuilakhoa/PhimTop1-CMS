import 'package:flutter/material.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import 'dart:async';

class ExploreProvider with ChangeNotifier {
  bool isLoading = false;
  String? error;
  
  List<CategoryItem> allCategories = [];
  
  String keyword = "";
  String activeType = "phim-moi-cap-nhat";
  String activeGenre = "";
  String activeCountry = "";
  String activeYear = "";
  
  List<MovieItem> movies = [];
  String domain = "";
  
  List<MovieItem> trendingMovies = [];
  bool isTrendingLoading = true;

  Timer? _debounce;

  ExploreProvider() {
    _initData();
  }

  Future<void> _initData() async {
    isTrendingLoading = true;
    notifyListeners();

    try {
      final homeResponse = await cmsApi.getHome();
      if (homeResponse.data != null) {
        trendingMovies = homeResponse.data!.items.take(12).toList();
        domain = homeResponse.data!.domain;
      }
      
      final catResponse = await cmsApi.getCategories();
      if (catResponse.data != null) {
        allCategories = catResponse.data!.items;
      }
    } catch (e) {
      // ignore
    } finally {
      isTrendingLoading = false;
      notifyListeners();
    }
  }

  void setFilters({
    String? type,
    String? genre,
    String? country,
    String? year,
    String? searchKeyword,
  }) {
    if (type != null) activeType = type;
    if (genre != null) activeGenre = genre;
    if (country != null) activeCountry = country;
    if (year != null) activeYear = year;
    if (searchKeyword != null) keyword = searchKeyword;

    if (_debounce?.isActive ?? false) _debounce!.cancel();
    _debounce = Timer(const Duration(milliseconds: 500), () {
      _fetchMovies();
    });
    
    notifyListeners();
  }

  Future<void> _fetchMovies() async {
    if (keyword.trim().isEmpty && activeType == "phim-moi-cap-nhat" && activeGenre.isEmpty && activeCountry.isEmpty && activeYear.isEmpty) {
      movies = [];
      notifyListeners();
      return;
    }

    isLoading = true;
    error = null;
    notifyListeners();

    try {
      if (keyword.trim().isNotEmpty) {
        final response = await cmsApi.searchMovies(keyword.trim());
        movies = response.data?.items ?? [];
        if (response.data != null) domain = response.data!.domain;
      } else {
        final response = await cmsApi.getCategory(
          "danh-sach", 
          activeType,
          category: activeGenre.isEmpty ? null : activeGenre,
          country: activeCountry.isEmpty ? null : activeCountry,
          year: activeYear.isEmpty ? null : activeYear,
        );
        movies = response.data?.items ?? [];
        if (response.data != null) domain = response.data!.domain;
      }
    } catch (e) {
      error = e.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
