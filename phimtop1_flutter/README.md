# PhimTop1 Flutter App

The official mobile application for **PhimTop1 CMS**, built with Flutter.

## Architecture

This application is built using a monolithic but well-structured approach utilizing the `Provider` package for state management.
- **`lib/screens/`**: UI components and pages (Home, Watch Movie, Search, Settings).
- **`lib/services/`**: API interaction layers. For example, `watch_party_service.dart` handles the Co-Watching integration with the PhimTop1 backend.
- **`lib/providers/`**: Business logic and state management (e.g., `explore_provider.dart`).
- **`lib/widgets/`**: Reusable UI components (e.g., TV-optimized focusable wrappers).

## Features
- **Movie Browsing**: Homepage slider, latest updates, and search functionality.
- **Video Playback**: Native HLS/m3u8 streaming player.
- **Watch Party (Co-watching)**: Users can join a room to watch movies synchronously.
- **Android TV Support**: D-pad friendly UI navigation.

## Requirements
- **Flutter SDK**: `3.44.x` (or newer stable release)
- **Dart SDK**: Compatible with your Flutter version
- **Backend API**: PhimTop1 CMS running on a server with HTTPS.

## Setup Instructions

### 1. Install Dependencies
Run the following command in the project root:
```bash
flutter pub get
```

### 2. Configure API Endpoint
The application needs to know where your PhimTop1 CMS is hosted. 
Ensure you update the API base URL in the app's configuration (usually in `lib/utils/constants.dart` or a similar config file, depending on your setup).
Make sure to also provide the `X-App-API-Key` if configured in the CMS settings.

### 3. Run the Application
To run the app on an attached device or emulator:
```bash
flutter run
```

### 4. Build for Production

#### Android (APK / App Bundle)
To build an APK file for Android:
```bash
flutter build apk --release
```
For Google Play Store submission, build an App Bundle:
```bash
flutter build appbundle --release
```

#### iOS
To build the iOS application:
```bash
flutter build ios --release
```
*Note: iOS builds require a macOS environment with Xcode installed.*

## CI/CD Pipeline
This project includes GitHub Actions workflows for automated builds:
- **`.github/workflows/ios_build.yml`**: Automatically builds an unsigned `.ipa` payload on push to `main` for easy testing.
