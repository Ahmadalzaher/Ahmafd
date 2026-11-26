import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../app/router.dart';
import '../../core/models/models.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';
import '../../core/utils/logout.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  List<EventModel> _events = [];
  List<GateModel> _gates = [];
  AttendanceStats? _stats;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final events = await ref.read(ticketsProvider).events();
      final gates = await ref.read(ticketsProvider).gates();
      final eventId = ref.read(eventIdProvider);
      final chosen = eventId == 0 && events.isNotEmpty ? events.first.id : eventId;
      ref.read(eventIdProvider.notifier).set(chosen);
      AttendanceStats? stats;
      if (chosen != 0) stats = await ref.read(ticketsProvider).stats(chosen);
      final health = await ref.read(ticketsProvider).online();
      ref.read(onlineProvider.notifier).set(health);
      if (mounted) setState(() {
        _events = events;
        _gates = gates;
        _stats = stats;
        _error = null;
        _loading = false;
      });
    } catch (e) {
      ref.read(onlineProvider.notifier).set(false);
      if (mounted) setState(() {
        _error = 'Server unreachable';
        _loading = false;
      });
    }
  }

  Future<void> _logout() async {
    if (!await confirmLogout(context)) return;
    await ref.read(authProvider).logout();
    ref.read(tokenProvider.notifier).set(null);
    if (mounted) Navigator.of(context).pushReplacementNamed(AppRouter.login);
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    final online = ref.watch(onlineProvider);
    final eventId = ref.watch(eventIdProvider);
    final gateId = ref.watch(gateIdProvider);
    EventModel? event;
    for (final e in _events) {
      if (e.id == eventId) {
        event = e;
        break;
      }
    }

    return Scaffold(
      appBar: AppBar(
        leading: Padding(
          padding: const EdgeInsets.all(6),
          child: Image.asset('assets/logo/akf_logo.png', fit: BoxFit.contain),
        ),
        title: Text(s.get('dashboard')),
        actions: [
          IconButton(tooltip: s.get('profile'), onPressed: () => Navigator.pushNamed(context, AppRouter.profile),
              icon: const Icon(Icons.person)),
          IconButton(tooltip: s.get('logout'), onPressed: _logout, icon: const Icon(Icons.logout)),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: online ? Colors.green.shade50 : Colors.red.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: online ? Colors.green : Colors.red),
              ),
              child: Row(children: [
                Icon(online ? Icons.cloud_done : Icons.cloud_off),
                const SizedBox(width: 10),
                Text(online ? '${s.get('server')}: OK' : s.get('offline')),
              ]),
            ),
            const SizedBox(height: 16),
            if (_loading) const Center(child: CircularProgressIndicator()),
            if (_error != null) Text(_error!, style: const TextStyle(color: Colors.red)),
            Card(
              child: ListTile(
                title: Text(s.get('event')),
                subtitle: Text(event?.title ?? '—'),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () => Navigator.pushNamed(context, AppRouter.events),
              ),
            ),
            Card(
              child: ListTile(
                title: Text(s.get('gate')),
                subtitle: Text(_gates.where((g) => g.id == gateId).map((g) => g.name).join(', ').isEmpty
                    ? '—'
                    : _gates.where((g) => g.id == gateId).map((g) => g.name).join(', ')),
                trailing: const Icon(Icons.arrow_forward_ios),
                onTap: () => Navigator.pushNamed(context, AppRouter.events),
              ),
            ),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: !online || eventId == 0
                  ? null
                  : () => Navigator.pushNamed(context, AppRouter.scanner),
              icon: const Icon(Icons.qr_code_scanner, size: 28),
              label: Text(s.get('scan'), style: const TextStyle(fontSize: 20)),
            ),
            const SizedBox(height: 20),
            if (_stats != null)
              Builder(builder: (context) {
                final st = _stats!;
                return Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: [
                    _Stat(label: s.get('tickets'), value: st.tickets),
                    _Stat(label: s.get('persons'), value: st.persons),
                    _Stat(label: s.get('checkedIn'), value: st.checkedIn),
                    _Stat(label: s.get('remaining'), value: st.remainingPersons),
                  ],
                );
              }),
            const SizedBox(height: 20),
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              childAspectRatio: 2.6,
              children: [
                _Nav(icon: Icons.history, label: s.get('history'), route: AppRouter.history),
                _Nav(icon: Icons.bar_chart, label: s.get('attendance'), route: AppRouter.attendance),
                _Nav(icon: Icons.how_to_reg, label: 'Attendance List / الحضور', route: AppRouter.attendanceList),
                if (ref.watch(canUndoProvider))
                  _Nav(icon: Icons.undo, label: s.get('supervisor'), route: AppRouter.supervisor),
                _Nav(icon: Icons.event_note, label: 'Session', route: AppRouter.sessions),
                _Nav(icon: Icons.person_add, label: 'New Visitor Registration', route: AppRouter.registrationQr),
                _Nav(icon: Icons.devices, label: s.get('device'), route: AppRouter.devices),
                _Nav(icon: Icons.settings, label: s.get('settings'), route: AppRouter.settings),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  const _Stat({required this.label, required this.value});
  final String label;
  final int value;
  @override
  Widget build(BuildContext context) => SizedBox(
        width: 150,
        child: Card(
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(label, style: Theme.of(context).textTheme.bodySmall),
              const SizedBox(height: 6),
              Text('$value', style: Theme.of(context).textTheme.headlineSmall),
            ]),
          ),
        ),
      );
}

class _Nav extends StatelessWidget {
  const _Nav({required this.icon, required this.label, required this.route});
  final IconData icon;
  final String label;
  final String route;
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.all(4),
        child: OutlinedButton.icon(
          onPressed: () => Navigator.pushNamed(context, route),
          icon: Icon(icon),
          label: Text(label),
        ),
      );
}
