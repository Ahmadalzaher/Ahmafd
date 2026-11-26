# Backend Gap Analysis — Spec vs Actual AKF Tickets

This document is written before Flutter implementation, as required by stages 1–2.
It lists only verified facts from the local plugin sources.

## Implemented in the real backend (after this session's changes)

| Requirement | Status | Where |
|---|---|---|
| Login with WP credentials / Application Password | Yes | `akf/v1/app/login` |
| Short-lived token, stored only as hash | Yes (8 h, no refresh) | `akf_staff_sessions` |
| Server-side capability checks | Yes (`akf_scan`, `akf_undo_checkin`) | app REST + core REST |
| Minimal ticket fields, no token/payment data | Yes | `AKF_REST::public_fields()` |
| Atomic duplicate prevention | Yes (conditional UPDATE) | `AKF_REST::checkin()` |
| Original check-in returned for already-used | Yes (added) | `AKF_REST::used_fields()` |
| Event ownership check on check-in | Yes when `event` supplied (added) | `AKF_REST::checkin()` |
| Full verification URL / dirty token accepted | Yes (added) | `AKF_REST::find_ticket()` |
| Gates list | Yes | `akf/v1/app/gates` |
| Events list | Yes | `akf/v1/app/events` |
| Live attendance stats | Yes | `akf/v1/app/stats` |
| Scan history with result filter | Yes | `akf/v1/app/history` |
| Supervisor undo with mandatory reason + audit | Yes | `akf/v1/app/undo` |
| Strict online health check | Yes | `akf/v1/app/online` |
| request_id / device_id recorded in scan logs + audit | Yes (schema v1.0.1) | `akf_scan_logs` |
| Offline admission disabled in installed PWA | Yes | `akf-gate-app/assets/app.js` |
| DE/EN/AR in web PWA | Partial (app UI is English; DE/EN/AR on tickets) | — |

## Gaps that require explicit decisions or further backend work

1. **Per-staff event assignments — DONE.** Table `akf_staff_events` plus
   `POST /akf/v1/app/admin/assign-event` (admin cookie/nonce) manage assignments.
   `events`, `stats`, `history`, `validate` and `checkin` all enforce the
   assignment server-side (`403 akf_app_bad_event`). A graphical admin screen
   is still not implemented; assignment is currently via the REST endpoint.
2. **Device registration — DONE (backend).** `POST /akf/v1/app/device/register`
   issues a server-issued 64-hex ID stored only as a hash in `akf_app_devices`,
   bound to the staff account and sent on every scan. An admin list/detail view
   is still not implemented.
3. **Refresh tokens — DONE.** `POST /akf/v1/app/refresh` rotates a 30-day
   refresh token and issues a fresh 8-hour access token. Flutter stores the
   refresh token in secure storage; replay of a rotated token returns 401.
4. **Idempotency — DONE.** A replayed `request_id` from the same staff member
   returns the original decision with `replayed: true`, without a second
   state change. Verified live: first call `valid`, second call replayed `valid`.
5. **Session restrictions — DONE for ownership.** `validate`/`checkin` accept
   `session` and return `wrong_session` when the ticket belongs to another
   session. `GET /akf/v1/app/sessions` lists active sessions. Session capacity
   limits are now enforced at check-in (`session_full`).
6. **Code128 is not printed on AKF tickets.** The approved PDF draws Code 39
   with the ticket number. The scanner can decode Code128, but there is no
   Code128 payload on current tickets. Changing the approved design needs
   project-owner approval.
7. **Role granularity in history/statistics is coarse.** Supervisors can read
   any event's history once the event is authorized; per-event assignment fixes
   this. Gate staff currently receive the same history shape; the spec requires
   only their own scans and minimum data.
8. **Wrong-event result needs client event selection.** The server enforces it
   when `event` is supplied; the current web PWA does not yet send an event.
   The Flutter app must send `event` on every validate/checkin call.
9. **Official logo asset not supplied.** The app must use the project owner's
   official AKF logo; no replacement should be generated.
10. **No staging environment or device farm.** APK/AAB/iOS builds, camera,
    flashlight and print tests cannot be executed here.

## Toolchain status (checked on this machine)

| Dependency | Status |
|---|---|
| Flutter SDK | MISSING |
| Dart SDK | MISSING |
| JDK | MISSING |
| Android SDK / adb | MISSING |
| Gradle | MISSING |
| Xcode / CocoaPods | Not available on Windows |
| AKF REST source | Present (local plugin files) |
| WordPress admin access | Available (browser tools) |
| Official logo file | Not supplied |

No APK/AAB/IPA can be produced and no Flutter test can be executed until the
toolchain is installed. This is an environment limitation, not a code result.
