import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:go_router/go_router.dart';
import '../models/models.dart';
import 'focusable_wrapper.dart';

class FeaturedSlider extends StatefulWidget {
  final List<MovieItem> movies;
  final String domain;

  const FeaturedSlider({
    super.key,
    required this.movies,
    required this.domain,
  });

  @override
  State<FeaturedSlider> createState() => _FeaturedSliderState();
}

class _FeaturedSliderState extends State<FeaturedSlider> {
  late PageController _pageController;
  int _currentPage = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(initialPage: 0);
    _startAutoPlay();
  }

  void _startAutoPlay() {
    _timer = Timer.periodic(const Duration(seconds: 5), (timer) {
      if (_currentPage < widget.movies.length - 1) {
        _currentPage++;
      } else {
        _currentPage = 0;
      }
      if (_pageController.hasClients) {
        _pageController.animateToPage(
          _currentPage,
          duration: const Duration(milliseconds: 800),
          curve: Curves.fastOutSlowIn,
        );
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.movies.isEmpty) return const SizedBox();

    // Responsive height: ~60% of screen height
    final screenHeight = MediaQuery.of(context).size.height;
    final sliderHeight = screenHeight * 0.6;

    return SizedBox(
      height: sliderHeight,
      width: double.infinity,
      child: Stack(
        children: [
          PageView.builder(
            controller: _pageController,
            onPageChanged: (index) {
              setState(() {
                _currentPage = index;
              });
            },
            itemCount: widget.movies.length,
            itemBuilder: (context, index) {
              final movie = widget.movies[index];
              return _buildSlide(context, movie, sliderHeight);
            },
          ),
          
          // Pagination Indicator (dots)
          Positioned(
            bottom: 16,
            right: 16,
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: List.generate(
                widget.movies.length,
                (index) => AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  height: 6,
                  width: _currentPage == index ? 16 : 6,
                  decoration: BoxDecoration(
                    color: _currentPage == index ? Colors.white : Colors.white.withOpacity(0.4),
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSlide(BuildContext context, MovieItem movie, double sliderHeight) {
    String? getValidUrl(String? url) => (url != null && url.isNotEmpty) ? url : null;
    
    // Prefer posterUrl for featured slider
    final pUrl = getValidUrl(movie.posterUrl);
    final tUrl = getValidUrl(movie.thumbUrl);
    final thumb = pUrl ?? tUrl ?? "";
        
    final imageUrl = thumb.startsWith('http') 
        ? thumb 
        : (thumb.startsWith('/') ? '${widget.domain}$thumb' : '${widget.domain}/$thumb');

    // Extract categories
    String categoryText = "Phim Đề Cử";
    if (movie.category != null && movie.category!.isNotEmpty) {
      categoryText = movie.category!.map((e) => e.name).join(' • ');
    }
    
    // Year text
    String yearText = movie.year?.toString() ?? "";
    String subtitle = yearText.isNotEmpty ? "$yearText | $categoryText" : categoryText;

    return FocusableWrapper(
      onTap: () => context.push('/movie/${movie.slug}'),
      child: Stack(
        fit: StackFit.expand,
        children: [
          // Background Image
          CachedNetworkImage(
            imageUrl: imageUrl,
            fit: BoxFit.cover,
            alignment: Alignment.topCenter,
            placeholder: (context, url) => Container(
              color: Theme.of(context).scaffoldBackgroundColor,
              child: const Center(child: CircularProgressIndicator()),
            ),
            errorWidget: (context, url, error) => Container(
              color: Theme.of(context).scaffoldBackgroundColor,
              child: const Icon(Icons.error),
            ),
          ),
          
          // Gradient Overlay
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.transparent,
                  Colors.black.withOpacity(0.0),
                  Colors.black.withOpacity(0.7),
                  Theme.of(context).scaffoldBackgroundColor,
                ],
                stops: const [0.0, 0.5, 0.85, 1.0],
              ),
            ),
          ),

          // Content Overlay
          Positioned(
            left: 16,
            right: 16,
            bottom: 32,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                // Left Side: Texts & Tags
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Movie Title
                      Text(
                        movie.name,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 28,
                          fontWeight: FontWeight.w900,
                          height: 1.2,
                          shadows: [
                            Shadow(color: Colors.black54, blurRadius: 4, offset: Offset(0, 2)),
                          ],
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 8),
                      
                      // Tags Row
                      Row(
                        children: [
                          _buildTag("TOP 1", color: const Color(0xFF00E359)),
                          const SizedBox(width: 8),
                          _buildTag("Thịnh Hành", color: Colors.amber),
                          const SizedBox(width: 8),
                          _buildTag("FHD", outlined: true),
                        ],
                      ),
                      const SizedBox(height: 8),
                      
                      // Categories / Info Row
                      Text(
                        subtitle,
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.8),
                          fontSize: 13,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(width: 16),
                
                // Right Side: Play Button
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    color: const Color(0xFF00E359), // iQIYI Green
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF00E359).withOpacity(0.4),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.play_arrow_rounded,
                    color: Colors.white,
                    size: 36,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTag(String text, {Color? color, bool outlined = false}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: outlined ? Colors.transparent : (color ?? Colors.grey[800]),
        border: outlined ? Border.all(color: Colors.white54, width: 1) : null,
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: outlined ? Colors.white70 : Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
