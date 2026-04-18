import 'dart:convert';

import '../config.dart';
import '../apis/system.dart';
import '../models/contact_model.dart';
import 'database.dart';

class System {
  late DbProvider dbProvider;

  System() {
    dbProvider = new DbProvider();
  }

  //store system data
  Future<int> insert(key, value, [keyId]) async {
    final db = await dbProvider.database;
    var data = {'key': '$key', 'keyId': keyId, 'value': value};
    var response = await db.insert('system', data);
    return response;
  }

//save user details
  Future<int> insertUserDetails(Map userDetails) async {
    final db = await dbProvider.database;
    var data = {'key': 'loggedInUser', 'value': jsonEncode(userDetails)};
    var response = await db.insert('system', data);
    return response;
  }

  //store token
  Future<int> insertToken(String token) async {
    final db = await dbProvider.database;
    var data = {'key': 'token', 'value': token};
    var response = await db.insert('system', data);
    return response;
  }

  insertProductLastSyncDateTimeNow() async {
    //if already present then update, else insert new
    final db = await dbProvider.database;
    String? lastSync = await this.getProductLastSync();

    if (lastSync == null) {
      var data = {
        'key': 'product_last_sync',
        'value': DateTime.now().toString()
      };
      await db.insert('system', data);
    } else {
      await db.update('system', {'value': DateTime.now().toString()},
          where: 'key = ?', whereArgs: ['product_last_sync']);
    }
  }

  // insert/update/get call_log_last_sync time
  callLogLastSyncDateTime([bool? insert]) async {
    //if already present then update, else insert new
    final db = await dbProvider.database;
    var lastSyncDetail = await db
        .query('system', where: 'key = ?', whereArgs: ['call_logs_last_sync']);
    var lastSync =
        (lastSyncDetail.isNotEmpty) ? lastSyncDetail[0]['value'] : null;

    if (insert == true && lastSync == null) {
      await db.insert('system',
          {'key': 'call_logs_last_sync', 'value': DateTime.now().toString()});
      return lastSync;
    } else if (insert == true) {
      db.update('system', {'value': DateTime.now().toString()},
          where: 'key = ?', whereArgs: ['call_logs_last_sync']);
      return lastSync;
    } else {
      return lastSync;
    }
  }

  Future<dynamic> getProductLastSync() async {
    final db = await dbProvider.database;
    var result = await db
        .query('system', where: 'key = ?', whereArgs: ['product_last_sync']);
    var response = (result.length > 0) ? result[0]['value'] : null;
    return response;
  }

  //fetch token
  Future<String> getToken() async {
    final db = await dbProvider.database;
    var result =
        await db.query('system', where: 'key = ?', whereArgs: ['token']);
    String? token = result[0]['value'].toString();
    return token;
  }

  // Return permission list
  Future<List> getPermission() async {
    var result = await this.get('loggedInUser');
    if (result.containsKey('is_admin') && result['is_admin'] == true) {
      return ['all'];
    } else {
      List permissions = await this.get('user_permissions');
      return permissions;
    }
  }

  //Return the list of categories.
  Future<List> getCategories() async {
    var categories = await this.get('taxonomy');

    if (categories.length > 0) {
      return categories;
    } else {
      return [];
    }
  }

  //Return the list of sub categories.
  Future<List> getSubCategories(parentId) async {
    final db = await dbProvider.database;
    String where = 'and keyId = $parentId';
    var subCategories = await db.query('system',
        where: 'key = ? $where', whereArgs: ['sub_categories']);
    if (subCategories.length > 0) {
      return subCategories;
    } else {
      return [];
    }
  }

  //Return the list of brands.
  Future<List> getBrands() async {
    var brands = await this.get('brand');
    if (brands.length > 0) {
      return brands;
    } else {
      return [];
    }
  }

  storePermissions() async {
    final db = await dbProvider.database;
    var result = await this.get('loggedInUser');
    if (result.containsKey('all_permissions')) {
      var userData = {
        'key': 'user_permissions',
        'value': jsonEncode(result['all_permissions'])
      };
      await db.insert('system', userData);
    }
  }

  //Return the list of payment accounts.
  Future<List> getPaymentAccounts() async {
    var accounts = await this.get('payment_accounts');
    if (accounts.length > 0) {
      return accounts;
    } else {
      return [];
    }
  }

  //get brandList,categoryList,taxRateList,locationList,permissionList
  Future<dynamic> get(key, [keyId]) async {
    final db = await dbProvider.database;
    String where = '';
    if (keyId != null) {
      where = 'and keyId = $keyId';
    }
    List<Map<String, dynamic>> result =
        await db.query('system', where: 'key = ? $where', whereArgs: ['$key']);
    var response = (result.length > 0) ? jsonDecode(result[0]['value']) : [];
    return response;
  }

