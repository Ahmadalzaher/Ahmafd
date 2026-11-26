import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class SessionsScreen extends ConsumerStatefulWidget {
  const SessionsScreen({super.key});

  @override
  ConsumerState<SessionsScreen> createState() => _SessionsScreenState();
}

class _SessionsScreenState extends ConsumerState<SessionsScreen> {
  List<Map<String, dynamic>> _sessions = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final rows = await ref.read(ticketsProvider).sessions(ref.read(eventIdProvider));
      if (mounted) setState(() {
        _sessions = rows;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final selected = ref.watch(sessionIdProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Session')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                ListTile(
                  title: Text('All sessions'),
                  leading: Radio<int>(
                    value: 0,
                    groupValue: selected,
                    onChanged: (v) => ref.read(sessionIdProvider.notifier).set(v ?? 0),
                  ),
                  onTap: () => ref.read(sessionIdProvider.notifier).set(0),
                ),
                ..._sessions.map((row) => ListTile(
                      title: Text((row['title'] ?? '').toString()),
                      subtitle: Text('${row['starts_at'] ?? ''} · ${row['venue'] ?? ''}'),
                      leading: Radio<int>(
                        value: int.tryParse('${row['id'] ?? 0}') ?? 0,
                        groupValue: selected,
                        onChanged: (v) => ref.read(sessionIdProvider.notifier).set(v ?? 0),
                      ),
                      onTap: () => ref.read(sessionIdProvider.notifier).set(int.tryParse('${row['id']}') ?? 0),
                    )),
                if (_sessions.isEmpty) const Padding(padding: EdgeInsets.all(16), child: Text('No sessions configured.')),
              ],
            ),
    );
  }
}
