import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:io';

class UpdateDialog extends StatefulWidget {
  final String version;
  final String message;
  final String downloadUrl;
  final bool forceUpdate;

  const UpdateDialog({
    Key? key,
    required this.version,
    required this.message,
    required this.downloadUrl,
    required this.forceUpdate,
  }) : super(key: key);

  @override
  State<UpdateDialog> createState() => _UpdateDialogState();
}

class _UpdateDialogState extends State<UpdateDialog> {
  bool _isDownloading = false;
  double _progress = 0.0;
  String _statusMessage = '';

  Future<void> _startDownload() async {
    if (!widget.downloadUrl.endsWith('.apk')) {
      // If it's not a direct APK link (e.g. Play Store), just launch it
      final uri = Uri.parse(widget.downloadUrl);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
      return;
    }

    setState(() {
      _isDownloading = true;
      _progress = 0.0;
      _statusMessage = 'Đang tải bản cập nhật...';
    });

    try {
      final dio = Dio();
      final dir = await getExternalStorageDirectory() ?? await getApplicationDocumentsDirectory();
      final filePath = '${dir.path}/update_v${widget.version}.apk';

      await dio.download(
        widget.downloadUrl,
        filePath,
        onReceiveProgress: (received, total) {
          if (total != -1) {
            setState(() {
              _progress = received / total;
              _statusMessage = 'Đang tải... ${( _progress * 100).toStringAsFixed(0)}%';
            });
          }
        },
      );

      setState(() {
        _statusMessage = 'Đang mở bộ cài đặt...';
      });

      final result = await OpenFilex.open(filePath);
      if (result.type != ResultType.done) {
        setState(() {
          _statusMessage = 'Không thể tự động cài đặt. Vui lòng cập nhật thủ công.';
          _isDownloading = false;
        });
      } else {
        if (!widget.forceUpdate) {
          if (mounted) Navigator.pop(context);
        }
      }
    } catch (e) {
      setState(() {
        _statusMessage = 'Có lỗi xảy ra khi tải. Vui lòng thử lại sau.';
        _isDownloading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async => !widget.forceUpdate && !_isDownloading,
      child: AlertDialog(
        backgroundColor: const Color(0xFF1E1E1E),
        title: Text('Cập nhật mới (v${widget.version})', style: const TextStyle(color: Colors.white)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.message,
              style: const TextStyle(color: Colors.white70),
            ),
            if (_isDownloading) ...[
              const SizedBox(height: 20),
              LinearProgressIndicator(
                value: _progress,
                backgroundColor: Colors.grey[800],
                color: Colors.red,
              ),
              const SizedBox(height: 8),
              Text(
                _statusMessage,
                style: const TextStyle(color: Colors.redAccent, fontSize: 13),
              ),
            ]
          ],
        ),
        actions: [
          if (!widget.forceUpdate && !_isDownloading)
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Để sau', style: TextStyle(color: Colors.grey)),
            ),
          if (!_isDownloading)
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: Theme.of(context).primaryColor),
              onPressed: () {
                if (Platform.isIOS) {
                  // For iOS, always open the link
                  final uri = Uri.parse(widget.downloadUrl);
                  canLaunchUrl(uri).then((canLaunch) {
                    if (canLaunch) {
                      launchUrl(uri, mode: LaunchMode.externalApplication);
                    }
                  });
                } else {
                  // Android: try to download if it's an APK, else launch url
                  _startDownload();
                }
              },
              child: const Text('Cập nhật ngay', style: TextStyle(color: Colors.white)),
            ),
        ],
      ),
    );
  }
}
