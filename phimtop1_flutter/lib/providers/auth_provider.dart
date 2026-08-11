import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api/cms_api.dart';
import '../models/models.dart';

class AuthProvider with ChangeNotifier {
  User? user;
  String? token;
  bool isLoading = false;
  String? error;

  AuthProvider() {
    _loadUser();
  }

  Future<void> _loadUser() async {
    final prefs = await SharedPreferences.getInstance();
    token = prefs.getString('token');
    final userId = prefs.getString('user_id');
    final userName = prefs.getString('user_name');
    final userEmail = prefs.getString('user_email');
    
    if (token != null && userId != null) {
      user = User.fromJson({
        'id': userId,
        'name': userName,
        'email': userEmail,
      });
      notifyListeners();
    }
  }

  Future<bool> login(String email, String password) async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final response = await cmsApi.login(email, password);
      if (response.status == 'success' && response.token != null && response.user != null) {
        token = response.token;
        user = response.user;
        
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', token!);
        await prefs.setString('user_id', user!.id);
        await prefs.setString('user_name', user!.name);
        await prefs.setString('user_email', user!.email);
        
        isLoading = false;
        notifyListeners();
        return true;
      } else {
        error = response.message ?? 'Đăng nhập thất bại';
      }
    } catch (e) {
      error = e.toString();
    }
    
    isLoading = false;
    notifyListeners();
    return false;
  }

  Future<bool> register(String name, String email, String password) async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final response = await cmsApi.register(name, email, password);
      if (response.status == 'success' && response.token != null && response.user != null) {
        token = response.token;
        user = response.user;
        
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', token!);
        await prefs.setString('user_id', user!.id);
        await prefs.setString('user_name', user!.name);
        await prefs.setString('user_email', user!.email);
        
        isLoading = false;
        notifyListeners();
        return true;
      } else {
        error = response.message ?? 'Đăng ký thất bại';
      }
    } catch (e) {
      error = e.toString();
    }
    
    isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    user = null;
    token = null;
    notifyListeners();
  }
}
