import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:path_provider/path_provider.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../../core/models/models.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class AttendanceListScreen extends ConsumerStatefulWidget {
  const AttendanceListScreen({super.key});

  @override
  ConsumerState<AttendanceListScreen> createState() => _AttendanceListScreenState();
}

class _AttendanceListScreenState extends ConsumerState<AttendanceListScreen> {
  List<AttendanceRow> _rows = [];
  List<EventModel> _events = [];
  int _event = 0;
  String _filter = 'all';
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
    setState(() => _loading = true);
    try {
      final rows = await ref.read(ticketsProvider).attendance(_event);
      if (mounted) setState(() {
        _rows = rows;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _save(String format) async {
    try {
      final bytes = await ref.read(ticketsProvider).exportAttendance(_event, format);
      final dir = await getApplicationDocumentsDirectory();
      final ext = format == 'xls' ? 'xls' : 'csv';
      final file = File('${dir.path}/attendance-event-${ref.read(eventIdProvider)}.$ext');
      await file.writeAsBytes(bytes);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Saved: ${file.path}')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Export failed')));
      }
    }
  }

  Future<void> _print() async {
    try {
      final doc = pw.Document();
      doc.addPage(
        pw.MultiPage(
          build: (context) => [
            pw.Header(level: 0, child: pw.Text('Attendance / الحضور')),
            pw.Table.fromTextArray(
              headers: ['Ticket', 'Guest', 'Persons', 'Status', 'Checked in', 'Gate'],
              data: _rows.map((r) => [
                    r.number,
                    r.name,
                    '${r.persons}',
                    r.present ? 'Present' : 'Absent',
                    r.checkedIn ?? '',
                    r.gate ?? '',
                  ]).toList(),
            ),
          ],
        ),
      );
      await Printing.sharePdf(bytes: await doc.save(), filename: 'attendance-event-${ref.read(eventIdProvider)}.pdf');
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Print failed')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final shown = _filter == 'present'
        ? _rows.where((r) => r.present).toList()
        : _filter == 'absent'
            ? _rows.where((r) => !r.present).toList()
            : _rows;
    return Scaffold(
      appBar: AppBar(title: const Text('Attendance / الحضور')),
      floatingActionButton: FloatingActionButton(onPressed: _load, child: const Icon(Icons.refresh)),
      body: Column(
        children: [
          if (_events.isNotEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
              child: DropdownButtonFormField<int>(
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
            ),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                ChoiceChip(label: const Text('All / الكل'), selected: _filter == 'all',
                    onSelected: (_) => setState(() => _filter = 'all')),
                const SizedBox(width: 8),
                ChoiceChip(label: const Text('Present / حضر'), selected: _filter == 'present',
                    onSelected: (_) => setState(() => _filter = 'present')),
                const SizedBox(width: 8),
                ChoiceChip(label: const Text('Absent / لم يحضر'), selected: _filter == 'absent',
                    onSelected: (_) => setState(() => _filter = 'absent')),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: Wrap(
              spacing: 8,
              children: [
                OutlinedButton.icon(
                  onPressed: () => _save('csv'),
                  icon: const Icon(Icons.download),
                  label: const Text('CSV'),
                ),
                OutlinedButton.icon(
                  onPressed: () => _save('xls'),
                  icon: const Icon(Icons.table_chart),
                  label: const Text('Excel'),
                ),
                OutlinedButton.icon(
                  onPressed: _print,
                  icon: const Icon(Icons.print),
                  label: const Text('Print'),
                ),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _load,
                    child: ListView.builder(
                      itemCount: shown.length,
                      itemBuilder: (context, i) {
                        final r = shown[i];
                        return Card(
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: r.present ? Colors.green : Colors.grey.shade400,
                              child: Icon(r.present ? Icons.check : Icons.person_off, color: Colors.white),
                            ),
                            title: Text(r.number),
                            subtitle: Text(
                              '${r.name} · ${r.persons}\n'
                              '${r.present ? 'حضر / Present' : 'لم يحضر / Absent'}'
                              '${r.checkedIn != null ? ' · ${r.checkedIn}' : ''}'
                              '${r.gate != null ? ' · ${r.gate}' : ''}',
                            ),
                            isThreeLine: true,
                          ),
                        );
                      },
                    ),
                  ),
          ),
        ],
      ),
    );
  }
}
