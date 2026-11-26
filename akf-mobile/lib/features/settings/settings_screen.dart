import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../app/router.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';
import '../../core/utils/logout.dart';

class SettingsScreen extends ConsumerStatefulWidget {
  const SettingsScreen({super.key});

  @override
  ConsumerState<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends ConsumerState<SettingsScreen> {
  bool _sound = true;
  bool _vibration = true;
  final _languages = {'de': 'Deutsch', 'en': 'English', 'ar': 'العربية'};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) setState(() {
      _sound = prefs.getBool('akf_sound') ?? true;
      _vibration = prefs.getBool('akf_vibration') ?? true;
    });
  }

  Future<void> _setBool(String key, bool value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(key, value);
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    final locale = ref.watch(localeProvider);
    final online = ref.watch(onlineProvider);
    return Scaffold(
      appBar: AppBar(title: Text(s.get('settings'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          ListTile(
            title: Text(s.get('language')),
            trailing: DropdownButton<String>(
              value: locale.languageCode,
              items: _languages.entries
                  .map((e) => DropdownMenuItem(value: e.key, child: Text(e.value)))
                  .toList(),
              onChanged: (v) {
                if (v != null) ref.read(localeProvider.notifier).set(Locale(v));
              },
            ),
          ),
          SwitchListTile(
            title: Text(s.get('sound')),
            value: _sound,
            onChanged: (v) {
              setState(() => _sound = v);
              _setBool('akf_sound', v);
            },
          ),
          SwitchListTile(
            title: Text(s.get('vibration')),
            value: _vibration,
            onChanged: (v) {
              setState(() => _vibration = v);
              _setBool('akf_vibration', v);
            },
          ),
          ListTile(title: Text(s.get('server')), trailing: Text(online ? 'OK' : s.get('offline'))),
          const ListTile(title: Text('Version'), trailing: Text('1.0.0')),
          const SizedBox(height: 20),
          FilledButton.icon(
            onPressed: () async {
              if (!await confirmLogout(context)) return;
              await ref.read(authProvider).logout();
              ref.read(tokenProvider.notifier).set(null);
              if (mounted) Navigator.of(context).pushReplacementNamed(AppRouter.login);
            },
            icon: const Icon(Icons.logout),
            label: Text(s.get('logout')),
          ),
        ],
      ),
    );
  }
}
