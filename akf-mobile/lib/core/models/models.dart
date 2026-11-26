class StaffProfile {
  const StaffProfile({required this.name, required this.gate, required this.canUndo, this.role = 'gate_staff'});
  final String name;
  final int gate;
  final bool canUndo;
  final String role;

  factory StaffProfile.fromJson(Map<String, dynamic> json) => StaffProfile(
        name: (json['name'] ?? '').toString(),
        gate: int.tryParse('${json['gate'] ?? 0}') ?? 0,
        canUndo: json['can_undo'] == true || json['can_undo'] == 1,
        role: (json['role'] ?? 'gate_staff').toString(),
      );
}

class GateModel {
  const GateModel({required this.id, required this.name});
  final int id;
  final String name;
  factory GateModel.fromJson(Map<String, dynamic> json) =>
      GateModel(id: int.tryParse('${json['id'] ?? 0}') ?? 0, name: (json['name'] ?? '').toString());
}

class EventModel {
  const EventModel({
    required this.id,
    required this.title,
    required this.date,
    required this.startTime,
    required this.endTime,
    required this.venue,
    required this.address,
    required this.capacity,
  });
  final int id;
  final String title;
  final String date;
  final String startTime;
  final String endTime;
  final String venue;
  final String address;
  final int capacity;

  factory EventModel.fromJson(Map<String, dynamic> json) => EventModel(
        id: int.tryParse('${json['id'] ?? 0}') ?? 0,
        title: (json['title'] ?? '').toString(),
        date: (json['date'] ?? '').toString(),
        startTime: (json['start_time'] ?? '').toString(),
        endTime: (json['end_time'] ?? '').toString(),
        venue: (json['venue'] ?? '').toString(),
        address: (json['address'] ?? '').toString(),
        capacity: int.tryParse('${json['capacity'] ?? 0}') ?? 0,
      );
}

class TicketInfo {
  const TicketInfo({
    required this.number,
    required this.name,
    required this.persons,
    required this.eventId,
    required this.type,
    required this.status,
    required this.checkedIn,
  });
  final String number;
  final String name;
  final int persons;
  final int eventId;
  final int type;
  final String status;
  final bool checkedIn;

  factory TicketInfo.fromJson(Map<String, dynamic> json) => TicketInfo(
        number: (json['number'] ?? '').toString(),
        name: (json['name'] ?? '').toString(),
        persons: int.tryParse('${json['persons'] ?? 0}') ?? 0,
        eventId: int.tryParse('${json['event'] ?? 0}') ?? 0,
        type: int.tryParse('${json['type'] ?? 0}') ?? 0,
        status: (json['status'] ?? '').toString(),
        checkedIn: json['checked_in'] == true,
      );
}

class OriginalCheckin {
  const OriginalCheckin({this.number, this.checkedIn, this.gate, this.staff, this.persons = 0});
  final String? number;
  final String? checkedIn;
  final int? gate;
  final String? staff;
  final int persons;

  factory OriginalCheckin.fromJson(Map<String, dynamic> json) => OriginalCheckin(
        number: json['number']?.toString(),
        checkedIn: json['checked_in']?.toString(),
        gate: int.tryParse('${json['gate'] ?? 0}'),
        staff: json['staff']?.toString(),
        persons: int.tryParse('${json['persons'] ?? 0}') ?? 0,
      );
}

class ScanResult {
  const ScanResult({required this.result, required this.message, this.ticket, this.checkin});
  final String result;
  final String message;
  final TicketInfo? ticket;
  final OriginalCheckin? checkin;

  factory ScanResult.fromJson(Map<String, dynamic> json) => ScanResult(
        result: (json['result'] ?? 'error').toString(),
        message: (json['message'] ?? '').toString(),
        ticket: json['ticket'] is Map ? TicketInfo.fromJson(Map<String, dynamic>.from(json['ticket'] as Map)) : null,
        checkin: json['checkin'] is Map
            ? OriginalCheckin.fromJson(Map<String, dynamic>.from(json['checkin'] as Map))
            : null,
      );
}

