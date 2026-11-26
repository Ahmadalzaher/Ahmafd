import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:vibration/vibration.dart';

import '../../app/router.dart';
import '../../core/errors/api_exception.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';
import '../../core/utils/barcode_parser.dart';

class ScannerScreen extends ConsumerStatefulWidget {
  const ScannerScreen({super.key});

  @override
  ConsumerState<ScannerScreen> createState() => _ScannerScreenState();
}

class _ScannerScreenState extends ConsumerState<ScannerScreen> {
  final MobileScannerController _controller = MobileScannerController(
    formats: const [BarcodeFormat.qrCode, BarcodeFormat.code128],
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
    torchEnabled: false,
  );
  bool _inFlight = false;
  bool _online = true;
  String? _deviceId;
  Timer? _healthTimer;

  @override
  void initState() {
    super.initState();
    _loadDevice();
    _startCamera();
    _checkServer();
    _healthTimer = Timer.periodic(const Duration(seconds: 10), (_) => _checkServer());
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // Coming back from the result screen re-activates this route.
    _startCamera();
  }

  @override
  void dispose() {
    _healthTimer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  Future<void> _loadDevice() async {
    final stored = await ref.read(authProvider).storedDevice();
    if (stored != null && stored.isNotEmpty) {
      _deviceId = stored;
      return;
    }
    try {
      _deviceId = await ref.read(authProvider).registerDevice();
    } catch (_) {
      _deviceId = null;
    }
  }

  Future<void> _startCamera() async {
    try {
      await _controller.start();
    } catch (_) {
      // Permission may still be pending; the user can retry from the UI.
    }
  }

  Future<void> _checkServer() async {
    try {
      final ok = await ref.read(ticketsProvider).online();
      ref.read(onlineProvider.notifier).set(ok);
      if (mounted) setState(() => _online = ok);
    } catch (_) {
      ref.read(onlineProvider.notifier).set(false);
      if (mounted) setState(() => _online = false);
    }
    // Never stop the camera here: a slow health check must not break scanning.
  }

  void _onDetect(BarcodeCapture capture) {
    if (_inFlight) return;
    final barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;
    final raw = barcodes.first.rawValue;
    if (raw == null || raw.isEmpty) return;
    final code = BarcodeParser.normalize(raw);
    // Only an in-flight request blocks a new detection. Duplicate admissions
    // are prevented atomically by the server, so returning from the result
    // screen can immediately scan the same ticket again.
    _submit(code);
  }

  Future<void> _submit(String code) async {
    if (_inFlight) return;
    setState(() => _inFlight = true);
    final requestId = BarcodeParser.newRequestId();
    final event = ref.read(eventIdProvider);
    final gate = ref.read(gateIdProvider);
    try {
      final repo = ref.read(ticketsProvider);
      final check = await repo.validate(
        code: code,
        event: event,
        gate: gate,
        requestId: requestId,
        deviceId: _deviceId,
        session: ref.read(sessionIdProvider),
      );
      if (check.result != 'valid') {
        _finish(check, code);
        return;
      }
      final admitted = await repo.checkin(
        code: code,
        event: event,
        gate: gate,
        persons: check.ticket?.persons ?? 1,
        requestId: requestId,
        deviceId: _deviceId,
        session: ref.read(sessionIdProvider),
      );
      if (admitted.result == 'valid') {
        Vibration.vibrate(duration: 120);
      }
      _finish(admitted, code);
    } on ApiException catch (e) {
      ref.read(onlineProvider.notifier).set(false);
      _finishNetworkError(e.message, code);
    } catch (_) {
      _finishNetworkError('Connection error', code);
    } finally {
      if (mounted) setState(() => _inFlight = false);
    }
  }

  void _finish(dynamic result, String code) {
    final ticket = result.ticket;
    final original = result.checkin;
    if (!mounted) return;
    Navigator.pushNamed(context, AppRouter.result, arguments: <String, dynamic>{
      'result': result.result,
      'message': result.message,
      'code': code,
      'number': ticket?.number ?? original?.number ?? '',
      'name': ticket?.name ?? '',
      'persons': ticket?.persons ?? original?.persons ?? 0,
      'checkedIn': original?.checkedIn ?? '',
      'gate': original?.gate ?? 0,
      'staff': original?.staff ?? '',
    });
  }

  void _finishNetworkError(String message, String code) {
    if (!mounted) return;
    Navigator.pushNamed(context, AppRouter.result, arguments: <String, dynamic>{
      'result': 'network_error',
      'message': message,
      'code': code,
    });
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(
        title: Text(s.get('scan')),
        actions: [
          IconButton(
            tooltip: s.get('torch'),
            onPressed: () => _controller.toggleTorch(),
            icon: const Icon(Icons.flashlight_on),
          ),
          IconButton(
            onPressed: () => _controller.switchCamera(),
            icon: const Icon(Icons.cameraswitch),
          ),
        ],
      ),
      body: Stack(
        children: [
          MobileScanner(controller: _controller, onDetect: _onDetect),
          if (!_online)
            Container(
              color: Colors.red.withOpacity(0.92),
              alignment: Alignment.center,
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(Icons.cloud_off, size: 64, color: Colors.white),
                const SizedBox(height: 12),
                Text(s.get('offline'), style: const TextStyle(color: Colors.white, fontSize: 20)),
                const SizedBox(height: 18),
                ElevatedButton(onPressed: _checkServer, child: Text(s.get('retry'))),
              ]),
            )
          else
            Align(
              alignment: Alignment.bottomCenter,
              child: Container(
                margin: const EdgeInsets.only(bottom: 24),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(24)),
                child: Text(_inFlight ? s.get('detected') : s.get('startCamera'),
                    style: const TextStyle(color: Colors.white)),
              ),
            ),
        ],
      ),
    );
  }
}
