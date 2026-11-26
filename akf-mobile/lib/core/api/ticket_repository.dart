import 'dart:typed_data';

import 'package:uuid/uuid.dart';

import '../models/models.dart';
import 'api_client.dart';

class TicketRepository {
  TicketRepository(this._api);
  final ApiClient _api;

  Future<List<EventModel>> events() async {
    final data = await _api.get('akf/v1/app/events');
    final list = (data['events'] as List?) ?? const [];
    final all = list
        .map((e) => EventModel.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
    // The site stores one event per language. Gate staff should only see the
    // German version, so filter out Arabic and English duplicates.
    final german = RegExp(r'Tanzen|Einladung|Orientalischer|Deutsch|Feier|Kurs');
    return all.where((e) => german.hasMatch(e.title)).toList();
  }

  Future<List<Map<String, dynamic>>> sessions(int event) async {
    final data = await _api.get('akf/v1/app/sessions', query: {'event': event});
    final list = (data['sessions'] as List?) ?? const [];
    return list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
  }

  Future<List<GateModel>> gates() async {
    final data = await _api.get('akf/v1/app/gates');
    final list = (data['gates'] as List?) ?? const [];
    return list.map((e) => GateModel.fromJson(Map<String, dynamic>.from(e as Map))).toList();
  }

  Future<ScanResult> validate({
    required String code,
    required int event,
    required int gate,
    required String requestId,
    String? deviceId,
    int session = 0,
  }) async {
    final data = await _api.post('akf/v1/app/validate', {
      'code': code,
      'event': event,
      'session': session,
      'gate': gate,
      'request_id': requestId,
      if (deviceId != null) 'device_id': deviceId,
    });
    return ScanResult.fromJson(data);
  }

  Future<ScanResult> checkin({
    required String code,
    required int event,
    required int gate,
    required int persons,
    required String requestId,
    String? deviceId,
    int session = 0,
  }) async {
    final data = await _api.post('akf/v1/app/checkin', {
      'code': code,
      'event': event,
      'session': session,
      'gate': gate,
      'persons': persons,
      'request_id': requestId,
      if (deviceId != null) 'device_id': deviceId,
    });
    return ScanResult.fromJson(data);
  }

  Future<ScanResult> undo({required String code, required String reason}) async {
    final data = await _api.post('akf/v1/app/undo', {'code': code, 'reason': reason});
    return ScanResult.fromJson(data);
  }

  Future<AttendanceStats> stats(int event) async {
    final data = await _api.get('akf/v1/app/stats', query: {'event': event});
    return AttendanceStats.fromJson(data);
  }

  Future<RegistrationQr> registrationQr(int event) async {
    final data = await _api.post('akf/v1/reg/staff/qr', {'event': event});
    return RegistrationQr.fromJson(data);
  }

  Future<Uint8List> exportAttendance(int event, String format) =>
      _api.getBytes('akf/v1/app/attendance/export', query: {'event': event, 'format': format});

  Future<List<AttendanceRow>> attendance(int event) async {
    final data = await _api.get('akf/v1/app/attendance', query: {'event': event});
    final list = (data['attendance'] as List?) ?? const [];
    return list.map((e) => AttendanceRow.fromJson(Map<String, dynamic>.from(e as Map))).toList();
  }

  Future<List<HistoryRow>> history(int event, {String filter = '', int limit = 50}) async {
    final data = await _api.get('akf/v1/app/history', query: {
      'event': event,
      if (filter.isNotEmpty) 'filter': filter,
      'limit': limit,
    });
    final list = (data['history'] as List?) ?? const [];
    return list.map((e) => HistoryRow.fromJson(Map<String, dynamic>.from(e as Map))).toList();
  }

  Future<bool> online() async {
    final data = await _api.get('akf/v1/app/online');
    return data['online'] == true;
  }

  String newDeviceId() => const Uuid().v4();
}
