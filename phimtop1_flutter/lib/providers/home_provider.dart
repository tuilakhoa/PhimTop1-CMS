import 'package:flutter/material.dart';
import '../api/cms_api.dart';
import '../models/models.dart';

class HomeProvider with ChangeNotifier {
  bool isLoading = true;
  String? error;
  
  List<MovieItem> featuredMovies = [];
  List<MovieItem> normalMovies = [];
  List<MovieItem> trendingMovies = [];
  List<MovieItem> phimBo = [];
  List<MovieItem> phimLe = [];
  List<MovieItem> hoatHinh = [];
  List<MovieItem> tvShows = [];
  String domain = "";
  String logoUrl = "";

  Future<void> fetchHomeData() async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final initRes = await cmsApi.getAppInit();
      if (initRes.data != null) {
        logoUrl = initRes.data!.logoUrl;
      }

      final homeResponse = await cmsApi.getHome();
      if (homeResponse.data != null) {
        featuredMovies = homeResponse.data!.featuredMovies ?? [];
        normalMovies = homeResponse.data!.items;
        domain = homeResponse.data!.domain;
      }
      
      final trendingRes = await cmsApi.fetchTrending();
      if (trendingRes.data != null) trendingMovies = trendingRes.data!.items.take(12).toList();
      
      final phimBoRes = await cmsApi.getCategory("danh-sach", "phim-bo");
      if (phimBoRes.data != null) phimBo = phimBoRes.data!.items.take(12).toList();
      
      final phimLeRes = await cmsApi.getCategory("danh-sach", "phim-le");
      if (phimLeRes.data != null) phimLe = phimLeRes.data!.items.take(12).toList();
      
      final hoatHinhRes = await cmsApi.getCategory("danh-sach", "hoat-hinh");
      if (hoatHinhRes.data != null) hoatHinh = hoatHinhRes.data!.items.take(12).toList();
      
      final tvShowsRes = await cmsApi.getCategory("danh-sach", "tv-shows");
      if (tvShowsRes.data != null) tvShows = tvShowsRes.data!.items.take(12).toList();

    } catch (e) {
      error = e.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
