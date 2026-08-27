import 'package:flutter/material.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import 'package:shared_preferences/shared_preferences.dart';

class HomeProvider with ChangeNotifier {
  bool isLoading = true;
  String? error;
  
  List<MovieItem> featuredMovies = [];
  List<MovieItem> normalMovies = [];
  List<MovieItem> trendingMovies = [];
  List<MovieItem> recommendedMovies = [];
  List<MovieItem> phimBo = [];
  List<MovieItem> phimLe = [];
  List<MovieItem> hoatHinh = [];
  List<MovieItem> tvShows = [];
  String domain = "";
  String logoUrl = "";
  
  String appLatestVersion = "1.0.0";
  int appBuildNumber = 1;
  bool appForceUpdate = false;
  
  String appLatestVersionIos = "1.0.0";
  int appBuildNumberIos = 1;
  bool appForceUpdateIos = false;
  String appDownloadUrlIos = "";

  String appUpdateMessage = "";
  String appDownloadUrl = "";
  String appInAppUpdateUrl = "";

  Future<void> fetchHomeData() async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final initRes = await cmsApi.getAppInit();
      if (initRes.data != null) {
        logoUrl = initRes.data!.logoUrl;
        appLatestVersion = initRes.data!.appLatestVersion;
        appBuildNumber = initRes.data!.appBuildNumber;
        appForceUpdate = initRes.data!.appForceUpdate;
        
        appLatestVersionIos = initRes.data!.appLatestVersionIos;
        appBuildNumberIos = initRes.data!.appBuildNumberIos;
        appForceUpdateIos = initRes.data!.appForceUpdateIos;
        appDownloadUrlIos = initRes.data!.appDownloadUrlIos;

        appUpdateMessage = initRes.data!.appUpdateMessage;
        appDownloadUrl = initRes.data!.appDownloadUrl;
        appInAppUpdateUrl = initRes.data!.appInAppUpdateUrl;
      }

      final homeResponse = await cmsApi.getHome();
      if (homeResponse.data != null) {
        featuredMovies = homeResponse.data!.featuredMovies ?? [];
        normalMovies = homeResponse.data!.items;
        domain = homeResponse.data!.domain;
      }
      
      final trendingRes = await cmsApi.fetchTrending();
      if (trendingRes.data != null) trendingMovies = trendingRes.data!.items.take(12).toList();
      
      try {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString('auth_token');
        final recommendRes = await cmsApi.getRecommendations(token: token);
        if (recommendRes.data != null) recommendedMovies = recommendRes.data!.items.take(12).toList();
      } catch (e) {
        // Ignore recommend errors
      }
      
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
