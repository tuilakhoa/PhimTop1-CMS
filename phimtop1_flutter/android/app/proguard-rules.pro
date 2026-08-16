# Flutter Wrapper
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.**  { *; }
-keep class io.flutter.util.**  { *; }
-keep class io.flutter.view.**  { *; }
-keep class io.flutter.**  { *; }
-keep class io.flutter.plugins.**  { *; }

# Keep WorkManager classes
-keep class androidx.work.** { *; }
-keep class * extends androidx.work.ListenableWorker { *; }

# Keep Room database classes (WorkManager uses Room)
-keep class * extends androidx.room.RoomDatabase { *; }
-keep class androidx.room.** { *; }
-keep class androidx.sqlite.** { *; }

# Keep App Startup provider
-keep class androidx.startup.InitializationProvider { *; }

# Ignore missing Play Core classes referenced by Flutter's deferred components
-dontwarn com.google.android.play.core.**
