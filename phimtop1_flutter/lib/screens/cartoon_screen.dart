import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:go_router/go_router.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../core/config.dart';
import '../widgets/focusable_wrapper.dart';
import '../widgets/tv_cast_button.dart';
import '../widgets/error_view.dart';
import '../widgets/youtube_tv_movie_card.dart';

class CartoonScreen extends StatefulWidget {
  const CartoonScreen({super.key});

  @override
  State<CartoonScreen> createState() => _CartoonScreenState();
}

class _CartoonScreenState extends State<CartoonScreen> {
  final ScrollController _scrollController = ScrollController();
  bool isLoading = true;
  bool isLoadingMore = false;
  String? error;
  List<MovieItem> movies = [];
  String domain = "";
  int page = 1;

  @override
  void initState() {
    super.initState();
    _fetchData();
    _scrollController.addListener(() {
      if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
        _fetchMore();
      }
    });
  }

  Future<void> _fetchData() async {
    setState(() {
      isLoading = true;
      error = null;
      page = 1;
    });

    try {
      final res = await cmsApi.getCategory("danh-sach", "hoat-hinh", page: page);
      if (res.data != null) {
        setState(() {
          movies = res.data!.items;
          domain = res.data!.domain;
          isLoading = false;
        });
      } else {
        setState(() {
          error = res.message ?? "Lỗi không xác định";
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        error = e.toString();
        isLoading = false;
      });
    }
  }

  Future<void> _fetchMore() async {
    if (isLoadingMore || isLoading) return;

    setState(() {
      isLoadingMore = true;
    });

    try {
      page++;
      final res = await cmsApi.getCategory("danh-sach", "hoat-hinh", page: page);
      if (res.data != null && res.data!.items.isNotEmpty) {
        setState(() {
          movies.addAll(res.data!.items);
          isLoadingMore = false;
        });
      } else {
        setState(() {
          isLoadingMore = false;
        });
      }
    } catch (e) {
      setState(() {
        isLoadingMore = false;
      });
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800 && size.shortestSide >= 500;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _isTvMode(context) ? const Color(0xFF0F0F0F) : Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: _isTvMode(context) ? const Color(0xFF0F0F0F) : null,
        title: const Text('Phim Hoạt Hình', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        centerTitle: true,
        actions: const [
          TvCastButton(),
        ],
      ),
      body: _buildBody(context),
    );
  }

  Widget _buildBody(BuildContext context) {
    if (isLoading && movies.isEmpty) {
      return Center(child: CircularProgressIndicator(color: Theme.of(context).primaryColor));
    }

    if (error != null && movies.isEmpty) {
      return ErrorView(error: error!, onRetry: _fetchData);
    }

    if (movies.isEmpty) {
      return const Center(child: Text("Không có phim nào", style: TextStyle(color: Colors.white)));
    }

    final isTv = _isTvMode(context);
    
    return RefreshIndicator(
      onRefresh: _fetchData,
      child: Column(
        children: [
          Expanded(
            child: GridView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(12),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: isTv ? 4 : 3,
                childAspectRatio: isTv ? 1.3 : 0.65,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
              ),
              itemCount: movies.length,
              itemBuilder: (context, index) {
                final movie = movies[index];
                
                return FocusableWrapper(
                  onTap: () => context.push('/movie/${movie.slug}'),
                  child: isTv 
                      ? YoutubeTvMovieCard(movie: movie, domain: domain)
                      : _buildMobileCard(movie),
                );
              },
            ),
          ),
          if (isLoadingMore)
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Center(child: CircularProgressIndicator(color: Theme.of(context).primaryColor)),
            )
        ],
      ),
    );
  }
  
  Widget _buildMobileCard(MovieItem movie) {
    String? getValidUrl(String? url) => (url != null && url.isNotEmpty) ? url : null;
    final tUrl = getValidUrl(movie.thumbUrl);
    final pUrl = getValidUrl(movie.posterUrl);
    final thumb = tUrl ?? pUrl ?? "";
    
    final imageUrl = thumb.startsWith('http') 
        ? thumb 
        : (thumb.startsWith('/') ? '$domain$thumb' : '$domain/$thumb');

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: CachedNetworkImage(
              imageUrl: imageUrl,
              fit: BoxFit.cover,
              width: double.infinity,
              placeholder: (context, url) => Container(color: Colors.grey[900]),
              errorWidget: (context, url, error) => Container(
                color: Colors.grey[900],
                child: const Icon(Icons.error, color: Colors.grey),
              ),
            ),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          movie.name,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
            fontSize: 12,
          ),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        if (movie.originName != null) ...[
          const SizedBox(height: 2),
          Text(
            movie.originName!,
            style: const TextStyle(
              color: Colors.grey,
              fontSize: 10,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ],
    );
  }
}
