import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/models/models.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class EventsScreen extends ConsumerStatefulWidget {
  const EventsScreen({super.key});

  @override
  ConsumerState<EventsScreen> createState() => _EventsScreenState();
}

class _EventsScreenState extends ConsumerState<EventsScreen> {
  List<EventModel> _events = [];
  List<GateModel> _gates = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final events = await ref.read(ticketsProvider).events();
      final gates = await ref.read(ticketsProvider).gates();
      if (mounted) setState(() {
        _events = events;
        _gates = gates;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    final eventId = ref.watch(eventIdProvider);
    final gateId = ref.watch(gateIdProvider);
    return Scaffold(
      appBar: AppBar(title: Text(s.get('events'))),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Text(s.get('event'), style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                ..._events.map((e) => Card(
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: e.id == eventId ? Colors.green : Colors.grey.shade300,
                          child: Icon(e.id == eventId ? Icons.check : Icons.event),
                        ),
                        title: Text(e.title),
                        subtitle: Text('${e.date} · ${e.startTime}–${e.endTime}\n${e.venue} ${e.address}'),
                        isThreeLine: true,
                        onTap: () {
                          ref.read(eventIdProvider.notifier).set(e.id);
                          setState(() {});
                        },
                      ),
                    )),
                const SizedBox(height: 24),
                Text(s.get('gate'), style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                ..._gates.map((g) => ListTile(
                      leading: Radio<int>(
                        value: g.id,
                        groupValue: gateId,
                        onChanged: (v) => ref.read(gateIdProvider.notifier).set(v ?? 0),
                      ),
                      title: Text(g.name),
                      onTap: () => ref.read(gateIdProvider.notifier).set(g.id),
                    )),
              ],
            ),
    );
  }
}
