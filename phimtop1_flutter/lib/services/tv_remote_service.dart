import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:network_info_plus/network_info_plus.dart';

class TvRemoteService extends ChangeNotifier {
  static final TvRemoteService _instance = TvRemoteService._internal();
  factory TvRemoteService() => _instance;
  TvRemoteService._internal();

  HttpServer? _server;
  WebSocket? _clientSocket;
  RawDatagramSocket? _udpServer;
  final List<WebSocket> _connectedClients = [];

  bool get isServerRunning => _server != null;
  bool get isClientConnected => _clientSocket != null && _clientSocket!.readyState == WebSocket.open;
  
  String _serverIp = "";
  String get serverIp => _serverIp;

  String _currentPin = "";
  String get currentPin => _currentPin;

  // Stream for TV to listen to play commands
  final _playCommandController = StreamController<String>.broadcast();
  Stream<String> get onPlayCommand => _playCommandController.stream;

  final _playDirectCommandController = StreamController<Map<String, dynamic>>.broadcast();
  Stream<Map<String, dynamic>> get onPlayDirectCommand => _playDirectCommandController.stream;

  final _playerActionController = StreamController<Map<String, dynamic>>.broadcast();
  Stream<Map<String, dynamic>> get onPlayerAction => _playerActionController.stream;

  // Start Server (TV Side)
  Future<void> startServer() async {
    if (isServerRunning) return;

    try {
      String? ip;
      try {
        if (!kIsWeb && (Platform.isLinux || Platform.isWindows || Platform.isMacOS)) {
          final interfaces = await NetworkInterface.list(type: InternetAddressType.IPv4, includeLoopback: false);
          for (var interface in interfaces) {
            final name = interface.name.toLowerCase();
            if (!name.startsWith('docker') && !name.startsWith('virbr') && !name.startsWith('veth')) {
              ip = interface.addresses.first.address;
              break;
            }
          }
          if (ip == null && interfaces.isNotEmpty) {
            ip = interfaces.first.addresses.first.address;
          }
        } else {
          final info = NetworkInfo();
          ip = await info.getWifiIP();
        }
      } catch (e) {
        debugPrint("Error getting IP: $e");
      }
      _serverIp = ip ?? "127.0.0.1";
      
      _currentPin = (Random().nextInt(9000) + 1000).toString(); // 4-digit PIN

      // Start WebSocket Server
      _server = await HttpServer.bind(InternetAddress.anyIPv4, 8080);
      
      // Start UDP Server for Discovery
      _udpServer = await RawDatagramSocket.bind(InternetAddress.anyIPv4, 8081);
      _udpServer!.broadcastEnabled = true;
      _udpServer!.listen((RawSocketEvent event) {
        if (event == RawSocketEvent.read) {
          Datagram? dg = _udpServer!.receive();
          if (dg != null) {
            String msg = utf8.decode(dg.data);
            if (msg == "DISCOVER_PHIMTOP1_TV") {
              // Reply with presence
              _udpServer!.send(utf8.encode("PHIMTOP1_TV:$_serverIp:PhimTop1 TV"), dg.address, dg.port);
            }
          }
        }
      });

      notifyListeners();

      _server!.listen((HttpRequest request) {
        if (WebSocketTransformer.isUpgradeRequest(request)) {
          WebSocketTransformer.upgrade(request).then((WebSocket socket) {
            bool isAuthenticated = false;

            socket.listen(
              (message) {
                try {
                  final data = jsonDecode(message);
                  
                  if (!isAuthenticated) {
                    if (data['action'] == 'auth' && data['pin'] == _currentPin) {
                      isAuthenticated = true;
                      _connectedClients.add(socket);
                      notifyListeners();
                      socket.add(jsonEncode({"status": "auth_success"}));
                    } else {
                      socket.add(jsonEncode({"status": "auth_failed"}));
                      socket.close();
                    }
                    return;
                  }

                  if (data['action'] == 'play' && data['slug'] != null) {
                    _playCommandController.add(data['slug']);
                  } else if (data['action'] == 'play_direct') {
                    _playDirectCommandController.add(data);
                  } else if (data['action'] == 'player_control') {
                    _playerActionController.add(data);
                  }
                } catch (e) {
                  debugPrint("Error parsing message: $e");
                }
              },
              onDone: () {
                _connectedClients.remove(socket);
                notifyListeners();
              },
              onError: (e) {
                _connectedClients.remove(socket);
                notifyListeners();
              },
            );
          });
        }
      });
    } catch (e) {
      debugPrint("Failed to start server: $e");
    }
  }

