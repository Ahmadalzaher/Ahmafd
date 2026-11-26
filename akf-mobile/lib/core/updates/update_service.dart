import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:package_info_plus/package_info_plus.dart';

import '../providers.dart';

/// Shared in-app updater: checks the published version and installs the APK
/// directly through the Android package installer (no browser link).
class UpdateService {
  static const _channel = MethodChannel('de.arabkulturfestival.yalla_events/updater');

  static Future<String> installedVersion() async {
    final info = await PackageInfo.fromPlatform();
    return '${info.version}+${info.buildNumber}';
  }

  static Future<int> installedBuildNumber() async {
    final info = await PackageInfo.fromPlatform();
    return int.tryParse(info.buildNumber) ?? 0;
  }

  static Future<void> checkAndPrompt(BuildContext context, WidgetRef ref) async {
    try {
      final remoteData = await ref.read(apiProvider).get('akf/v1/app/version');
      final remote = int.tryParse('${remoteData['version_code'] ?? 0}') ?? 0;
      final current = await installedBuildNumber();
      if (remote <= current || remote == 0 || !context.mounted) return;
      final version = (remoteData['version_name'] ?? '').toString();
      final notes = (remoteData['changelog'] ?? '').toString();
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
                downloadAndInstall(context, ref);
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

  static Future<void> downloadAndInstall(BuildContext context, WidgetRef ref) async {
    if (!context.mounted) return;
    showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Dialog(
        child: Padding(
          padding: EdgeInsets.all(28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 16),
              Text('Downloading update… / Update wird geladen… / جارٍ تحميل التحديث…'),
            ],
          ),
        ),
      ),
    );
    try {
      final bytes = await ref.read(apiProvider).downloadBytes('akf/v1/app/apk');
      final dir = await _channel.invokeMethod<String>('downloadDir');
      if (dir == null || dir.isEmpty) throw StateError('No download directory');
      final file = File('$dir/yalla-events-update.apk');
      await file.writeAsBytes(bytes, flush: true);

      final canInstall = await _channel.invokeMethod<bool>('canInstall') ?? false;
      if (!canInstall) {
        await _channel.invokeMethod('openInstallSettings');
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Bitte „Unbekannte Apps installieren“ erlauben, dann erneut Update tippen.'),
              duration: Duration(seconds: 8),
            ),
          );
        }
        return;
      }
      await _channel.invokeMethod('installApk', <String, dynamic>{'path': file.path});
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Update failed / Update fehlgeschlagen / فشل التحديث: $e')),
        );
      }
    } finally {
      if (context.mounted && Navigator.of(context).canPop()) {
        Navigator.pop(context);
      }
    }
  }
}
