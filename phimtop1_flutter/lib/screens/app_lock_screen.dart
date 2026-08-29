import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:go_router/go_router.dart';
import 'package:local_auth/local_auth.dart';

class AppLockScreen extends StatefulWidget {
  const AppLockScreen({super.key});
  @override
  State<AppLockScreen> createState() => _AppLockScreenState();
}

class _AppLockScreenState extends State<AppLockScreen> {
  final TextEditingController _controller = TextEditingController();
  final LocalAuthentication _auth = LocalAuthentication();
  String? _error;
  bool _canCheckBiometrics = false;

  bool _hasPin = false;

  @override
  void initState() {
    super.initState();
    _checkBiometrics();
    _checkPin();
  }

  Future<void> _checkPin() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _hasPin = prefs.getString('app_lock_pin') != null;
    });
  }

  Future<void> _checkBiometrics() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      if (prefs.getBool('app_lock_biometric') != true) return;
      
      final canCheck = await _auth.canCheckBiometrics;
      final isDeviceSupported = await _auth.isDeviceSupported();
      setState(() {
        _canCheckBiometrics = canCheck || isDeviceSupported;
      });
      if (_canCheckBiometrics) {
        _authenticateBiometric();
      }
    } catch (e) {
      // Ignore
    }
  }

  Future<void> _authenticateBiometric() async {
    try {
      String bioLabel = "Sinh trắc học";
      final biometrics = await _auth.getAvailableBiometrics();
      if (biometrics.contains(BiometricType.face)) {
        bioLabel = "Khuôn mặt";
      } else if (biometrics.contains(BiometricType.fingerprint)) {
        bioLabel = "Vân tay";
      }
      final authenticated = await _auth.authenticate(
        localizedReason: 'Xác thực $bioLabel để mở khóa ứng dụng',
      );
      if (authenticated && mounted) {
        context.go('/');
      }
    } catch (e) {
      // Ignore
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              GestureDetector(
                onTap: _canCheckBiometrics ? _authenticateBiometric : null,
                child: Icon(
                  _canCheckBiometrics ? Icons.fingerprint : Icons.lock, 
                  size: 64, 
                  color: _canCheckBiometrics ? Theme.of(context).primaryColor : Colors.white54
                ),
              ),
              const SizedBox(height: 24),
              const Text("Ứng dụng đã bị khóa", style: TextStyle(color: Colors.white, fontSize: 20)),
              if (_canCheckBiometrics)
                Padding(
                  padding: const EdgeInsets.only(top: 8.0),
                  child: TextButton.icon(
                    onPressed: _authenticateBiometric,
                    icon: const Icon(Icons.fingerprint),
                    label: const Text("Mở khóa bằng Sinh trắc học"),
                  ),
                ),
              const SizedBox(height: 24),
              if (_hasPin)
                TextField(
                  controller: _controller,
                  obscureText: true,
                  keyboardType: TextInputType.number,
                  maxLength: 4,
                  autofocus: !_canCheckBiometrics,
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.white, fontSize: 24, letterSpacing: 16),
                  decoration: InputDecoration(
                    counterText: "",
                    errorText: _error,
                    enabledBorder: const UnderlineInputBorder(borderSide: BorderSide(color: Colors.white54)),
                    focusedBorder: UnderlineInputBorder(borderSide: BorderSide(color: Theme.of(context).primaryColor)),
                  ),
                  onChanged: (val) async {
                    if (val.length == 4) {
                      final prefs = await SharedPreferences.getInstance();
                      final pin = prefs.getString('app_lock_pin');
                      if (val == pin) {
                         if (context.mounted) context.go('/');
                      } else {
                         setState(() => _error = "Mã PIN không đúng");
                         _controller.clear();
                      }
                    }
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }
}
