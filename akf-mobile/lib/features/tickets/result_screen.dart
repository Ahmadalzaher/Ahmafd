import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../app/router.dart';
import '../../core/state/app_state.dart';

class ResultScreen extends ConsumerWidget {
  const ResultScreen({super.key, required this.data});
  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    final result = (data['result'] ?? 'error').toString();
    final map = <String, Color>{
      'valid': AppResultColors.green,
      'already_used': AppResultColors.red,
      'invalid': AppResultColors.red,
      'refunded': AppResultColors.red,
      'cancelled': AppResultColors.red,
      'wrong_event': AppResultColors.orange,
      'wrong_session': AppResultColors.orange,
      'session_full': AppResultColors.orange,
      'network_error': AppResultColors.red,
      'error': AppResultColors.orange,
    };
    final color = map[result] ?? AppResultColors.orange;
    final titleKey = <String, String>{
      'valid': 'valid',
      'already_used': 'alreadyUsed',
      'invalid': 'invalid',
      'refunded': 'refunded',
      'cancelled': 'cancelled',
      'wrong_event': 'wrongEvent',
      'wrong_session': 'wrongEvent',
      'session_full': 'wrongEvent',
      'network_error': 'networkError',
    }[result];

    return Scaffold(
      backgroundColor: color,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        title: Text(titleKey == null ? s.get('invalid') : s.get(titleKey)),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  result == 'valid' ? Icons.check_circle : Icons.error_outline,
                  size: 110,
                  color: Colors.white,
                ),
                const SizedBox(height: 18),
                Text(
                  titleKey == null ? result.toUpperCase() : s.get(titleKey),
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 12),
                Text((data['message'] ?? '').toString(),
                    textAlign: TextAlign.center, style: const TextStyle(color: Colors.white, fontSize: 16)),
                if (result == 'network_error')
                  Padding(
                    padding: const EdgeInsets.only(top: 16),
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(backgroundColor: Colors.white, foregroundColor: color),
                      onPressed: () => Navigator.pushReplacementNamed(context, AppRouter.scanner),
                      child: Text(s.get('retry')),
                    ),
                  )
                else ...[
                  const SizedBox(height: 24),
                  _Info(label: 'Ticket', value: (data['number'] ?? '').toString()),
                  if ((data['name'] ?? '').toString().isNotEmpty)
                    _Info(label: 'Guest', value: data['name'].toString()),
                  if ((data['persons'] ?? 0) != 0)
                    _Info(label: s.get('persons'), value: '${data['persons']}'),
                  if ((data['checkedIn'] ?? '').toString().isNotEmpty)
                    _Info(label: s.get('checkedIn'), value: data['checkedIn'].toString()),
                  if ((data['gate'] ?? 0) != 0)
                    _Info(label: s.get('gate'), value: '${data['gate']}'),
                  if ((data['staff'] ?? '').toString().isNotEmpty)
                    _Info(label: 'Staff', value: data['staff'].toString()),
                ],
                const SizedBox(height: 30),
                FilledButton(
                  style: FilledButton.styleFrom(backgroundColor: Colors.white, foregroundColor: color),
                  onPressed: () => Navigator.pushReplacementNamed(context, AppRouter.scanner),
                  child: Text(s.get('scan')),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _Info extends StatelessWidget {
  const _Info({required this.label, required this.value});
  final String label;
  final String value;
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
          Text('$label: ', style: const TextStyle(color: Colors.white70)),
          Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        ]),
      );
}

class AppResultColors {
  static const green = Color(0xFF2E7D4F);
  static const red = Color(0xFFB3261E);
  static const orange = Color(0xFFE08A00);
}
