import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/models/models.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class HistoryScreen extends ConsumerStatefulWidget {
  const HistoryScreen({super.key});

  @override
  ConsumerState<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends ConsumerState<HistoryScreen> {
  String _filter = '';
  List<HistoryRow> _rows = [];
  bool _loading = true;

  static const filters = ['', 'valid', 'already_used', 'invalid', 'refunded', 'cancelled'];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final rows = await ref.read(ticketsProvider).history(ref.read(eventIdProvider), filter: _filter);
      if (mounted) setState(() {
        _rows = rows;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(s.get('history'))),
      floatingActionButton: FloatingActionButton(onPressed: _load, child: const Icon(Icons.refresh)),
      body: Column(children: [
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.all(12),
          child: Row(children: [
            for (final f in filters)
              Padding(
                padding: const EdgeInsets.only(right: 6),
                child: ChoiceChip(
                  label: Text(f.isEmpty ? 'All' : f.replaceAll('_', ' ')),
                  selected: _filter == f,
                  onSelected: (_) {
                    setState(() => _filter = f);
                    _load();
                  },
                ),
              ),
          ]),
        ),
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : ListView.builder(
                  itemCount: _rows.length,
                  itemBuilder: (context, i) {
                    final r = _rows[i];
                    return ListTile(
                      leading: Icon(
                        r.result == 'valid' ? Icons.check_circle : Icons.error_outline,
                        color: r.result == 'valid' ? Colors.green : Colors.red,
                      ),
                      title: Text(r.ticketNumber.isEmpty ? r.result : r.ticketNumber),
                      subtitle: Text('${r.time} · ${r.result} · gate ${r.gate} · ${r.staff}'),
                    );
                  },
                ),
        ),
      ]),
    );
  }
}
