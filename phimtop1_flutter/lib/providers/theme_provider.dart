import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeProvider with ChangeNotifier {
  ThemeMode _themeMode = ThemeMode.system;
  bool _useSystem = true;
  String _currentSkin = 'default';
  bool _useSystemAccent = false;

  ThemeProvider() {
    _loadTheme();
  }

  ThemeMode get themeMode => _themeMode;
  bool get useSystem => _useSystem;
  String get currentSkin => _currentSkin;
  bool get useSystemAccent => _useSystemAccent;

  Future<void> _loadTheme() async {
    final prefs = await SharedPreferences.getInstance();
    _useSystem = prefs.getBool('theme_use_system') ?? false;
    _useSystemAccent = prefs.getBool('theme_use_system_accent') ?? false;
    final themeModeStr = prefs.getString('theme_mode') ?? 'dark';
    _currentSkin = prefs.getString('theme_skin') ?? 'default';

    if (_useSystem) {
      _themeMode = ThemeMode.system;
    } else {
      _themeMode = themeModeStr == 'light' ? ThemeMode.light : ThemeMode.dark;
    }
    notifyListeners();
  }

  Future<void> setUseSystem(bool value) async {
    _useSystem = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('theme_use_system', value);
    
    if (_useSystem) {
      _themeMode = ThemeMode.system;
    } else {
      final themeModeStr = prefs.getString('theme_mode') ?? 'dark';
      _themeMode = themeModeStr == 'light' ? ThemeMode.light : ThemeMode.dark;
    }
    notifyListeners();
  }

  Future<void> setUseSystemAccent(bool value) async {
    _useSystemAccent = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('theme_use_system_accent', value);
    notifyListeners();
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    if (_useSystem) return;

    _themeMode = mode;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('theme_mode', mode == ThemeMode.light ? 'light' : 'dark');
    notifyListeners();
  }

  Future<void> setSkin(String skin) async {
    _currentSkin = skin;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('theme_skin', skin);
    notifyListeners();
  }
}
