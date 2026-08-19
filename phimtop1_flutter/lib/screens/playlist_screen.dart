import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:go_router/go_router.dart';
import '../providers/playlist_provider.dart';
import '../models/models.dart';
import '../widgets/error_view.dart';

class PlaylistScreen extends StatefulWidget {
  const PlaylistScreen({super.key});

  @override
  State<PlaylistScreen> createState() => _PlaylistScreenState();
}

class _PlaylistScreenState extends State<PlaylistScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PlaylistProvider>().fetchPlaylists();
    });
  }

  void _showCreateDialog() {
    final TextEditingController controller = TextEditingController();
    showDialog(
      context: context,
      builder: (context) {
        final isDark = Theme.of(context).brightness == Brightness.dark;
        final textColor = isDark ? Colors.white : Colors.black;

        return AlertDialog(
          backgroundColor: isDark ? Colors.grey[900] : Colors.white,
          title: Text('Tạo danh sách phát mới', style: TextStyle(color: textColor)),
          content: TextField(
            controller: controller,
            style: TextStyle(color: textColor),
            decoration: InputDecoration(
              hintText: 'Tên danh sách',
              hintStyle: const TextStyle(color: Colors.grey),
              enabledBorder: const UnderlineInputBorder(borderSide: BorderSide(color: Colors.grey)),
              focusedBorder: UnderlineInputBorder(borderSide: BorderSide(color: Theme.of(context).primaryColor)),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Hủy', style: TextStyle(color: Colors.grey)),
            ),
            TextButton(
              onPressed: () async {
                final name = controller.text.trim();
                if (name.isNotEmpty) {
                  final provider = context.read<PlaylistProvider>();
                  final success = await provider.createPlaylist(name);
                  if (mounted) {
                    Navigator.pop(context);
                    if (!success) {
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lỗi khi tạo danh sách phát')));
                    }
                  }
                }
              },
              child: Text('Tạo', style: TextStyle(color: Theme.of(context).primaryColor)),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;

    return Scaffold(
      appBar: AppBar(
        title: Text('Danh sách phát', style: TextStyle(color: textColor, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        actions: [
          IconButton(
            icon: Icon(Icons.add, color: textColor),
            onPressed: _showCreateDialog,
          ),
        ],
      ),
      body: Consumer<PlaylistProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.playlists.isEmpty) {
            return Center(child: CircularProgressIndicator(color: Theme.of(context).primaryColor));
          }
          if (provider.error != null && provider.playlists.isEmpty) {
            return ErrorView(
              error: provider.error!,
              onRetry: provider.fetchPlaylists,
            );
          }
          if (provider.playlists.isEmpty) {
            return Center(child: Text("Bạn chưa có danh sách phát nào", style: TextStyle(color: textColor)));
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: provider.playlists.length,
            itemBuilder: (context, index) {
              final playlist = provider.playlists[index];
              return Card(
                color: isDark ? Colors.grey[900] : Colors.white,
                margin: const EdgeInsets.only(bottom: 16),
                child: ExpansionTile(
                  title: Text(playlist.name, style: TextStyle(color: textColor, fontWeight: FontWeight.bold)),
                  subtitle: Text("${playlist.items?.length ?? 0} phim", style: const TextStyle(color: Colors.grey, fontSize: 12)),
                  iconColor: textColor,
                  collapsedIconColor: Colors.grey,
                  children: [
                    if (playlist.items == null || playlist.items!.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(16.0),
                        child: Text("Danh sách trống", style: TextStyle(color: Colors.grey)),
                      )
                    else
                      ...playlist.items!.map((item) {
                        final thumb = item.thumbUrl ?? "";
                        final imageUrl = thumb.startsWith('http') ? thumb : 'https://phimimg.com/$thumb';
                        
                        return ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                          leading: ClipRRect(
                            borderRadius: BorderRadius.circular(4),
                            child: SizedBox(
                              width: 40,
                              height: 60,
                              child: CachedNetworkImage(
                                imageUrl: imageUrl,
                                fit: BoxFit.cover,
                                placeholder: (context, url) => Container(color: isDark ? Colors.grey[800] : Colors.grey[200]),
                                errorWidget: (context, url, error) => Container(color: isDark ? Colors.grey[800] : Colors.grey[200]),
                              ),
                            ),
                          ),
                          title: Text(item.movieName, style: TextStyle(color: textColor, fontSize: 14)),
                          trailing: IconButton(
                            icon: const Icon(Icons.close, color: Colors.grey, size: 20),
                            onPressed: () async {
                              final confirm = await showDialog<bool>(
                                context: context,
                                builder: (context) {
                                  final isDark = Theme.of(context).brightness == Brightness.dark;
                                  final textColor = isDark ? Colors.white : Colors.black;
                                  return AlertDialog(
                                    backgroundColor: isDark ? Colors.grey[900] : Colors.white,
                                    title: Text("Xóa phim", style: TextStyle(color: textColor)),
                                    content: const Text("Xóa phim này khỏi danh sách phát?", style: TextStyle(color: Colors.grey)),
                                  actions: [
                                    TextButton(onPressed: () => Navigator.pop(context, false), child: const Text("Hủy", style: TextStyle(color: Colors.grey))),
                                    TextButton(onPressed: () => Navigator.pop(context, true), child: const Text("Xóa", style: TextStyle(color: Colors.red))),
                                  ],
                                  );
                                },
                              );
                              if (confirm == true) {
                                provider.removeFromPlaylist(playlist.id, item.movieSlug);
                              }
                            },
                          ),
                          onTap: () {
                            context.push('/movie/${item.movieSlug}');
                          },
                        );
                      }).toList(),
                    Padding(
                      padding: const EdgeInsets.all(8.0),
                      child: TextButton.icon(
                        icon: const Icon(Icons.delete, color: Colors.red, size: 16),
                        label: const Text("Xóa danh sách này", style: TextStyle(color: Colors.red)),
                        onPressed: () async {
                          final confirm = await showDialog<bool>(
                            context: context,
                            builder: (context) {
                              final isDark = Theme.of(context).brightness == Brightness.dark;
                              final textColor = isDark ? Colors.white : Colors.black;
                              return AlertDialog(
                                backgroundColor: isDark ? Colors.grey[900] : Colors.white,
                                title: Text("Xóa danh sách", style: TextStyle(color: textColor)),
                                content: const Text("Bạn có chắc chắn muốn xóa danh sách phát này?", style: TextStyle(color: Colors.grey)),
                              actions: [
                                TextButton(onPressed: () => Navigator.pop(context, false), child: const Text("Hủy", style: TextStyle(color: Colors.grey))),
                                TextButton(onPressed: () => Navigator.pop(context, true), child: const Text("Xóa", style: TextStyle(color: Colors.red))),
                              ],
                              );
                            },
                          );
                          if (confirm == true) {
                            provider.deletePlaylist(playlist.id);
                          }
                        },
                      ),
                    ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }
}
