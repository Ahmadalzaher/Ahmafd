import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/services.dart';
import 'package:package_info_plus/package_info_plus.dart';

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
  static const _updater = MethodChannel('de.arabkulturfestival.yalla_events/updater');
  bool _restored = false;
  bool _updateChecked = false;

  @override
  void initState() {
    super.initState();
    // Restore only after the first frame is drawn; secure-storage calls before
    // the first frame previously caused a blank launch screen on devices.
    WidgetsBinding.instance.addPostFrameCallback((_) => _restore());
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
      if (mounted) {
        setState(() => _restored = true);
        _checkUpdate();
      }
    }
  }

  /// Offer the published APK when the server has a newer build.
  /// A failed check never blocks the app.
  Future<void> _checkUpdate() async {
    if (_updateChecked) return;
    _updateChecked = true;
    try {
      final info = await PackageInfo.fromPlatform();
      final data = await ref.read(apiProvider).get('akf/v1/app/version');
      final remote = int.tryParse('${data['version_code'] ?? 0}') ?? 0;
      final current = int.tryParse(info.buildNumber) ?? 0;
      if (remote <= current || remote == 0 || !mounted) return;
      final version = (data['version_name'] ?? '').toString();
      final notes = (data['changelog'] ?? '').toString();
      showDialog<void>(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          title: const Text('Update available / Update verfügbar / يوجد تحديث'),
          content: Text(
            '${version.isEmpty ? '' : 'Version $version\n\n'}'
            '${notes.isEmpty ? 'A new version is ready / Eine neue Version ist verfügbar / يتوفر إصدار جديد' : notes}',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Later / Später / لاحقاً'),
            ),
            TextButton(
              onPressed: () {
                Navigator.pop(ctx);
                _downloadAndInstall();
              },
              child: const Text('Update / Aktualisieren / تحديث'),
            ),
          ],
        ),
      );
    } catch (_) {
      // Offline or unavailable endpoint: keep using the installed version.
    }
  }

  /// Download the published APK and hand it to the Android installer.
  Future<void> _downloadAndInstall() async {
    if (!mounted) return;
    final progress = Dialog(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: const [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Downloading update… / Update wird geladen… / جارٍ تحميل التحديث…'),
          ],
        ),
      ),
    );
    showDialog<void>(context: context, barrierDismissible: false, builder: (_) => progress);
    try {
      final bytes = await ref.read(apiProvider).downloadBytes('akf/v1/app/apk');
      final dir = await _updater.invokeMethod<String>('downloadDir');
      if (dir == null || dir.isEmpty) throw StateError('No download directory');
      final file = File('$dir/yalla-events-update.apk');
      await file.writeAsBytes(bytes, flush: true);

      final canInstall = await _updater.invokeMethod<bool>('canInstall') ?? false;
      if (!canInstall) {
        await _updater.invokeMethod('openInstallSettings');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Bitte „Unbekannte Apps installieren“ für Yalla Events erlauben, dann erneut Update tippen.'),
              duration: Duration(seconds: 8),
            ),
          );
        }
        return;
      }
      await _updater.invokeMethod('installApk', <String, dynamic>{'path': file.path});
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Update failed / Update fehlgeschlagen / فشل التحديث: $e')),
        );
      }
    } finally {
      if (mounted && Navigator.of(context).canPop()) {
        Navigator.pop(context);
      }
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
        return Directionality(textDirection: direction, child: child ?? const SizedBox.shrink());
      },
    );
  }
}
