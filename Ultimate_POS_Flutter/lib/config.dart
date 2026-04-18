import 'dart:ui';
//import 'package:google_maps_flutter/google_maps_flutter.dart';

class Config {
  static const String serverOrigin = 'https://sector71.app';
  static const String baseUrl = serverOrigin + '/public/';
  static const String connectorApiUrl = baseUrl + 'connector/api/';
  static int? userId;
  static String appName = 'Sector71 Software';
  static bool syncCallLog = false, showRegister = false, showFieldForce = false;
  static bool requireManagerPinForSensitiveActions = false;
  static String? managerPin;
  String clientId = '21',
      clientSecret = 'psiZvPfu3Q6YsS0FMIRpb9DJPZT6MG0LtA4teXbU',
      copyright = '\u00a9',
      version = 'V 1.8',
      splashScreen = '${Config.baseUrl}uploads/mobile/welcome.jpg',
      loginScreen = '${Config.baseUrl}uploads/mobile/login.jpg',
      noDataImage = '${Config.baseUrl}uploads/mobile/no_data.jpg',
      defaultBusinessImage = '${Config.baseUrl}uploads/business_default.jpg';

  //quantity precision       //currency precision   //call_log sync duration
  static int quantityPrecision = 2,
      currencyPrecision = 2,
      callLogSyncDuration = 10;

  //List of locale language code
  List locale = ['en', 'ar', 'de', 'fr', 'es', 'tr', 'id', 'my'];
  String defaultLanguage = 'en';

  //List of locales included
  List<Locale> supportedLocales = [
    Locale('en', 'US'),
    Locale('ar', ''),
    Locale('de', ''),
    Locale('fr', ''),
    Locale('es', ''),
    Locale('tr', ''),
    Locale('id', ''),
    Locale('my', '')
  ];

  //dropdown items for changing language
  List<Map<String, dynamic>> lang = [
    {'languageCode': 'en', 'countryCode': 'US', 'name': 'English'},
    {'languageCode': 'ar', 'countryCode': '', 'name': 'العربي'},
    {'languageCode': 'de', 'countryCode': '', 'name': 'Deutsche'},
    {'languageCode': 'fr', 'countryCode': '', 'name': 'Français'},
    {'languageCode': 'es', 'countryCode': '', 'name': 'Española'},
    {'languageCode': 'tr', 'countryCode': '', 'name': 'Türkçe'},
    {'languageCode': 'id', 'countryCode': '', 'name': 'Indonesian'},
    {'languageCode': 'my', 'countryCode': '', 'name': 'မြန်မာ'}
  ];

  //final initialPosition = LatLng(20.46752985010792, 82.92005813910752);
  final String googleAPIKey = 'YOUR_GOOGLE_API_KEY';
}
