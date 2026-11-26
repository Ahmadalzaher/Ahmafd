import '../api/api_client.dart';
import '../models/models.dart';
import '../storage/secure_storage.dart';

class AuthRepository {
  AuthRepository(this._api, this._storage) {
    _api.refreshToken = _refreshAccessToken;
  }

  final ApiClient _api;
  final SecureStorage _storage;

  Future<StaffProfile> login({
    required String username,
    required String password,
    int gate = 0,
  }) async {
    final data = await _api.post('akf/v1/app/login', {
      'username': username,
      'password': password,
      'gate': gate,
    });
    final token = (data['token'] ?? '').toString();
    if (token.isEmpty) {
      throw StateError('Login response did not contain a token');
    }
    await _storage.saveToken(token);
    final refresh = (data['refresh_token'] ?? '').toString();
    if (refresh.isNotEmpty) await _storage.saveRefresh(refresh);
    _api.setToken(token);
    return StaffProfile(
      name: (data['name'] ?? '').toString(),
      gate: int.tryParse('${data['gate'] ?? gate}') ?? gate,
      canUndo: data['can_undo'] == true,
    );
  }

  Future<String?> restoreToken() async {
    final token = await _storage.readToken();
    _api.setToken(token);
    return token;
  }

  Future<StaffProfile> me() async {
    final data = await _api.get('akf/v1/app/me');
    return StaffProfile.fromJson(data);
  }

  /// Exchange the current refresh token for a new access token.
  /// The old refresh token is rotated and becomes invalid server-side.
  Future<String?> _refreshAccessToken() async {
    final refresh = await _storage.readRefresh();
    if (refresh == null || refresh.isEmpty) return null;
    final callback = _api.refreshToken;
    _api.refreshToken = null; // avoid a refresh loop
    try {
      final data = await _api.post('akf/v1/app/refresh', {'refresh_token': refresh});
      final token = (data['token'] ?? '').toString();
      if (token.isEmpty) return null;
      await _storage.saveToken(token);
      final next = (data['refresh_token'] ?? '').toString();
      if (next.isNotEmpty) await _storage.saveRefresh(next);
      _api.setToken(token);
      return token;
    } catch (_) {
      await _storage.clear();
      _api.setToken(null);
      return null;
    } finally {
      _api.refreshToken = callback;
    }
  }

  /// Register this device and receive a server-issued opaque ID.
  Future<String> registerDevice({String label = 'AKF Gate App'}) async {
    final data = await _api.post('akf/v1/app/device/register', {'label': label});
    final id = (data['device_id'] ?? '').toString();
    if (id.isEmpty) throw StateError('Device registration failed');
    await _storage.saveDevice(id);
    return id;
  }

  Future<String?> storedDevice() => _storage.readDevice();
  Future<String?> storedRefresh() => _storage.readRefresh();

  Future<void> logout() async {
    final token = _api.currentToken;
    if (token != null && token.isNotEmpty) {
      try {
        await _api.post('akf/v1/app/logout', {});
      } catch (_) {
        // Local logout still proceeds if the server is unreachable.
      }
    }
    _api.setToken(null);
    await _storage.clear();
  }
}