  //empty system table
  Future<int> empty() async {
    final db = await dbProvider.database;
    var response = await db.delete('system');
    return response;
  }

  //delete column from system table
  Future<int> delete(colName) async {
    final db = await dbProvider.database;
    var response =
        await db.delete('system', where: 'key = ?', whereArgs: ['$colName']);
    return response;
  }

  refreshPermissionList() async {
    final db = await dbProvider.database;
    await db
        .delete('system', where: 'key = ?', whereArgs: ['user_permissions']);
    await Permissions().get();
  }

  //delete column from system table
  refresh() async {
    final db = await dbProvider.database;
    List colNames = [
      'business',
      'user_permissions',
      'active-subscription',
      'payment_methods',
      'payment_method',
      'location',
      'tax',
      'brand',
      'taxonomy',
      'sub_categories',
      'payment_accounts'
    ];
    Contact().emptyContact();
    colNames.forEach((element) async {
      await db.delete('system', where: 'key = ?', whereArgs: ['$element']);
    });
    await SystemApi().store();
    await applyWebsiteFeatureSettings();
  }

  //sync feature behavior from sector71.app data (business, subscription, permissions)
  Future<void> applyWebsiteFeatureSettings() async {
    try {
      final business = await get('business');
      final activeSubscription = await get('active-subscription');
      final permissions = await getPermission();

      // App branding from website business profile.
      if (business is List && business.isNotEmpty && business[0] is Map) {
        final Map businessDetails = business[0];
        final websiteBusinessName = businessDetails['name']?.toString();
        if (websiteBusinessName != null &&
            websiteBusinessName.trim().isNotEmpty) {
          Config.appName = websiteBusinessName;
        }

        // Optional registration visibility keys if provided by backend.
        Config.showRegister = _isTruthy(businessDetails['show_register']) ||
            _isTruthy(businessDetails['allow_registration']) ||
            _isTruthy(businessDetails['enable_registration']);
      }

      Map<dynamic, dynamic> packageDetails = {};
      if (activeSubscription is List &&
          activeSubscription.isNotEmpty &&
          activeSubscription[0] is Map &&
          activeSubscription[0]['package_details'] is Map) {
        packageDetails = activeSubscription[0]['package_details'];
      }

        // Manager authorization flags synced from website-side settings when available.
        final Map businessDetails =
          (business is List && business.isNotEmpty && business[0] is Map)
            ? business[0]
            : {};
        final managerPin = (businessDetails['mobile_manager_pin'] ??
            businessDetails['manager_pin'] ??
            businessDetails['pos_manager_pin'])
          ?.toString()
          .trim();
        final requireManagerPin =
          _isTruthy(businessDetails['require_manager_pin']) ||
            _isTruthy(packageDetails['require_manager_pin']) ||
            _isTruthy(packageDetails['manager_pin_required']);
        final hasManagerPin = managerPin != null && managerPin.isNotEmpty;
        Config.managerPin = hasManagerPin ? managerPin : null;
        Config.requireManagerPinForSensitiveActions =
          requireManagerPin || hasManagerPin;

      // Derive features from package modules and user permissions.
      final hasAllPermission = permissions.contains('all');
      final fieldForcePermission = hasAllPermission ||
          permissions.contains('crm.access_all_schedule') ||
          permissions.contains('crm.access_own_schedule') ||
          permissions.contains('essentials.crm_module') ||
          permissions.contains('field_force.access');

      final callLogPermission = hasAllPermission ||
          permissions.contains('crm.access_all_schedule') ||
          permissions.contains('crm.access_own_schedule') ||
          permissions.contains('essentials.crm_module');

      final packageFieldForce =
          _isTruthy(packageDetails['field_force_module']) ||
              _isTruthy(packageDetails['crm_module']);
      final packageCallLog = _isTruthy(packageDetails['call_log_module']) ||
          _isTruthy(packageDetails['crm_module']) ||
          _isTruthy(packageDetails['essentials_module']);

      Config.showFieldForce = fieldForcePermission && packageFieldForce;
      Config.syncCallLog = callLogPermission && packageCallLog;
    } catch (_) {
      // Keep safe defaults if website flags are not available yet.
    }
  }

  bool _isTruthy(dynamic value) {
    if (value == null) return false;
    if (value is bool) return value;
    final text = value.toString().trim().toLowerCase();
    return text == '1' || text == 'true' || text == 'yes';
  }
}
