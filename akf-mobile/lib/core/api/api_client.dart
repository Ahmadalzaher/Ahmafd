import 'dart:typed_data';

import 'package:dio/dio.dart';

import '../errors/api_exception.dart';

class ApiClient {
  ApiClient({Dio? dio, String? baseUrl})
      : _dio = dio ?? Dio(BaseOptions(
          baseUrl: baseUrl ?? 'https://arab-kulturfestival.de/wp-json/',
          connectTimeout: const Duration(seconds: 12),
          receiveTimeout: const Duration(seconds: 15),
          headers: {'Accept': 'application/json'},
        )) {
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        if (_token != null && _token!.isNotEmpty) {
          options.headers['X-AKF-Token'] = _token;
        }
        handler.next(options);
      },
      onError: (error, handler) async {
        final data = error.response?.data;
        String code = 'network_error';
        String message = 'Server unavailable';
        int status = error.response?.statusCode ?? 0;
        if (data is Map) {
          code = (data['code'] ?? code).toString();
          message = (data['message'] ?? message).toString();
        }
        if (status == 401 && refreshToken != null && error.requestOptions.extra['retried'] != true) {
          final fresh = await refreshToken!();
          if (fresh != null && fresh.isNotEmpty) {
            error.requestOptions.extra['retried'] = true;
            error.requestOptions.headers['X-AKF-Token'] = fresh;
            try {
              final response = await _dio.fetch<dynamic>(error.requestOptions);
              handler.resolve(response);
              return;
            } catch (_) {
              // fall through to the original error
            }
          }
        }
        handler.reject(DioException(
          requestOptions: error.requestOptions,
          error: ApiException(code, message, status: status),
          response: error.response,
        ));
      },
    ));
  }

  final Dio _dio;
  String? _token;
  Future<String?> Function()? refreshToken;

  String? get currentToken => _token;
  void setToken(String? token) => _token = token;

  Future<Map<String, dynamic>> post(String path, Map<String, dynamic> body) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(path, data: body);
      return Map<String, dynamic>.from(response.data ?? <String, dynamic>{});
    } on DioException catch (e) {
      throw _map(e);
    }
  }

  Future<Map<String, dynamic>> get(String path, {Map<String, dynamic>? query}) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(path, queryParameters: query);
      return Map<String, dynamic>.from(response.data ?? <String, dynamic>{});
    } on DioException catch (e) {
      throw _map(e);
    }
  }

  Future<Uint8List> getBytes(String path, {Map<String, dynamic>? query}) async {
    try {
      final response = await _dio.get<List<int>>(
        path,
        queryParameters: query,
        options: Options(responseType: ResponseType.bytes),
      );
      return Uint8List.fromList(response.data ?? <int>[]);
    } on DioException catch (e) {
      throw _map(e);
    }
  }

  /// Download a large binary with a generous timeout (APK updates).
  Future<Uint8List> downloadBytes(String path) async {
    try {
      final response = await _dio.get<List<int>>(
        path,
        options: Options(
          responseType: ResponseType.bytes,
          receiveTimeout: const Duration(minutes: 3),
          sendTimeout: const Duration(seconds: 30),
        ),
      );
      return Uint8List.fromList(response.data ?? <int>[]);
    } on DioException catch (e) {
      throw _map(e);
    }
  }

  ApiException _map(DioException e) {
    if (e.error is ApiException) {
      return e.error as ApiException;
    }
    final data = e.response?.data;
    if (data is Map) {
      return ApiException(
        (data['code'] ?? 'api_error').toString(),
        (data['message'] ?? 'Request failed').toString(),
        status: e.response?.statusCode ?? 0,
      );
    }
    return const ApiException('network_error', 'No connection to server');
  }
}
