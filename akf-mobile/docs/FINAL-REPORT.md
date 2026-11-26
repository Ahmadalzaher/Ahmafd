# Yalla Events — Stage Report (2026-11-25)

## Stage 1 — Inspect real backend: DONE
Inspected local plugin sources:
- `class-akf-rest.php` (core admission endpoints)
- `class-app-rest.php` (mobile-facing endpoints)
- `class-akf-db.php` (11 `akf_*` tables)
- `class-akf-permissions.php`, `class-akf-events.php`,
  `class-akf-ticket-types.php`, `class-akf-reports.php`, `class-akf-pdf.php`.
No invented endpoints were used.

## Stage 2 — Document API contract: DONE
`docs/API.md` documents every endpoint used, headers, schemas, permissions,
error codes and idempotency behaviour. `docs/BACKEND-GAPS.md` records verified gaps.

## Stage 3 — Backend changes: DONE and deployed live
- `AKF_REST::checkin()` now verifies the selected event (`wrong_event`) and
  returns original check-in details for `already_used`.
- `AKF_REST::find_ticket()` accepts full verification URLs and dirty tokens.
- `akf_scan_logs` gained `request_id` and `device_id` columns (schema v1.0.1);
  check-in writes staff, event, gate, persons, request and device IDs to logs/audit.
- New app endpoints: `events`, `stats`, `history`, `online`.
- Live verification: login 200; `me/gates/events/stats/history/online` all 200
  with real data; two consecutive check-ins produced `valid` then `already_used`.

## Stages 4–12 — Flutter source: WRITTEN, NOT COMPILED
36 source/doc files created in `D:\oben code\akf-mobile`. Clean architecture with
Riverpod, Dio, flutter_secure_storage, mobile_scanner (QR + Code128), strict
online mode, request IDs, debounce, result screens, history, stats, supervisor
undo, settings and full DE/EN/AR (RTL) strings. Static import/balance check passed
(no missing relative imports). No Flutter analyzer was available.

## Stages 13–14 — executed after toolchain installation
- Installed Flutter 3.50.1 / Dart 3.14.0, JDK 17, Android SDK 36 + CMake.
- `flutter pub get` succeeded.
- `flutter analyze`: **0 errors, 0 warnings** (35 informational lints).
- `flutter test`: **11/11 passed** (8 unit + 3 widget tests for DE/EN/AR and RTL).
- Device debugging: isolated and fixed a launch failure caused by secure-storage
  session restore; final working APK confirmed by the project owner on device.
- Role tests executed: Gate Staff undo forbidden (403), Supervisor undo allowed
  and audited, Administrator profile/events available.
- `flutter build apk --release`: **APK built** (69,591,144 bytes).
- `flutter build appbundle --release`: **AAB built** (62,288,493 bytes).
- Build had to run from `D:\akfbuild\akf-mobile` (no spaces) — the `D:\oben code`
  path caused Kotlin incremental-cache failures.
- Real backend security/concurrency checks executed: 20 simultaneous check-ins
  on one ticket → exactly 1 valid + 19 already_used; wrong event → `wrong_event`;
  forged token → `401 unauthorized`.
- Refresh rotation, server-issued device registration and per-staff event
  assignments implemented and verified live. Flutter now stores and uses both
  tokens and sends the device ID with scans; event authorization is enforced on
  `events/stats/history/validate/checkin` (403 for unassigned events).
- Session ownership checks, session listing, idempotent replay handling and a
  Flutter session selector added. Live idempotency test: first `valid`, second
  replayed `valid`.
- APK/AAB rebuilt (70,071,596 / 62,714,154 bytes); plugin ZIPs rebuilt after the
  latest PHP changes (core 3,336,052; gate app 86,207; gate scanner 65,118 bytes).

## Stage 15 — remaining blockers
| Blocker | Status |
|---|---|
| iOS build | Windows host — Xcode/CocoaPods unavailable, no IPA |
| Official logo | DONE — copied from approved ticket design into `assets/logo/akf_logo.png` and used for splash/login/dashboard/launcher icons |
| Real-device tests | Camera/torch/permission/weak network/lifecycle not executed |
| Concurrency/security tests | Written requirement, not executed on devices |
| Print test | Not executed; Code128 not printed on approved tickets (Code39) |
| Staging rehearsal | No staging environment |
| Backend gaps | Event assignments, device registration, refresh tokens, session enforcement |

## Production readiness
NOT declared. Source compiles, analyzer is clean, unit tests pass, and an
installable APK + AAB exist. Production sign-off still requires device/concurrency/
security tests, iOS build on macOS, staging rehearsal, and the
documented backend gaps.
