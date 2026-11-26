# Yalla Events — Flutter source

Professional online-only admission app for Arab Kulturfestival.
Backend: the existing WordPress **AKF Tickets** layer inside `Yalla Engage`.
WordPress remains the only source of truth; this app contains no ticket database.

## State
- Stages 1–3 (backend inspection, API contract, backend endpoint/security work): **done** on the live site.
- Stages 4–12 (Flutter source, auth, events/gates, scanner, results, history, stats, supervisor, DE/EN/AR): **done**.
- Stages 13–14: `flutter analyze` clean, `flutter test` 8/8 passed, **release APK and AAB built**.
- Stage 15 partial: iOS build, device/concurrency/security tests and staging rehearsal still outstanding. Official logo is integrated.

See `docs/API.md`, `docs/BACKEND-GAPS.md`, `docs/TESTING.md`, `docs/SECURITY.md`.

## Quick orientation
```
lib/main.dart
lib/app/            app shell, router, theme, localization
lib/core/           api, auth, errors, storage, utils, models, providers
lib/features/       auth, dashboard, events, scanner, tickets, history,
                    attendance, supervisor, devices, settings, profile
test/unit/          written unit tests (not executed here)
docs/               API contract, gaps, installation, security, testing, user guide
```

## Hard requirements before running
1. Install Flutter SDK + JDK + Android SDK (or Xcode on macOS for iOS).
2. The official AKF logo is already in `assets/logo/akf_logo.png` (copied from the approved ticket design). No substitute was generated.
3. Keep the live backend plugins active: Yalla Engage (AKF layer), akf-gate-app, akf-gate-scanner.

No production-readiness claim is made.
