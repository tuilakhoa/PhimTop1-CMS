import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/trending_provider.dart';
import '../widgets/focusable_wrapper.dart';

class TrendingScreen extends StatefulWidget {
  const TrendingScreen({super.key});

  @override
  State<TrendingScreen> createState() => _TrendingScreenState();
}

class _TrendingScreenState extends State<TrendingScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(() {
      if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
        context.read<TrendingProvider>().fetchTrending();
      }
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bảng Xếp Hạng'),
        centerTitle: true,
      ),
      body: Consumer<TrendingProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.movies.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }
          if (provider.error != null && provider.movies.isEmpty) {
            return Center(child: Text(provider.error!, style: const TextStyle(color: Colors.red)));
          }
          if (provider.movies.isEmpty) {
            return const Center(child: Text('Chưa có dữ liệu lượt xem phim.'));
          }

          return RefreshIndicator(
            onRefresh: () => provider.fetchTrending(refresh: true),
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: provider.movies.length + (provider.hasMore ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == provider.movies.length) {
                  return const Center(
                    child: Padding(
                      padding: EdgeInsets.all(16.0),
                      child: CircularProgressIndicator(),
                    ),
                  );
                }

                final movie = provider.movies[index];
                final rank = index + 1;
                
                String? getValidUrl(String? url) => (url != null && url.isNotEmpty) ? url : null;
                final pUrl = getValidUrl(movie.posterUrl);
                final tUrl = getValidUrl(movie.thumbUrl);
                final thumb = tUrl ?? pUrl ?? "";
                
                final imageUrl = thumb.startsWith('http') 
                    ? thumb 
                    : (thumb.startsWith('/') ? '${provider.domain}$thumb' : '${provider.domain}/$thumb');

                Color rankColor = Colors.grey[600]!;
                if (rank == 1) rankColor = Colors.red[600]!;
                else if (rank == 2) rankColor = Colors.orange[500]!;
                else if (rank == 3) rankColor = Colors.yellow[600]!;

                return FocusableWrapper(
                  onTap: () => context.push('/movie/${movie.slug}'),
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 16),
                    height: 130,
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardColor,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.white10, width: 0.5),
                    ),
                    child: Row(
                      children: [
                        // Rank Number
                        Container(
                          width: 50,
                          alignment: Alignment.center,
                          child: Text(
                            '$rank',
                            style: TextStyle(
                              color: rank <= 3 ? rankColor : Colors.white54,
                              fontSize: rank <= 3 ? 32 : 24,
                              fontWeight: FontWeight.w900,
                              fontStyle: FontStyle.italic,
                            ),
                          ),
                        ),
                        // Thumbnail
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: CachedNetworkImage(
                            imageUrl: imageUrl,
                            width: 85,
                            height: 110,
                            fit: BoxFit.cover,
                            placeholder: (context, url) => Container(color: Colors.grey[900]),
                            errorWidget: (context, url, error) => Container(color: Colors.grey[900], child: const Icon(Icons.error)),
                          ),
                        ),
                        const SizedBox(width: 16),
                        // Details
                        Expanded(
                          child: Padding(
                            padding: const EdgeInsets.symmetric(vertical: 12.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  movie.name,
                                  style: TextStyle(
                                    color: rank <= 3 ? rankColor : Colors.white, 
                                    fontSize: 16, 
                                    fontWeight: FontWeight.bold
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 4),
                                if (movie.originName != null)
                                  Text(
                                    movie.originName!,
                                    style: const TextStyle(color: Colors.grey, fontSize: 13),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    const Icon(Icons.whatshot, color: Colors.orange, size: 16),
                                    const SizedBox(width: 4),
                                    Text(
                                      '${movie.view ?? 0}',
                                      style: const TextStyle(color: Colors.orange, fontSize: 13, fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
