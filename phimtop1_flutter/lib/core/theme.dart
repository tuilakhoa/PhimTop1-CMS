import 'package:flutter/material.dart';

class AppTheme {
  static const Color defaultPrimaryColor = Color(0xFFE50914);
  
  static ThemeData getTheme({required Brightness brightness, required String skin}) {
    Color primaryColor = defaultPrimaryColor;
    
    if (skin == 'vip') {
      primaryColor = const Color(0xFFD4AF37); // Gold
    } else if (skin == 'futingyun') {
      primaryColor = const Color(0xFFE06B5F);
    } else if (skin == 'zhaoling') {
      primaryColor = const Color(0xFF6B4226);
    }

    if (brightness == Brightness.dark) {
      const Color backgroundColor = Color(0xFF0F0F0F);
      const Color surfaceColor = Color(0xFF1E1E1E);
      
      return ThemeData(
        brightness: Brightness.dark,
        primaryColor: primaryColor,
        scaffoldBackgroundColor: backgroundColor,
        colorScheme: ColorScheme.dark(
          primary: primaryColor,
          surface: surfaceColor,
          background: backgroundColor,
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: backgroundColor,
          elevation: 0,
          centerTitle: false,
        ),
        bottomNavigationBarTheme: BottomNavigationBarThemeData(
          backgroundColor: backgroundColor,
          selectedItemColor: primaryColor,
          unselectedItemColor: Colors.grey,
          type: BottomNavigationBarType.fixed,
        ),
      );
    } else {
      const Color backgroundColor = Color(0xFFF5F5F5);
      const Color surfaceColor = Color(0xFFFFFFFF);
      
      return ThemeData(
        brightness: Brightness.light,
        primaryColor: primaryColor,
        scaffoldBackgroundColor: backgroundColor,
        colorScheme: ColorScheme.light(
          primary: primaryColor,
          surface: surfaceColor,
          background: backgroundColor,
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: surfaceColor,
          elevation: 0,
          centerTitle: false,
          foregroundColor: Colors.black,
        ),
        bottomNavigationBarTheme: BottomNavigationBarThemeData(
          backgroundColor: surfaceColor,
          selectedItemColor: primaryColor,
          unselectedItemColor: Colors.grey,
          type: BottomNavigationBarType.fixed,
        ),
      );
    }
  }

  static ThemeData get darkTheme => getTheme(brightness: Brightness.dark, skin: 'default');
}
