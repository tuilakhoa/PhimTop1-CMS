import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/models.dart';

class YoukuMovieCard extends StatelessWidget {
  final MovieItem movie;
  final String domain;
  final bool isFeatured;

  const YoukuMovieCard({
    super.key,
    required this.movie,
    required this.domain,
    this.isFeatured = false,
  });

  @override
  Widget build(BuildContext context) {
    String? getValidUrl(String? url) => (url != null && url.isNotEmpty) ? url : null;
    final pUrl = getValidUrl(movie.posterUrl);
    final tUrl = getValidUrl(movie.thumbUrl);

    final thumb = isFeatured 
        ? (pUrl ?? tUrl ?? "") 
        : (tUrl ?? pUrl ?? "");
        
    final imageUrl = thumb.startsWith('http') 
        ? thumb 
        : (thumb.startsWith('/') ? '$domain$thumb' : '$domain/$thumb');

    return Container(
      width: isFeatured ? MediaQuery.of(context).size.width * 0.85 : 160,
      margin: const EdgeInsets.only(right: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: CachedNetworkImage(
                imageUrl: imageUrl,
                fit: BoxFit.cover,
                width: double.infinity,
                placeholder: (context, url) => Container(
                  color: Theme.of(context).brightness == Brightness.dark ? Colors.grey[900] : Colors.grey[200],
                  child: const Center(child: CircularProgressIndicator()),
                ),
                errorWidget: (context, url, error) => Container(
                  color: Theme.of(context).brightness == Brightness.dark ? Colors.grey[900] : Colors.grey[200],
                  child: const Icon(Icons.error),
                ),
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            movie.name,
            style: TextStyle(
              color: Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black,
              fontWeight: FontWeight.bold,
              fontSize: 14,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          if (movie.originName != null) ...[
            const SizedBox(height: 4),
            Text(
              movie.originName!,
              style: const TextStyle(
                color: Colors.grey,
                fontSize: 12,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      ),
    );
  }
}
