# Test Status — executed vs written

## Executed (Flutter/Dart) — PASSED
Toolchain installed during this session:
- Flutter 3.50.1 / Dart 3.14.0
- JDK 17 (Eclipse Temurin)
- Android SDK 36 + build-tools 36.0.0 + platform-tools + CMake 3.22.1

Commands actually run in `D:\akfbuild\akf-mobile` (path without spaces, needed
to avoid a Kotlin incremental-cache failure caused by `D:\oben code`):
- `flutter pub get` — success
- `flutter analyze` — **0 errors, 0 warnings**, 35 informational lint suggestions
- `flutter test` - **11/11 tests passed** (8 unit + 3 widget tests for DE/EN/AR result screens and RTL)
  - barcode parsing: raw token, full verification URL, hidden characters,
    short-code rejection, unique request IDs
  - API response parsing: valid ticket, already-used original check-in,
    ticket-vs-person count separation

## Executed (real WordPress backend) — PASSED
- Login 200; `me/gates/events/stats/history/online` 200 with real data.
- Atomic check-in: `valid` then `already_used` on a second scan.
- Supervisor undo restored `valid`.
- Full verification URL + dirty token accepted by the server.
- Web PWA scan decoded a real ticket and performed check-in on a device.
- **Concurrency test (20 simultaneous check-ins for one ticket):**
  `1 valid / 19 already_used` — only one admission recorded.
- **Wrong-event test:** `event=999` returned `wrong_event`.
- **Invalid-token test:** forged token returned `401 akf_app_unauthorized`.
- **Event-assignment test:** staff account sees only event 1; `stats?event=999`
  and `checkin` with `event=999` both returned `403 akf_app_bad_event`.
- **Idempotency test:** same `request_id` twice → first `valid`, second
  `valid` with `"replayed": true`; no second state change.
- **Session endpoint:** `GET /akf/v1/app/sessions?event=1` returned 200.

## Builds actually produced
- `builds/yalla-events-v1.0.0.apk` — 70,071,596 bytes (release APK, Yalla Events + logo + session + inactivity logout)
- `builds/yalla-events-v1.0.0.aab` — 62,717,574 bytes (release AAB, same final code)
- Backend executed: refresh rotation returned new tokens; replay of old refresh token returned `401 akf_app_bad_refresh`; device registration returned a 64-hex server-issued ID.

## NOT executed (must be done before production)
- Integration tests on a real Android/iOS device (widget tests for localized result screens already pass).
- Two-phone concurrency scan of one ticket.
- 20 simultaneous requests; duplicate request IDs; retry after timeout.
- Security tests: unauthorized staff/event, expired token, unauthorized undo,
  offline check-in attempt.
- iPhone/iPad camera, iOS build/signing (Windows host cannot build iOS).
- Flashlight, camera-permission denial, weak network, background/foreground,
  rotation, extended scanning.
- Print test of the approved ticket; Code128 is not printed today (Code39 is).
- DE/EN/AR visual RTL verification on device.

No test is reported as passed unless it was actually executed.
