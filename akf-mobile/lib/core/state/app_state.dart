import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../app/localization/strings.dart';

final AppValue<String?> startupErrorNotifier = AppValue<String?>(null);
final startupErrorProvider = NotifierProvider<AppValue<String?>, String?>(
  () => startupErrorNotifier,
);

class AppValue<T> extends Notifier<T> {
  AppValue(this.initial);
  final T initial;
  @override
  T build() => initial;
  void set(T value) => state = value;
}

final localeProvider = NotifierProvider<AppValue<Locale>, Locale>(
  () => AppValue(const Locale('de')),
);
final stringsProvider = Provider<AppStrings>((ref) => AppStrings(ref.watch(localeProvider)));

final tokenProvider = NotifierProvider<AppValue<String?>, String?>(
  () => AppValue(null),
);
final staffNameProvider = NotifierProvider<AppValue<String>, String>(
  () => AppValue(''),
);
final canUndoProvider = NotifierProvider<AppValue<bool>, bool>(
  () => AppValue(false),
);
final eventIdProvider = NotifierProvider<AppValue<int>, int>(
  () => AppValue(0),
);
final gateIdProvider = NotifierProvider<AppValue<int>, int>(
  () => AppValue(0),
);
final sessionIdProvider = NotifierProvider<AppValue<int>, int>(
  () => AppValue(0),
);
final onlineProvider = NotifierProvider<AppValue<bool>, bool>(
  () => AppValue(true),
);
final lastServerTimeProvider = NotifierProvider<AppValue<String>, String>(
  () => AppValue(''),
);
