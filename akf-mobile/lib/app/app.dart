import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/errors/api_exception.dart';
import '../core/providers.dart';
import '../core/state/app_state.dart';
import 'localization/strings.dart';
import 'router.dart';
import 'theme/app_theme.dart';
import '../features/auth/login_screen.dart';
import '../features/dashboard/dashboard_screen.dart';

class AkfApp extends ConsumerStatefulWidget {
  const AkfApp({super.key});

  @override
  ConsumerState<AkfApp> createState() => _AkfAppState();
}

class _AkfAppState extends ConsumerState<AkfApp> {
  bool _restored = false;
  Timer? _inactivityTimer;
  static const _inactivityLimit = Duration(minutes: 15);

  @override
  void initState() {
    super.initState();
    _resetInactivity();
    // Restore only after the first frame is drawn; secure-storage calls before
    // the first frame previously caused a blank launch screen on devices.
    WidgetsBinding.instance.addPostFrameCallback((_) => _restore());
  }

  @override
  void dispose() {
    _inactivityTimer?.cancel();
    super.dispose();
  }

  void _resetInactivity() {
    _inactivityTimer?.cancel();
    _inactivityTimer = Timer(_inactivityLimit, _logoutForInactivity);
  }

  Future<void> _logoutForInactivity() async {
    await ref.read(authProvider).logout();
    ref.read(tokenProvider.notifier).set(null);
    if (mounted) {
      try {
        Navigator.of(context).pushReplacementNamed(AppRouter.login);
      } catch (_) {}
    }
  }


  @override
  Widget build(BuildContext context) {
    final locale = ref.watch(localeProvider);
    return _buildApp(locale);
  }

  Future<void> _restore() async {
    try {
      final token = await ref.read(authProvider).restoreToken();
      if (token != null && token.isNotEmpty) {
        final me = await ref.read(authProvider).me();
        ref.read(tokenProvider.notifier).set(ref.read(apiProvider).currentToken);
        ref.read(staffNameProvider.notifier).set(me.name);
        ref.read(canUndoProvider.notifier).set(me.canUndo);
        ref.read(gateIdProvider.notifier).set(me.gate);
        ref.read(onlineProvider.notifier).set(true);
      } else {
        ref.read(tokenProvider.notifier).set(null);
      }
    } on ApiException catch (e) {
      if (e.status == 401 || e.status == 403) {
        await ref.read(authProvider).logout();
        ref.read(tokenProvider.notifier).set(null);
      } else {
        // Server temporarily unavailable: keep the session, block admission.
        ref.read(onlineProvider.notifier).set(false);
      }
    } catch (_) {
      ref.read(onlineProvider.notifier).set(false);
    } finally {
      if (mounted) setState(() => _restored = true);
    }
  }

  Widget _buildApp(Locale locale) {
    if (!_restored) {
      return const MaterialApp(
        home: Scaffold(
          body: Center(
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              Image(image: AssetImage('assets/logo/akf_logo.png'), width: 220),
              SizedBox(height: 24),
              CircularProgressIndicator(),
            ]),
          ),
        ),
      );
    }
    final loggedIn = ref.watch(tokenProvider) != null;
    return MaterialApp(
      title: AppStrings(locale).get('appName'),
      debugShowCheckedModeBanner: false,
      locale: locale,
      supportedLocales: const [Locale('de'), Locale('en'), Locale('ar')],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      theme: buildTheme(locale: locale),
      home: loggedIn ? const DashboardScreen() : const LoginScreen(),
      onGenerateRoute: (settings) {
        try {
          return AppRouter.generate(settings);
        } catch (_) {
          return MaterialPageRoute(builder: (_) => const LoginScreen());
        }
      },
      onUnknownRoute: (_) => MaterialPageRoute(builder: (_) => const LoginScreen()),
      builder: (context, child) {
        final direction = locale.languageCode == 'ar' ? TextDirection.rtl : TextDirection.ltr;
        return Listener(
          onPointerDown: (_) => _resetInactivity(),
          child: Directionality(textDirection: direction, child: child ?? const SizedBox.shrink()),
        );
      },
    );
  }
}
