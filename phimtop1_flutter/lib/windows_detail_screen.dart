import 'package:flutter/material.dart' show ThemeMode, Material, Brightness;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import 'dart:ui' show ImageFilter;
import '../providers/detail_provider.dart';
import '../models/models.dart';
import 'main_windows.dart';

class WindowsDetailScreen extends StatefulWidget {
  final String movieSlug;

  const WindowsDetailScreen({Key? key, required this.movieSlug}) : super(key: key);

  @override
  State<WindowsDetailScreen> createState() => _WindowsDetailScreenState();
}

class _WindowsDetailScreenState extends State<WindowsDetailScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<DetailProvider>().fetchDetail(widget.movieSlug);
    });
  }

  String _getImage(String? url, String domain) {
    if (url == null || url.isEmpty) return '';
    if (url.startsWith('http')) return url;
    return '$domain/$url';
  }

  @override
  Widget build(BuildContext context) {
    final detailProvider = context.watch<DetailProvider>();

    if (detailProvider.isLoading) {
      return const ScaffoldPage(
        content: Center(child: ProgressRing()),
      );
    }

    if (detailProvider.error != null) {
      return ScaffoldPage(
        header: PageHeader(
          leading: IconButton(
            icon: const Icon(FluentIcons.back),
            onPressed: () => Navigator.pop(context),
          ),
          title: const Text('Lỗi'),
        ),
        content: Center(child: Text(detailProvider.error!)),
      );
    }

    final movie = detailProvider.movie;
    if (movie == null) return const SizedBox.shrink();

    final domain = detailProvider.domain.isNotEmpty ? detailProvider.domain : 'https://phimimg.com';
    final posterUrl = _getImage(movie.posterUrl, domain);
    final thumbUrl = _getImage(movie.thumbUrl, domain);

    return ScaffoldPage(
      padding: EdgeInsets.zero,
      content: Stack(
        children: [
          // Background Image
          if (thumbUrl.isNotEmpty)
            Positioned.fill(
              child: Image.network(
                thumbUrl,
                fit: BoxFit.cover,
                errorBuilder: (c, e, s) => const SizedBox(),
              ),
            ),
          
          // Dark Gradient Overlay instead of Blur (Fixes lag and improves contrast)
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Colors.black.withOpacity(0.9),
                    Colors.black.withOpacity(0.6),
                    Colors.black.withOpacity(0.9),
                  ],
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                ),
              ),
            ),
          ),
          
          // Content
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: IconButton(
                  icon: const Icon(FluentIcons.back, color: Colors.white),
                  onPressed: () => Navigator.pop(context),
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 40.0, vertical: 20.0),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Poster with shadow
                      if (posterUrl.isNotEmpty)
                        Container(
                          decoration: BoxDecoration(
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.5),
                                blurRadius: 20,
                                offset: const Offset(0, 10),
                              )
                            ],
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.network(
                              posterUrl,
                              width: 300,
                              height: 450,
                              fit: BoxFit.cover,
                              errorBuilder: (c, e, s) => Container(
                                width: 300, height: 450, color: Colors.grey,
                                child: const Center(child: Icon(FluentIcons.error, size: 50)),
                              ),
                            ),
                          ),
                        ),
                      const SizedBox(width: 50),
                      
                      // Info
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              movie.name,
                              style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: Colors.white),
                            ),
                            if (movie.originName != null && movie.originName!.isNotEmpty)
                              Padding(
                                padding: const EdgeInsets.only(top: 8.0, bottom: 20.0),
                                child: Text(
                                  movie.originName!,
                                  style: TextStyle(fontSize: 24, color: Colors.white.withOpacity(0.7)),
                                ),
                              ),
                            
                            // Meta tags
                            Wrap(
                              spacing: 12,
                              children: [
                                if (movie.year != null) _buildTag(context, movie.year.toString()),
                                if (movie.episodeCurrent != null) _buildTag(context, movie.episodeCurrent!),
                                if (movie.time != null) _buildTag(context, movie.time!),
                              ],
                            ),
                            
                            const SizedBox(height: 30),
                            
                            // Watch Button
                            if (detailProvider.episodes.isNotEmpty)
                              FilledButton(
                                onPressed: () {
                                  Navigator.push(context, FluentPageRoute(builder: (_) => WindowsVideoPlayerScreen(movieSlug: movie.slug)));
                                },
                                child: const Padding(
                                  padding: EdgeInsets.symmetric(horizontal: 30.0, vertical: 12.0),
                                  child: Text('▶ Xem Phim', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                                ),
                              ),
                            
                            const SizedBox(height: 40),
                            
                            // Description
                            const Text('Nội dung phim', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white)),
                            const SizedBox(height: 12),
                            Text(
                              _stripHtml(movie.content ?? 'Đang cập nhật...'),
                              style: TextStyle(fontSize: 16, height: 1.6, color: Colors.white.withOpacity(0.9)),
                            ),
                            
                            const SizedBox(height: 40),
                            
                            // Episodes List
                            if (detailProvider.episodes.isNotEmpty && detailProvider.episodes[0].serverData.length > 1) ...[
                              const Text('Danh sách tập', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white)),
                              const SizedBox(height: 16),
                              Wrap(
                                spacing: 10,
                                runSpacing: 10,
                                children: detailProvider.episodes[0].serverData.map((ep) {
                                  return Button(
                                    child: Padding(
                                      padding: const EdgeInsets.all(4.0),
                                      child: Text(ep.name, style: const TextStyle(fontWeight: FontWeight.w600)),
                                    ),
                                    onPressed: () {
                                      detailProvider.changeEpisode(
                                        detailProvider.episodes[0].serverData.indexOf(ep),
                                        0,
                                      );
                                      Navigator.push(context, FluentPageRoute(builder: (_) => WindowsVideoPlayerScreen(movieSlug: movie.slug)));
                                    },
                                  );
                                }).toList(),
                              )
                            ]
                          ],
                        ),
                      )
                    ],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTag(BuildContext context, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.1),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: Colors.white.withOpacity(0.2)),
      ),
      child: Text(text, style: const TextStyle(fontSize: 14, color: Colors.white, fontWeight: FontWeight.w500)),
    );
  }


  String _stripHtml(String htmlString) {
    RegExp exp = RegExp(r"<[^>]*>", multiLine: true, caseSensitive: true);
    return htmlString.replaceAll(exp, '').trim();
  }
}
