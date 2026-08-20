import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/foundation.dart';
import 'package:google_sign_in/google_sign_in.dart';

class AuthService {
  final FirebaseAuth _auth = FirebaseAuth.instance;

  AuthService() {
    if (!kIsWeb) {
      GoogleSignIn.instance.initialize();
    }
  }

  // Stream to listen to auth state changes
  Stream<User?> get authStateChanges => _auth.authStateChanges();

  // Get current user
  User? get currentUser => _auth.currentUser;

  // Google Sign-In
  Future<UserCredential?> signInWithGoogle() async {
    try {
      if (kIsWeb) {
        // Web uses popup to avoid DWDS hangs and manual client ID config
        GoogleAuthProvider authProvider = GoogleAuthProvider();
        return await _auth.signInWithPopup(authProvider);
      } else {
        // Mobile uses standard flow
        final GoogleSignInAccount? googleUser = await GoogleSignIn.instance.authenticate();
        if (googleUser == null) return null; // Cancelled

        final GoogleSignInAuthentication googleAuth = await googleUser.authentication;
        
        final AuthCredential credential = GoogleAuthProvider.credential(
          idToken: googleAuth.idToken,
          accessToken: googleAuth.accessToken,
        );
        
        return await _auth.signInWithCredential(credential);
      }
    } catch (e) {
      print("Error during Google Sign-In: $e");
      return null;
    }
  }

  // Sign out
  Future<void> signOut() async {
    try {
      if (!kIsWeb) {
        await GoogleSignIn.instance.signOut();
      }
      await _auth.signOut();
    } catch (e) {
      print("Error signing out: $e");
    }
  }
}
