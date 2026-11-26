import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../app/router.dart';
import '../../core/errors/api_exception.dart';
import '../../core/providers.dart';
import '../../core/state/app_state.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _user = TextEditingController();
  final _pass = TextEditingController();
  bool _busy = false;
  String? _error;

  @override
  void dispose() {
    _user.dispose();
    _pass.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final s = ref.read(stringsProvider);
    if (_user.text.trim().isEmpty || _pass.text.isEmpty) {
      setState(() => _error = '${s.get('username')} / ${s.get('password')}');
      return;
    }
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      final profile = await ref.read(authProvider).login(
            username: _user.text.trim(),
            password: _pass.text,
            gate: ref.read(gateIdProvider),
          );
      ref.read(tokenProvider.notifier).set(ref.read(apiProvider).currentToken);
      ref.read(staffNameProvider.notifier).set(profile.name);
      ref.read(canUndoProvider.notifier).set(profile.canUndo);
      ref.read(gateIdProvider.notifier).set(profile.gate);
      if (mounted) Navigator.of(context).pushReplacementNamed(AppRouter.dashboard);
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (e) {
      setState(() => _error = 'Login failed');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = ref.watch(stringsProvider);
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Image.asset('assets/logo/akf_logo.png', height: 88, fit: BoxFit.contain),
                  const SizedBox(height: 16),
                  Text(s.get('appName'), textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 32),
                  TextField(controller: _user, decoration: InputDecoration(labelText: s.get('username'))),
                  const SizedBox(height: 16),
                  TextField(controller: _pass, obscureText: true,
                      decoration: InputDecoration(labelText: s.get('password'))),
                  if (_error != null) ...[
                    const SizedBox(height: 12),
                    Text(_error!, style: const TextStyle(color: Colors.red)),
                  ],
                  const SizedBox(height: 24),
                  FilledButton(onPressed: _busy ? null : _submit,
                      child: _busy ? const CircularProgressIndicator() : Text(s.get('login'))),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
