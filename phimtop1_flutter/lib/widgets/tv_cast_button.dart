import 'package:flutter/material.dart';
import '../services/tv_remote_service.dart';

class TvCastButton extends StatefulWidget {
  const TvCastButton({super.key});

  @override
  State<TvCastButton> createState() => _TvCastButtonState();
}

class _TvCastButtonState extends State<TvCastButton> {
  final TvRemoteService _tvService = TvRemoteService();

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _tvService,
      builder: (context, child) {
        if (_tvService.isServerRunning) {
          // If this device is acting as the TV server, don't show the cast button
          return const SizedBox.shrink();
        }

        final isConnected = _tvService.isClientConnected;
        return IconButton(
          icon: Icon(
            isConnected ? Icons.cast_connected : Icons.cast,
            color: isConnected ? Colors.green : Colors.grey,
          ),
          onPressed: () {
            if (isConnected) {
              _showDisconnectDialog(context);
            } else {
              _showDiscoveryBottomSheet(context);
            }
          },
        );
      },
    );
  }

  void _showDisconnectDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Ngắt kết nối"),
        content: const Text("Bạn có muốn ngắt kết nối với TV hiện tại không?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Hủy"),
          ),
          TextButton(
            onPressed: () {
              _tvService.disconnectFromTv();
              Navigator.pop(context);
            },
            child: const Text("Ngắt kết nối", style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  void _showDiscoveryBottomSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (bottomSheetContext) {
        return FutureBuilder<List<Map<String, String>>>(
          future: _tvService.discoverTvs(),
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const SizedBox(
                height: 250,
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      CircularProgressIndicator(),
                      SizedBox(height: 16),
                      Text("Đang tìm kiếm TV...", style: TextStyle(color: Colors.white)),
                    ],
                  ),
                ),
              );
            }

            final tvs = snapshot.data ?? [];
            if (tvs.isEmpty) {
              return SizedBox(
                height: 250,
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.tv_off, size: 64, color: Colors.grey),
                      const SizedBox(height: 16),
                      const Text("Không tìm thấy TV nào", style: TextStyle(color: Colors.white, fontSize: 18)),
                      const SizedBox(height: 8),
                      const Text("Hãy chắc chắn TV và điện thoại dùng chung mạng Wi-Fi", style: TextStyle(color: Colors.grey, fontSize: 14)),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () {
                          Navigator.pop(bottomSheetContext);
                          _showDiscoveryBottomSheet(this.context);
                        },
                        child: const Text("Thử lại"),
                      )
                    ],
                  ),
                ),
              );
            }

            return Container(
              padding: const EdgeInsets.all(16),
              height: 350,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text("Chọn TV để kết nối", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                  const SizedBox(height: 16),
                  Expanded(
                    child: ListView.builder(
                      itemCount: tvs.length,
                      itemBuilder: (context, index) {
                        final tv = tvs[index];
                        return ListTile(
                          leading: const Icon(Icons.tv, color: Colors.white),
                          title: Text(tv['name'] ?? "TV Không tên", style: const TextStyle(color: Colors.white)),
                          subtitle: Text(tv['ip'] ?? "", style: const TextStyle(color: Colors.grey)),
                          onTap: () {
                            Navigator.pop(bottomSheetContext);
                            _showPinDialog(tv['ip']!);
                          },
                        );
                      },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _showPinDialog(String ip) {
    final TextEditingController pinController = TextEditingController();
    final safeContext = this.context;
    
    showDialog(
      context: safeContext,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text("Nhập mã PIN"),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text("Vui lòng xem mã PIN trên màn hình TV và nhập vào bên dưới:"),
              const SizedBox(height: 16),
              TextField(
                controller: pinController,
                keyboardType: TextInputType.number,
                maxLength: 4,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 24, letterSpacing: 8, fontWeight: FontWeight.bold),
                decoration: const InputDecoration(
                  hintText: "----",
                  border: OutlineInputBorder(),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text("Hủy"),
            ),
            TextButton(
              onPressed: () async {
                final pin = pinController.text;
                if (pin.length < 4) {
                  // Nếu nhập chưa đủ 4 số, báo lỗi nhẹ
                  ScaffoldMessenger.of(dialogContext).showSnackBar(
                    const SnackBar(content: Text("Vui lòng nhập đủ 4 số mã PIN")),
                  );
                  return;
                }
                
                // Lấy messenger từ dialogContext trước khi pop
                final scaffoldMessenger = ScaffoldMessenger.of(dialogContext);
                Navigator.pop(dialogContext);
                
                scaffoldMessenger.showSnackBar(
                  const SnackBar(content: Text("Đang kết nối tới TV...")),
                );
                
                final success = await _tvService.connectToTv(ip, pin);
                if (mounted) {
                  scaffoldMessenger.showSnackBar(
                    SnackBar(content: Text(success ? "Đã kết nối thành công!" : "Mã PIN không đúng hoặc kết nối thất bại. Vui lòng mở Firewall trên TV nếu chưa mở.")),
                  );
                }
              },
              child: const Text("Kết nối"),
            ),
          ],
        );
      },
    );
  }
}
