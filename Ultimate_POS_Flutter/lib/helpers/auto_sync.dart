import 'dart:async';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../models/sellDatabase.dart';
import '../models/sell.dart';
import '../models/variations.dart';

class AutoSyncManager {
  static final AutoSyncManager _instance = AutoSyncManager._internal();
  factory AutoSyncManager() => _instance;
  AutoSyncManager._internal();

  StreamSubscription<ConnectivityResult>? _subscription;
  bool _isSyncing = false;

  void initialize() {
    _subscription = Connectivity().onConnectivityChanged.listen((ConnectivityResult result) {
      if (result == ConnectivityResult.wifi || result == ConnectivityResult.mobile) {
        _triggerAutoSync();
      }
    });

    // Also attempt an initial sync on startup if internet is available
    _checkInitialSync();
  }

  void dispose() {
    _subscription?.cancel();
  }

  Future<void> _checkInitialSync() async {
    var connectivityResult = await Connectivity().checkConnectivity();
    if (connectivityResult == ConnectivityResult.wifi || connectivityResult == ConnectivityResult.mobile) {
      _triggerAutoSync();
    }
  }

  Future<void> _triggerAutoSync() async {
    if (_isSyncing) return;

    final notSynced = await SellDatabase().getNotSyncedSells();
    if (notSynced.isEmpty) return;

    try {
      _isSyncing = true;
      // Call Sell API to push local queued entries
      await Sell().createApiSell(syncAll: true);
      // Wait for variations refresh if necessary
      await Variations().refresh();
    } catch (e) {
      print("AutoSync Manager Exception: $e");
    } finally {
      _isSyncing = false;
    }
  }
}