import 'api_responses.dart';

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
  final List<PersonItem>? peoples;

  MovieDetailData.fromJson(Map<String, dynamic> json)
      : domain = json['domain'] ?? '',
        movie = json['movie'] != null ? MovieDetail.fromJson(json['movie']) : null,
        episodes = (json['episodes'] as List?)?.map((e) => Episode.fromJson(e)).toList(),
        images = json['images'] != null ? MovieImages.fromJson(json['images']) : null,
        peoples = (json['peoples'] as List?)?.map((e) => PersonItem.fromJson(e)).toList();
}

class PersonItem {
  final String name;
  final String character;
  final String profilePath;

  PersonItem.fromJson(Map<String, dynamic> json)
      : name = json['name'] ?? '',
        character = json['character'] ?? '',
        profilePath = json['profile_path'] ?? '';
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
