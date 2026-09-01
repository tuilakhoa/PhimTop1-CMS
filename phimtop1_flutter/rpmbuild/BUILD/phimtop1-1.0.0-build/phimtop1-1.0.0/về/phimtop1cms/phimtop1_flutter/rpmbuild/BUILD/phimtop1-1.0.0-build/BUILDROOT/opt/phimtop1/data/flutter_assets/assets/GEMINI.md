# PhimTop1 Rules

- **CRITICAL APK SHA-1 INTEGRITY**: Whenever you are asked to delete logs, test files, or APK files during cleanup, you MUST NOT delete `app-mobile-release.apk.sha1`. This file contains the exact hash `271cc42b8aefba8d09e3a5fdec900f8e15c4d6d4`. 
- When rebuilding the application, ensure that you DO NOT overwrite, change, or remove this specific `app-mobile-release.apk.sha1` file.
- **KEYSTORE INTEGRITY**: Do NOT delete, overwrite, or modify any keystore files (e.g., `.jks` or `.keystore` files) associated with this project under any circumstances.
- **APK NAMING CONVENTION**: When building the app, name the APK as `<app_name>-<platform>.apk`. For example: `phimtop1-mobile.apk`.
