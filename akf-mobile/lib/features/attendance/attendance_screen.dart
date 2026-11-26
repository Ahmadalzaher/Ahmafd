import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/models/models.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class AttendanceScreen extends ConsumerStatefulWidget {
  const AttendanceScreen({super.key});

  @override
  ConsumerState<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends ConsumerState<AttendanceScreen> {
  AttendanceStats? _stats;
  List<EventModel> _events = [];
  int _event = 0;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _event = ref.read(eventIdProvider);
    _loadEvents();
  }

  Future<void> _loadEvents() async {
    try {
      final events = await ref.read(ticketsProvider).events();
      if (mounted) setState(() {
        _events = events;
        if (_event == 0 && events.isNotEmpty) { _event = events.first.id; }
      });
    } catch (_) {}
    _load();
  }

  Future<void> _load() async {
    try {
      final stats = await ref.read(ticketsProvider).stats(_event);
      if (mounted) setState(() {
        _stats = stats;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    final st = _stats;
    return Scaffold(
      appBar: AppBar(title: Text(s.get('attendance'))),
      floatingActionButton: FloatingActionButton(onPressed: _load, child: const Icon(Icons.refresh)),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : st == null
              ? const Center(child: Text('No data'))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    if (_events.isNotEmpty)
                      DropdownButtonFormField<int>(
                        initialValue: _event,
                        isExpanded: true,
                        decoration: const InputDecoration(labelText: 'Event / الفعالية'),
                        items: _events.map((e) => DropdownMenuItem<int>(value: e.id, child: Text(e.title))).toList(),
                        onChanged: (v) {
                          if (v == null) return;
                          setState(() => _event = v);
                          ref.read(eventIdProvider.notifier).set(v);
                          _load();
                        },
                      ),
                    Card(child: ListTile(title: Text(s.get('tickets')), trailing: Text('${st.tickets}'))),
                    Card(child: ListTile(title: Text(s.get('persons')), trailing: Text('${st.persons}'))),
                    Card(child: ListTile(title: Text(s.get('checkedIn')), trailing: Text('${st.checkedIn}'))),
                    Card(child: ListTile(title: Text('Admitted persons'), trailing: Text('${st.admittedPersons}'))),
                    Card(child: ListTile(title: Text(s.get('remaining')), trailing: Text('${st.remainingPersons}'))),
                    Card(child: ListTile(title: Text('Attendance'), trailing: Text('${st.percent}%'))),
                    LinearProgressIndicator(value: st.percent / 100, minHeight: 12),
                  ],
                ),
    );
  }
}
