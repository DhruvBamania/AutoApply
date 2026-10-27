import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../core/services/storage_service.dart';
import '../models/user_model.dart';

/// ---------------------------------------------------------------------------
/// WHAT IS A RIVERPOD PROVIDER?
/// In Flutter, a Provider is like a "live data broadcast tower".
/// When the data inside it changes (e.g. logging in, logging out, loading),
/// any screen listening to this provider automatically redraws itself!
/// ---------------------------------------------------------------------------

class AuthState {
  final bool isLoading;
  final UserModel? user;
  final String? errorMessage;
  final bool isInitialCheckDone;

  const AuthState({
    this.isLoading = false,
    this.user,
    this.errorMessage,
    this.isInitialCheckDone = false,
  });

  bool get isAuthenticated => user != null;
  bool get isCompany => user?.isCompany ?? false;
  bool get isCandidate => user?.isCandidate ?? false;

  AuthState copyWith({
    bool? isLoading,
    UserModel? user,
    String? errorMessage,
    bool? isInitialCheckDone,
    bool clearUser = false,
  }) {
    return AuthState(
      isLoading: isLoading ?? this.isLoading,
      user: clearUser ? null : (user ?? this.user),
      errorMessage: errorMessage,
      isInitialCheckDone: isInitialCheckDone ?? this.isInitialCheckDone,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier() : super(const AuthState()) {
    checkAuthStatus();
  }

  final _api = ApiClient();

  /// 1. Check if user already has a saved token on app start
  Future<void> checkAuthStatus() async {
    final token = StorageService.cachedToken;
    final cachedUserJson = StorageService.cachedUserData;

    // Fast-path: Instant 0ms session restoration from memory cache
    if (token != null && token.isNotEmpty && cachedUserJson != null) {
      try {
        final Map<String, dynamic> userMap = jsonDecode(cachedUserJson);
        final user = UserModel.fromJson(userMap);
        state = state.copyWith(
          user: user,
          isLoading: false,
          isInitialCheckDone: true,
        );
      } catch (_) {
        state = state.copyWith(isLoading: true);
      }
    } else {
      state = state.copyWith(isLoading: true);
    }

    try {
      final activeToken = await StorageService.getToken();
      if (activeToken == null || activeToken.isEmpty) {
        state = state.copyWith(isLoading: false, isInitialCheckDone: true, clearUser: true);
        return;
      }

      // Revalidate session silently in background with Laravel /auth/me
      final response = await _api.dio.get(ApiEndpoints.me);
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final userData = response.data['user'] ?? response.data['data']?['user'];
        if (userData != null) {
          final user = UserModel.fromJson(Map<String, dynamic>.from(userData));
          state = state.copyWith(
            user: user,
            isLoading: false,
            isInitialCheckDone: true,
          );
          return;
        }
      }
      // Only clear if server explicitly rejected auth
      if (response.statusCode == 401 || response.statusCode == 403) {
        await StorageService.clearAuth();
        state = state.copyWith(isLoading: false, isInitialCheckDone: true, clearUser: true);
      } else {
        state = state.copyWith(isLoading: false, isInitialCheckDone: true);
      }
    } catch (e) {
      // If network is offline, keep cached user so offline UX is smooth
      state = state.copyWith(isLoading: false, isInitialCheckDone: true);
    }
  }

  /// 2. Login with email & password
  Future<bool> login(String email, String password) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final response = await _api.dio.post(
        ApiEndpoints.login,
        data: {'email': email, 'password': password},
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (!isSuccess) {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Login failed',
        );
        return false;
      }

      // Support both root-level and nested 'data' structures
      final token = (response.data['token'] ?? response.data['data']?['token'])?.toString();
      final userData = response.data['user'] ?? response.data['data']?['user'];

      if (token == null || userData == null) {
        state = state.copyWith(
          isLoading: false,
          errorMessage: 'Invalid server response. Missing token or user profile.',
        );
        return false;
      }

      final user = UserModel.fromJson(Map<String, dynamic>.from(userData));

      // Persist token & role securely on device
      await StorageService.saveAuthData(
        token: token,
        role: user.role,
        userDataJson: jsonEncode(user.toJson()),
      );

      state = state.copyWith(user: user, isLoading: false);
      return true;
    } catch (e) {
      final errorMsg = ApiClient.formatError(e);
      state = state.copyWith(isLoading: false, errorMessage: errorMsg);
      return false;
    }
  }

  /// 3. Register a new Candidate or Company account
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String role, // 'user' or 'company'
    String? companyName,
    String? companyWebsite,
  }) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final payload = {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
        'role': role,
        if (role == 'company') 'company_name': companyName,
        if (role == 'company' && companyWebsite != null) 'website': companyWebsite,
      };

      final response = await _api.dio.post(ApiEndpoints.register, data: payload);
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 201;

      if (!isSuccess) {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Registration failed',
        );
        return false;
      }

      final token = (response.data['token'] ?? response.data['data']?['token'])?.toString();
      final userData = response.data['user'] ?? response.data['data']?['user'];

      if (token == null || userData == null) {
        state = state.copyWith(
          isLoading: false,
          errorMessage: 'Invalid response from server.',
        );
        return false;
      }

      final user = UserModel.fromJson(Map<String, dynamic>.from(userData));

      await StorageService.saveAuthData(
        token: token,
        role: user.role,
        userDataJson: jsonEncode(user.toJson()),
      );

      state = state.copyWith(user: user, isLoading: false);
      return true;
    } catch (e) {
      final errorMsg = ApiClient.formatError(e);
      state = state.copyWith(isLoading: false, errorMessage: errorMsg);
      return false;
    }
  }

  /// 4. Logout and purge session
  Future<void> logout() async {
    state = state.copyWith(isLoading: true);
    try {
      await _api.dio.post(ApiEndpoints.logout);
    } catch (_) {
      // Ignore network errors on logout
    } finally {
      await StorageService.clearAuth();
      state = const AuthState(isInitialCheckDone: true);
    }
  }

  /// 5. Update Company WhatsApp contact number
  Future<bool> updateCompanyWhatsApp(String? number) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final response = await _api.dio.put(
        ApiEndpoints.updateCompanyWhatsApp,
        data: {'whatsapp_number': number},
      );

      final isSuccess = response.data['success'] == true || response.statusCode == 200;
      if (isSuccess && state.user != null) {
        final updatedUser = state.user!.copyWith(whatsappNumber: number);
        await StorageService.saveAuthData(
          token: StorageService.cachedToken ?? '',
          role: updatedUser.role,
          userDataJson: jsonEncode(updatedUser.toJson()),
        );
        state = state.copyWith(isLoading: false, user: updatedUser);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to update WhatsApp number',
        );
        return false;
      }
    } catch (e) {
      final errorMsg = ApiClient.formatError(e);
      state = state.copyWith(isLoading: false, errorMessage: errorMsg);
      return false;
    }
  }

  /// Clear any error message banner
  void clearError() {
    state = state.copyWith(errorMessage: null);
  }
}

/// The global provider instance that screens can watch or read
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier();
});
