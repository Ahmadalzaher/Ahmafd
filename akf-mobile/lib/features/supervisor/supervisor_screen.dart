import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/errors/api_exception.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';
import '../../core/utils/barcode_parser.dart';

class SupervisorScreen extends ConsumerStatefulWidget {
  const SupervisorScreen({super.key});

  @override
  ConsumerState<SupervisorScreen> createState() => _SupervisorScreenState();
}

class _SupervisorScreenState extends ConsumerState<SupervisorScreen> {
  final _code = TextEditingController();
  final _reason = TextEditingController();
  String? _message;
  bool _busy = false;

  @override
  void dispose() {
    _code.dispose();
    _reason.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!ref.read(canUndoProvider)) {
      setState(() => _message = 'Not authorized');
      return;
    }
    final reason = _reason.text.trim();
    if (reason.isEmpty) {
      setState(() => _message = ref.read(stringsProvider).get('reason'));
      return;
    }
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(ref.read(stringsProvider).get('undo')),
        content: Text('$reason'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Confirm')),
        ],
      ),
    );
    if (confirmed != true) return;
    setState(() {
      _busy = true;
      _message = null;
    });
    try {
      final result = await ref.read(ticketsProvider).undo(
            code: BarcodeParser.normalize(_code.text),
            reason: reason,
          );
      setState(() => _message = result.message);
    } on ApiException catch (e) {
      setState(() => _message = e.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    if (!ref.watch(canUndoProvider)) {
      return Scaffold(
        appBar: AppBar(title: Text(s.get('supervisor'))),
        body: const Center(child: Text('Supervisor permission required')),
      );
    }
    return Scaffold(
      appBar: AppBar(title: Text(s.get('supervisor'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(controller: _code, decoration: InputDecoration(labelText: 'Ticket token')),
          const SizedBox(height: 16),
          TextField(controller: _reason, decoration: InputDecoration(labelText: s.get('reason'))),
          if (_message != null) ...[
            const SizedBox(height: 12),
            Text(_message!, style: const TextStyle(color: Colors.green)),
          ],
          const SizedBox(height: 24),
          FilledButton.icon(
            onPressed: _busy ? null : _submit,
            icon: const Icon(Icons.undo),
            label: Text(s.get('undo')),
          ),
        ],
      ),
    );
  }
}
