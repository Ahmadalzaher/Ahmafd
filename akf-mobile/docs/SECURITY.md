# Security Notes

## Implemented in the app source
- HTTPS-only base URL (`https://arab-kulturfestival.de/wp-json/`).
- Token stored in `flutter_secure_storage` (EncryptedSharedPreferences /
  Keychain first unlock). Passwords are never stored.
- `X-AKF-Token` header on every authenticated request; Dio interceptor never logs it.
- Strict online mode: scanner disables on failed server health check; no offline queue.
- Unique request ID per scan (`uuid` v4); in-flight lock and 4-second debounce.
- Server result is authoritative; no green state without server confirmation.
- Supervisor undo requires server-verified `akf_undo_checkin`.
- No raw ticket token or payment data is displayed in scan results.

## Implemented on the server
- Token stored only as SHA-256 in `akf_staff_sessions`, 8-hour expiry.
- Login rate limit, capability checks per request (`akf_scan`, `akf_undo_checkin`).
- Conditional atomic UPDATE for check-in; only one concurrent admission wins.
- `wrong_event` enforced when `event` is supplied.
- `request_id`, `device_id`, staff, gate, event and persons written to audit logs.
- Audit helper strips keys matching token/secret/password/card.

## Known limitations (see BACKEND-GAPS.md)
- Refresh-token rotation is implemented (30-day refresh, 8-hour access, replay rejected).
- Per-staff event assignments are stored and enforced on every admission request;
  the admin assignment UI is still REST-only.
- Device IDs are now server-issued and hash-verified; no admin device list yet.
- `request_id` replays return the original decision (`replayed: true`).
- Session ownership and capacity are enforced at scan time.
- Automatic logout after 15 minutes of inactivity (client-side, plus server
  token expiry).
- No app-side inactivity auto-logout yet (server token expiry still applies).
- Official logo is not bundled; must be supplied by the project owner.
