import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class DevicesScreen extends ConsumerStatefulWidget {
  const DevicesScreen({super.key});

  @override
  ConsumerState<DevicesScreen> createState() => _DevicesScreenState();
}

class _DevicesScreenState extends ConsumerState<DevicesScreen> {
  String _id = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      var id = await ref.read(authProvider).storedDevice() ?? '';
      if (id.isEmpty) {
        id = await ref.read(authProvider).registerDevice();
      }
      if (mounted) setState(() => _id = id);
    } catch (_) {
      if (mounted) setState(() => _id = 'Not registered');
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(s.get('device'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(child: ListTile(title: Text('Device ID'), subtitle: Text(_id))),
          const SizedBox(height: 16),
          const Text(
            'This ID was issued by the server and is bound to your staff account. It is sent with every scan.',
          ),
        ],
      ),
    );
  }
}
