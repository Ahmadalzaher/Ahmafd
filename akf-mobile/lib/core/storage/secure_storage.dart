import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorage {
  SecureStorage({FlutterSecureStorage? storage})
      : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;
  static const _tokenKey = 'akf_access_token';
  static const _refreshKey = 'akf_refresh_token';
  static const _deviceKey = 'akf_device_id';

  Future<void> saveToken(String token) => _storage.write(key: _tokenKey, value: token);
  Future<String?> readToken() => _storage.read(key: _tokenKey);

  Future<void> saveRefresh(String token) => _storage.write(key: _refreshKey, value: token);
  Future<String?> readRefresh() => _storage.read(key: _refreshKey);

  Future<void> saveDevice(String id) => _storage.write(key: _deviceKey, value: id);
  Future<String?> readDevice() => _storage.read(key: _deviceKey);

  Future<void> clear() => _storage.deleteAll();
}
