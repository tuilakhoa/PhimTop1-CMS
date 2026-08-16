import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:go_router/go_router.dart';

class AppLockScreen extends StatefulWidget {
  const AppLockScreen({super.key});
  @override
  State<AppLockScreen> createState() => _AppLockScreenState();
}

class _AppLockScreenState extends State<AppLockScreen> {
  final TextEditingController _controller = TextEditingController();
  String? _error;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.lock, size: 64, color: Colors.white54),
              const SizedBox(height: 24),
              const Text("Ứng dụng đã bị khóa", style: TextStyle(color: Colors.white, fontSize: 20)),
              const SizedBox(height: 24),
              TextField(
                controller: _controller,
                obscureText: true,
                keyboardType: TextInputType.number,
                maxLength: 4,
                autofocus: true,
                textAlign: TextAlign.center,
                style: const TextStyle(color: Colors.white, fontSize: 24, letterSpacing: 16),
                decoration: InputDecoration(
                  counterText: "",
                  errorText: _error,
                  enabledBorder: const UnderlineInputBorder(borderSide: BorderSide(color: Colors.white54)),
                  focusedBorder: const UnderlineInputBorder(borderSide: BorderSide(color: Colors.blueAccent)),
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
