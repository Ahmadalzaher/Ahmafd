import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../app/router.dart';
import '../../core/models/models.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class RegistrationQrScreen extends ConsumerStatefulWidget {
  const RegistrationQrScreen({super.key});

  @override
  ConsumerState<RegistrationQrScreen> createState() => _RegistrationQrScreenState();
}

class _RegistrationQrScreenState extends ConsumerState<RegistrationQrScreen> {
  RegistrationQr? _data;
  List<EventModel> _events = [];
  int _selected = 0;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
      _data = null;
    });
    try {
      final repo = ref.read(ticketsProvider);
      final events = await repo.events();
      if (events.isEmpty) {
        if (mounted) setState(() {
          _loading = false;
          _error = 'Keine Veranstaltung verfügbar / No event available';
        });
        return;
      }
      var selected = ref.read(eventIdProvider);
      if (!events.any((e) => e.id == selected)) selected = events.first.id;
      if (mounted) setState(() {
        _events = events;
        _selected = selected;
      });
      await _loadFor(selected);
    } catch (_) {
      if (mounted) setState(() {
        _loading = false;
        _error = 'Server unavailable';
      });
    }
  }

  Future<void> _loadFor(int id) async {
    setState(() {
      _loading = true;
      _error = null;
      _data = null;
    });
    try {
      final data = await ref.read(ticketsProvider).registrationQr(id);
      if (data.status != 'Active') {
        if (mounted) setState(() {
          _loading = false;
          _error = 'Registrierung für diese Veranstaltung nicht offen / Registration is not open for this event';
        });
        return;
      }
      ref.read(eventIdProvider.notifier).set(id);
      if (mounted) setState(() {
        _data = data;
        _selected = id;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() {
        _loading = false;
        _error = 'Registration QR unavailable for this event';
      });
    }
  }

  Uint8List? _bytes(String data) {
    if (!data.contains(',')) return null;
    try {
      return base64.decode(data.split(',').last);
    } catch (_) {
      return null;
    }
  }

  @override
  Widget build(BuildContext context) {
    final d = _data;
    final bytes = d != null ? _bytes(d.qrData) : null;
    return Scaffold(
      appBar: AppBar(title: const Text('New Visitor Registration')),
      floatingActionButton: FloatingActionButton(onPressed: _load, child: const Icon(Icons.refresh)),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Center(
            child: Column(
              children: [
                Image.asset('assets/logo/akf_logo.png', height: 54),
                const SizedBox(height: 16),
                if (_events.isNotEmpty)
                  DropdownButtonFormField<int>(
                    initialValue: _selected,
                    isExpanded: true,
                    decoration: const InputDecoration(labelText: 'Event / الفعالية'),
                    items: _events.map((e) => DropdownMenuItem<int>(value: e.id, child: Text(e.title))).toList(),
                    onChanged: (v) {
                      if (v == null || v == _selected) return;
                      setState(() => _selected = v);
                      _loadFor(v);
                    },
                  ),
                const SizedBox(height: 12),
                if (_loading) const CircularProgressIndicator(),
                if (_error != null)
                  Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.red, fontSize: 16)),
                if (d != null) ...[
                  Text(d.title, textAlign: TextAlign.center, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                  Text('${d.date} · ${d.venue}', textAlign: TextAlign.center),
                  const SizedBox(height: 20),
                  if (bytes != null)
                    Container(
                      padding: const EdgeInsets.all(16),
                      color: Colors.white,
                      child: Image.memory(bytes, width: 280, height: 280, fit: BoxFit.contain),
                    )
                  else
                    const Text('QR unavailable', style: TextStyle(color: Colors.red)),
                  const SizedBox(height: 16),
                  const Text('Scan this QR code to register', textAlign: TextAlign.center, style: TextStyle(fontSize: 19, fontWeight: FontWeight.bold)),
                  const Text('Scannen Sie diesen QR-Code zur Anmeldung', textAlign: TextAlign.center),
                  const Text('امسح رمز QR للتسجيل', textAlign: TextAlign.center, style: TextStyle(fontSize: 18)),
                  const SizedBox(height: 8),
                  const Text('Google registration is available on the website / Anmeldung mit Google auf der Webseite möglich / التسجيل عبر Google متاح على الموقع',
                      textAlign: TextAlign.center, style: TextStyle(fontSize: 13, color: Colors.green)),
                  const SizedBox(height: 20),
                  Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                    const Icon(Icons.how_to_reg, color: Colors.green),
                    Text('Confirmed: ${d.counts['confirmed'] ?? 0}  '),
                    const Icon(Icons.pending_actions, color: Colors.orange),
                    Text('Pending: ${d.counts['pending'] ?? 0}  '),
                    const Icon(Icons.event_seat),
                    Text('Available: ${d.counts['available'] ?? '-'}'),
                  ]),
                  const SizedBox(height: 20),
                  OutlinedButton.icon(
                    onPressed: _load,
                    icon: const Icon(Icons.event),
                    label: const Text('Refresh events / Veranstaltungen aktualisieren'),
                  ),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: () => Navigator.pushReplacementNamed(context, AppRouter.dashboard),
                    icon: const Icon(Icons.arrow_back),
                    label: const Text('Back to dashboard / Zurück zum Dashboard'),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
