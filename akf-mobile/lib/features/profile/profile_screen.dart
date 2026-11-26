import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/state/app_state.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final s = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(s.get('profile'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(child: ListTile(title: Text('Name'), subtitle: Text(ref.watch(staffNameProvider)))),
          Card(child: ListTile(title: Text(s.get('gate')), subtitle: Text('${ref.watch(gateIdProvider)}'))),
          Card(child: ListTile(title: Text(s.get('supervisor')), subtitle: Text('${ref.watch(canUndoProvider)}'))),
        ],
      ),
    );
  }
}
