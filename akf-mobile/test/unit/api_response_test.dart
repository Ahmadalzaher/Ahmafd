import 'package:flutter_test/flutter_test.dart';
import 'package:yalla_events/core/models/models.dart';

void main() {
  test('scan result parses valid ticket', () {
    final r = ScanResult.fromJson({
      'result': 'valid',
      'message': 'Admission confirmed.',
      'ticket': {
        'number': 'AKF-2026-000007',
        'name': 'Guest',
        'persons': 2,
        'event': 1,
        'type': 1,
        'status': 'valid',
        'checked_in': false,
      },
    });
    expect(r.result, 'valid');
    expect(r.ticket?.persons, 2);
    expect(r.ticket?.number, 'AKF-2026-000007');
  });

  test('already used includes original check-in', () {
    final r = ScanResult.fromJson({
      'result': 'already_used',
      'message': 'Ticket already used.',
      'checkin': {'number': 'AKF-2026-000006', 'checked_in': '2026-11-25 10:00:00', 'gate': 1},
    });
    expect(r.checkin?.gate, 1);
    expect(r.checkin?.checkedIn, '2026-11-25 10:00:00');
  });

  test('attendance row parses present and absent states', () {
    final absent = AttendanceRow.fromJson({
      'number': 'AKF-2026-000014',
      'name': 'Guest',
      'persons': 2,
      'status': 'valid',
      'checked_in': null,
      'gate': null,
      'present': false,
    });
    final present = AttendanceRow.fromJson({
      'number': 'AKF-2026-000014',
      'name': 'Guest',
      'persons': 2,
      'status': 'used',
      'checked_in': '2026-11-26 10:39:40',
      'gate': 'Haupteingang',
      'present': true,
    });
    expect(absent.present, isFalse);
    expect(present.present, isTrue);
    expect(present.gate, 'Haupteingang');
    expect(present.persons, 2);
  });

  test('stats keeps person count separate from ticket count', () {
    final s = AttendanceStats.fromJson({
      'tickets': 3,
      'persons': 7,
      'checked_in': 2,
      'admitted_persons': 5,
      'remaining_persons': 2,
      'percent': 71,
    });
    expect(s.tickets, 3);
    expect(s.admittedPersons, 5);
  });
}
