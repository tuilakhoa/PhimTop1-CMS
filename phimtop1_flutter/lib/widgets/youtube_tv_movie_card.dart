import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/models.dart';

class YoutubeTvMovieCard extends StatelessWidget {
  final MovieItem movie;
  final String domain;
  final bool isFocused;

  const YoutubeTvMovieCard({
    super.key,
    required this.movie,
    required this.domain,
    this.isFocused = false,
  });

  @override
  Widget build(BuildContext context) {
    String? getValidUrl(String? url) => (url != null && url.isNotEmpty) ? url : null;
    
    // For 16:9 aspect ratio, thumbUrl is usually better if available and landscape,
    // but often posterUrl is all we have. We'll use posterUrl as fallback.
    final tUrl = getValidUrl(movie.thumbUrl);
    final pUrl = getValidUrl(movie.posterUrl);
    
    // Prefer thumbUrl for horizontal layout
    final thumb = (tUrl ?? pUrl ?? "");
        
    final imageUrl = thumb.startsWith('http') 
        ? thumb 
        : (thumb.startsWith('/') ? '$domain$thumb' : '$domain/$thumb');

    return AnimatedScale(
      scale: isFocused ? 1.05 : 1.0,
      duration: const Duration(milliseconds: 200),
      curve: Curves.easeOutCubic,
      child: Container(
        width: double.infinity,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AspectRatio(
              aspectRatio: 16 / 9,
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                curve: Curves.easeOutCubic,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isFocused ? Colors.white : Colors.transparent,
                    width: isFocused ? 3 : 0,
                  ),
                  boxShadow: isFocused
                      ? [
                          BoxShadow(
                            color: Colors.white.withOpacity(0.3),
                            blurRadius: 10,
                            spreadRadius: 1,
                            offset: const Offset(0, 4),
                          )
                        ]
                      : [],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(9), // slightly smaller than container to fit inside border
                  child: CachedNetworkImage(
                    imageUrl: imageUrl,
                    fit: BoxFit.cover,
                    placeholder: (context, url) => Container(
                      color: Colors.grey[900],
                      child: const Center(child: CircularProgressIndicator()),
                    ),
                    errorWidget: (context, url, error) => Container(
                      color: Colors.grey[900],
                      child: const Icon(Icons.error, color: Colors.white54),
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),
            Flexible(
              child: Text(
                movie.name,
                style: TextStyle(
                  color: isFocused ? Colors.white : Colors.white.withOpacity(0.9),
                  fontWeight: FontWeight.bold,
                  fontSize: 15,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (movie.originName != null && movie.originName!.isNotEmpty) ...[
              const SizedBox(height: 4),
              Flexible(
                child: Text(
                  movie.originName!,
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.5),
                    fontSize: 13,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