  void stopServer() {
    _server?.close();
    _server = null;
    _udpServer?.close();
    _udpServer = null;
    _currentPin = "";
    
    for (var socket in _connectedClients) {
      socket.close();
    }
    _connectedClients.clear();
    notifyListeners();
  }

  // Connect Client (Mobile Side)
  Future<bool> connectToTv(String ip, String pin) async {
    try {
      _clientSocket = await WebSocket.connect('ws://$ip:8080').timeout(const Duration(seconds: 3));
      
      Completer<bool> authCompleter = Completer<bool>();

      _clientSocket!.listen(
        (message) {
          try {
            final data = jsonDecode(message);
            if (data['status'] == 'auth_success') {
              if (!authCompleter.isCompleted) authCompleter.complete(true);
              notifyListeners();
            } else if (data['status'] == 'auth_failed') {
              if (!authCompleter.isCompleted) authCompleter.complete(false);
              _clientSocket?.close();
              _clientSocket = null;
            }
          } catch (e) {
            debugPrint("Error parsing TV response: $e");
          }
        },
        onDone: () {
          _clientSocket = null;
          if (!authCompleter.isCompleted) authCompleter.complete(false);
          notifyListeners();
        },
        onError: (e) {
          _clientSocket = null;
          if (!authCompleter.isCompleted) authCompleter.complete(false);
          notifyListeners();
        },
      );

      // Send auth pin
      _clientSocket!.add(jsonEncode({
        "action": "auth",
        "pin": pin
      }));

      return await authCompleter.future.timeout(const Duration(seconds: 2), onTimeout: () => false);
    } catch (e) {
      debugPrint("Failed to connect to TV: $e");
      return false;
    }
  }

  // UDP Discovery
  Future<List<Map<String, String>>> discoverTvs() async {
    List<Map<String, String>> discovered = [];
    RawDatagramSocket? socket;
    try {
      socket = await RawDatagramSocket.bind(InternetAddress.anyIPv4, 0);
      socket.broadcastEnabled = true;

      socket.listen((RawSocketEvent event) {
        if (event == RawSocketEvent.read) {
          Datagram? dg = socket!.receive();
          if (dg != null) {
            String msg = utf8.decode(dg.data);
            if (msg.startsWith("PHIMTOP1_TV:")) {
              List<String> parts = msg.split(":");
              if (parts.length >= 3) {
                // To prevent duplicates from multiple responses
                if (!discovered.any((element) => element['ip'] == parts[1])) {
                  discovered.add({"ip": parts[1], "name": parts[2]});
                }
              }
            }
          }
        }
      });

      // Send broadcast
      socket.send(utf8.encode("DISCOVER_PHIMTOP1_TV"), InternetAddress("255.255.255.255"), 8081);
      
      // Wait for responses
      await Future.delayed(const Duration(seconds: 2));
    } catch (e) {
      debugPrint("Discovery error: $e");
    } finally {
      socket?.close();
    }
    return discovered;
  }

  void disconnectFromTv() {
    _clientSocket?.close();
    _clientSocket = null;
    notifyListeners();
  }

  void castToTv(String movieSlug) {
    if (isClientConnected) {
      final msg = jsonEncode({
        "action": "play",
        "slug": movieSlug,
      });
      _clientSocket!.add(msg);
    }
  }

  void castDirect(String m3u8Link, String title) {
    if (isClientConnected) {
      final msg = jsonEncode({
        "action": "play_direct",
        "m3u8Link": m3u8Link,
        "title": title,
      });
      _clientSocket!.add(msg);
    }
  }

  void sendPlayerControl(String command, {dynamic value}) {
    if (isClientConnected) {
      final msg = jsonEncode({
        "action": "player_control",
        "command": command,
        "value": value,
      });
      _clientSocket!.add(msg);
    }
  }
}
