import 'package:flutter/material.dart';

import '../features/attendance/attendance_list_screen.dart';
import '../features/attendance/attendance_screen.dart';
import '../features/auth/login_screen.dart';
import '../features/dashboard/dashboard_screen.dart';
import '../features/devices/devices_screen.dart';
import '../features/events/events_screen.dart';
import '../features/events/registration_qr_screen.dart';
import '../features/events/sessions_screen.dart';
import '../features/history/history_screen.dart';
import '../features/profile/profile_screen.dart';
import '../features/scanner/scanner_screen.dart';
import '../features/settings/settings_screen.dart';
import '../features/supervisor/supervisor_screen.dart';
import '../features/tickets/result_screen.dart';

class AppRouter {
  static const splash = '/';
  static const login = '/login';
  static const dashboard = '/dashboard';
  static const events = '/events';
  static const sessions = '/sessions';
  static const registrationQr = '/registration-qr';
  static const scanner = '/scanner';
  static const result = '/result';
  static const history = '/history';
  static const attendance = '/attendance';
  static const attendanceList = '/attendance-list';
  static const supervisor = '/supervisor';
  static const devices = '/devices';
  static const settings = '/settings';
  static const profile = '/profile';

  static Route<dynamic> generate(RouteSettings routeSettings) {
    switch (routeSettings.name) {
      case login:
        return MaterialPageRoute(builder: (_) => const LoginScreen());
      case dashboard:
        return MaterialPageRoute(builder: (_) => const DashboardScreen());
      case events:
        return MaterialPageRoute(builder: (_) => const EventsScreen());
      case sessions:
        return MaterialPageRoute(builder: (_) => const SessionsScreen());
      case registrationQr:
        return MaterialPageRoute(builder: (_) => const RegistrationQrScreen());
      case scanner:
        return MaterialPageRoute(builder: (_) => const ScannerScreen());
      case result:
        final args = routeSettings.arguments as Map<String, dynamic>? ?? const <String, dynamic>{};
        return MaterialPageRoute(builder: (_) => ResultScreen(data: args));
      case history:
        return MaterialPageRoute(builder: (_) => const HistoryScreen());
      case attendance:
        return MaterialPageRoute(builder: (_) => const AttendanceScreen());
      case attendanceList:
        return MaterialPageRoute(builder: (_) => const AttendanceListScreen());
      case supervisor:
        return MaterialPageRoute(builder: (_) => const SupervisorScreen());
      case devices:
        return MaterialPageRoute(builder: (_) => const DevicesScreen());
      case settings:
        return MaterialPageRoute(builder: (_) => const SettingsScreen());
      case profile:
        return MaterialPageRoute(builder: (_) => const ProfileScreen());
      default:
        return MaterialPageRoute(builder: (_) => const LoginScreen());
    }
  }
}
