import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:async';

class WatchPartyProvider extends ChangeNotifier {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  
  String? currentPartyId;
  Map<String, dynamic>? partyData;
  StreamSubscription? _partySubscription;

  bool get isInParty => currentPartyId != null;
  bool get isHost => partyData != null && partyData!['host_id'] == _userId;

  String _userId = '';
  String _userName = 'Khách';
  
  void initUser(String uid, String name) {
    _userId = uid;
    _userName = name;
  }

  Future<String> createParty(String movieSlug, String episodeSlug, String movieName) async {
    final code = DateTime.now().millisecondsSinceEpoch.toRadixString(36).substring(0, 5).toUpperCase();
    
    await _firestore.collection('watch_parties').doc(code).set({
      'host_id': _userId,
      'movie_slug': movieSlug,
      'episode_slug': episodeSlug,
      'movie_name': movieName,
      'state': 'paused',
      'position': 0,
      'updated_at': FieldValue.serverTimestamp(),
      'members': [{
        'uid': _userId,
        'name': _userName,
      }],
      'chat': [],
    });

    await joinParty(code);
    return code;
  }

  Future<bool> joinParty(String code) async {
    final docRef = _firestore.collection('watch_parties').doc(code);
    final doc = await docRef.get();
    if (!doc.exists) return false;

    currentPartyId = code;
    
    await docRef.update({
      'members': FieldValue.arrayUnion([{
        'uid': _userId,
        'name': _userName,
      }])
    });

    _partySubscription = docRef.snapshots().listen((snapshot) {
      if (snapshot.exists) {
        partyData = snapshot.data();
        notifyListeners();
      }
    });

    return true;
  }

  Future<void> leaveParty() async {
    if (currentPartyId == null) return;
    
    final docRef = _firestore.collection('watch_parties').doc(currentPartyId);
    await docRef.update({
      'members': FieldValue.arrayRemove([{
        'uid': _userId,
        'name': _userName,
      }])
    });

    _partySubscription?.cancel();
    currentPartyId = null;
    partyData = null;
    notifyListeners();
  }

  Future<void> syncVideoState(String state, int positionSeconds) async {
    if (currentPartyId == null || !isHost) return;
    
    await _firestore.collection('watch_parties').doc(currentPartyId).update({
      'state': state,
      'position': positionSeconds,
      'updated_at': FieldValue.serverTimestamp(),
    });
  }

  Future<void> sendMessage(String text) async {
    if (currentPartyId == null) return;
    
    final msg = {
      'uid': _userId,
      'name': _userName,
      'text': text,
      'time': DateTime.now().millisecondsSinceEpoch,
    };
    
    await _firestore.collection('watch_parties').doc(currentPartyId).update({
      'chat': FieldValue.arrayUnion([msg])
    });
  }
}
