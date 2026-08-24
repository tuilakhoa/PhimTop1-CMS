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
  final String appLatestVersion;
  final int appBuildNumber;
  final bool appForceUpdate;
  final String appLatestVersionIos;
  final int appBuildNumberIos;
  final bool appForceUpdateIos;
  final String appDownloadUrlIos;
  final String appUpdateMessage;

  AppInitData.fromJson(Map<String, dynamic> json)
      : siteName = json['siteName'] ?? '',
        logoUrl = json['logoUrl'] ?? '',
        maintenance = json['maintenance'] ?? false,
        appBannerEnabled = json['appBannerEnabled'] ?? 0,
        appDownloadUrl = json['appDownloadUrl'] ?? '',
        enableComics = json['enableComics'] ?? 0,
        isComicPluginActive = json['isComicPluginActive'],
        version = json['version'] ?? '',
        appLatestVersion = json['appLatestVersion'] ?? '1.0.0',
        appBuildNumber = json['appBuildNumber'] ?? 1,
        appForceUpdate = json['appForceUpdate'] ?? false,
        appLatestVersionIos = json['appLatestVersionIos'] ?? '1.0.0',
        appBuildNumberIos = json['appBuildNumberIos'] ?? 1,
        appForceUpdateIos = json['appForceUpdateIos'] ?? false,
        appDownloadUrlIos = json['appDownloadUrlIos'] ?? '',
        appUpdateMessage = json['appUpdateMessage'] ?? 'Đã có phiên bản mới, vui lòng cập nhật!';
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