class AttendanceStats {
  const AttendanceStats({
    required this.tickets,
    required this.persons,
    required this.registrations,
    required this.confirmedRegistrations,
    required this.pendingRegistrations,
    required this.registeredPersons,
    required this.checkedIn,
    required this.admittedPersons,
    required this.remainingPersons,
    required this.percent,
  });
  final int tickets;
  final int persons;
  final int registrations;
  final int confirmedRegistrations;
  final int pendingRegistrations;
  final int registeredPersons;
  final int checkedIn;
  final int admittedPersons;
  final int remainingPersons;
  final int percent;

  factory AttendanceStats.fromJson(Map<String, dynamic> json) => AttendanceStats(
        tickets: int.tryParse('${json['tickets'] ?? 0}') ?? 0,
        persons: int.tryParse('${json['persons'] ?? 0}') ?? 0,
        registrations: int.tryParse('${json['registrations'] ?? 0}') ?? 0,
        confirmedRegistrations: int.tryParse('${json['confirmed_registrations'] ?? 0}') ?? 0,
        pendingRegistrations: int.tryParse('${json['pending_registrations'] ?? 0}') ?? 0,
        registeredPersons: int.tryParse('${json['registered_persons'] ?? 0}') ?? 0,
        checkedIn: int.tryParse('${json['checked_in'] ?? 0}') ?? 0,
        admittedPersons: int.tryParse('${json['admitted_persons'] ?? 0}') ?? 0,
        remainingPersons: int.tryParse('${json['remaining_persons'] ?? 0}') ?? 0,
        percent: int.tryParse('${json['percent'] ?? 0}') ?? 0,
      );
}

class RegistrationQr {
  const RegistrationQr({
    required this.eventId,
    required this.title,
    required this.date,
    required this.venue,
    required this.status,
    required this.qrData,
    required this.counts,
    required this.recent,
  });
  final int eventId;
  final String title;
  final String date;
  final String venue;
  final String status;
  final String qrData;
  final Map<String, dynamic> counts;
  final List<dynamic> recent;

  factory RegistrationQr.fromJson(Map<String, dynamic> json) => RegistrationQr(
        eventId: int.tryParse('${json['event_id'] ?? 0}') ?? 0,
        title: (json['title'] ?? '').toString(),
        date: (json['date'] ?? '').toString(),
        venue: (json['venue'] ?? '').toString(),
        status: (json['status'] ?? '').toString(),
        qrData: (json['qr_data'] ?? '').toString(),
        counts: json['counts'] is Map ? Map<String, dynamic>.from(json['counts'] as Map) : <String, dynamic>{},
        recent: json['recent'] is List ? List<dynamic>.from(json['recent'] as List) : const [],
      );
}

class AttendanceRow {
  const AttendanceRow({
    required this.number,
    required this.name,
    required this.persons,
    required this.status,
    required this.checkedIn,
    required this.gate,
    required this.present,
  });
  final String number;
  final String name;
  final int persons;
  final String status;
  final String? checkedIn;
  final String? gate;
  final bool present;

  factory AttendanceRow.fromJson(Map<String, dynamic> json) => AttendanceRow(
        number: (json['number'] ?? '').toString(),
        name: (json['name'] ?? '').toString(),
        persons: int.tryParse('${json['persons'] ?? 0}') ?? 0,
        status: (json['status'] ?? '').toString(),
        checkedIn: json['checked_in']?.toString(),
        gate: json['gate']?.toString(),
        present: json['present'] == true,
      );
}

class HistoryRow {
  const HistoryRow({
    required this.time,
    required this.result,
    required this.ticketNumber,
    required this.gate,
    required this.staff,
    required this.persons,
  });
  final String time;
  final String result;
  final String ticketNumber;
  final int gate;
  final String staff;
  final int persons;

  factory HistoryRow.fromJson(Map<String, dynamic> json) => HistoryRow(
        time: (json['created_at'] ?? '').toString(),
        result: (json['result'] ?? '').toString(),
        ticketNumber: (json['ticket_number'] ?? '').toString(),
        gate: int.tryParse('${json['gate_id'] ?? 0}') ?? 0,
        staff: (json['staff_name'] ?? '').toString(),
        persons: int.tryParse('${json['persons_admitted'] ?? 0}') ?? 0,
      );
}
