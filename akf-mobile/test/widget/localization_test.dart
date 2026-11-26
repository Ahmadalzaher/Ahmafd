import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:yalla_events/core/state/app_state.dart';
import 'package:yalla_events/features/tickets/result_screen.dart';

void main() {
  testWidgets('German strings render on result screen', (tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: MaterialApp(
          home: ResultScreen(data: {'result': 'valid', 'message': 'Admission confirmed.', 'number': 'AKF-1'}),
        ),
      ),
    );
    expect(find.text('Check-in erfolgreich – Check-in successful'), findsWidgets);
  });

  testWidgets('Arabic strings render RTL on result screen', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [localeProvider.overrideWith(() => AppValue<Locale>(const Locale('ar')))],
        child: const MaterialApp(
          home: Directionality(textDirection: TextDirection.rtl, child: ResultScreen(data: {'result': 'already_used', 'message': ''})),
        ),
      ),
    );
    expect(find.text('تمت قراءة هذه البطاقة مسبقاً – Already checked in'), findsWidgets);
  });

  testWidgets('English strings render for network error', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [localeProvider.overrideWith(() => AppValue<Locale>(const Locale('en')))],
        child: const MaterialApp(home: ResultScreen(data: {'result': 'network_error', 'message': 'No connection'})),
      ),
    );
    expect(find.textContaining('Check-in unavailable'), findsWidgets);
    expect(find.text('Retry'), findsWidgets);
  });
}
