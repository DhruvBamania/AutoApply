import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class StorageService {
  static const _storage = FlutterSecureStorage();

  static const String _tokenKey = 'auth_token';
  static const String _roleKey = 'user_role';
  static const String _userKey = 'user_data';

  // In-memory cache for instant (0ms) access across all requests
  static String? _cachedToken;
  static String? _cachedRole;
  static String? _cachedUserData;
  static bool _isInitialized = false;

  /// Warm up cache on application launch
  static Future<void> init() async {
    if (_isInitialized) return;
    try {
      _cachedToken = await _storage.read(key: _tokenKey);
      _cachedRole = await _storage.read(key: _roleKey);
      _cachedUserData = await _storage.read(key: _userKey);
      _isInitialized = true;
    } catch (_) {}
  }

  /// Save token, role, and profile info upon successful authentication
  static Future<void> saveAuthData({
    required String token,
    required String role,
    String? userDataJson,
  }) async {
    _cachedToken = token;
    _cachedRole = role;
    _cachedUserData = userDataJson;
    _isInitialized = true;

    await Future.wait([
      _storage.write(key: _tokenKey, value: token),
      _storage.write(key: _roleKey, value: role),
      if (userDataJson != null) _storage.write(key: _userKey, value: userDataJson),
    ]);
  }

  /// Fast token retrieval with in-memory fallback
  static Future<String?> getToken() async {
    if (_cachedToken != null) return _cachedToken;
    _cachedToken = await _storage.read(key: _tokenKey);
    return _cachedToken;
  }

  /// Instant synchronous token accessor
  static String? get cachedToken => _cachedToken;

  /// Retrieve the cached user role ('user' or 'company')
  static Future<String?> getRole() async {
    if (_cachedRole != null) return _cachedRole;
    _cachedRole = await _storage.read(key: _roleKey);
    return _cachedRole;
  }

  static String? get cachedRole => _cachedRole;

  /// Retrieve cached user JSON
  static Future<String?> getUserData() async {
    if (_cachedUserData != null) return _cachedUserData;
    _cachedUserData = await _storage.read(key: _userKey);
    return _cachedUserData;
  }

  static String? get cachedUserData => _cachedUserData;

  /// Check if user has an active session token
  static Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  /// Wipe credentials on logout
  static Future<void> clearAuth() async {
    _cachedToken = null;
    _cachedRole = null;
    _cachedUserData = null;
    await Future.wait([
      _storage.delete(key: _tokenKey),
      _storage.delete(key: _roleKey),
      _storage.delete(key: _userKey),
    ]);
  }
}
