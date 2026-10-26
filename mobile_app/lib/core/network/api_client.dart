import 'package:dio/dio.dart';
import '../constants/api_endpoints.dart';
import '../services/storage_service.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late final Dio dio;

  ApiClient._internal() {
    dio = Dio(
      BaseOptions(
        baseUrl: ApiEndpoints.baseUrl,
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 20),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Accept-Encoding': 'gzip, deflate',
        },
      ),
    );

    // Attach Interceptors: Automatically injects Bearer Token on every request
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Instant synchronous in-memory token lookup (0ms)
          final token = StorageService.cachedToken ?? await StorageService.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (DioException error, handler) async {
          if (error.response?.statusCode == 401) {
            // Token expired or invalid
            await StorageService.clearAuth();
          }
          return handler.next(error);
        },
      ),
    );
  }

  /// Helper to extract clean error message from Dio response
  static String formatError(dynamic error) {
    if (error is DioException) {
      if (error.response?.data is Map) {
        final data = error.response!.data as Map;
        if (data.containsKey('message')) return data['message'].toString();
        if (data.containsKey('error')) return data['error'].toString();
      }
      switch (error.type) {
        case DioExceptionType.connectionTimeout:
          return 'Connection timeout. Check your server connection.';
        case DioExceptionType.connectionError:
          return 'Unable to reach backend server. Ensure Laravel is running.';
        default:
          return error.message ?? 'An unexpected network error occurred.';
      }
    }
    return error.toString();
  }
}
