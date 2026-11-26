# Yalla Events — Actual Backend API Contract

Base URL: `https://arab-kulturfestival.de/wp-json/`

Source of truth: WordPress plugin **Yalla Engage (AKF layer)** + companion plugins
`akf-gate-app` (mobile-facing routes) and `akf-gate-scanner` (web scanner).

The Flutter app must use only the `/akf/v1/app/*` routes below. The `/akf/v1/*`
core routes require a logged-in WordPress cookie + REST nonce and are therefore
not suitable for a standalone mobile app. The app routes resolve the staff
session token, verify the capability on the server, then temporarily set that
user as current user and delegate to the core methods.

## Authentication

| Item | Value |
|---|---|
| Login | `POST /akf/v1/app/login` with WordPress username + Application Password |
| Token | Random 64-hex string, returned once |
| Storage | Client secure storage only; server stores `sha256(token)` in `akf_staff_sessions` |
| Header | `X-AKF-Token: <token>` on every authenticated request |
| Lifetime | 480 minutes (8 h). Refresh tokens are **not** implemented — see gaps. |
| Rate limit | 20 login attempts / 15 min / IP; correct credentials clear the counter |

### `POST /akf/v1/app/login`
Request: `{ "username": "...", "password": "...", "gate": 0 }`
Response 200:
```json
{ "token": "<64 hex>", "name": "akf.gate1", "can_undo": false, "expires_in": 28800 }
```
Errors: `401 akf_app_bad_login`, `429 akf_app_throttled`, `500 akf_app_session_failed`.

### `POST /akf/v1/app/refresh`
Request: `{ "refresh_token": "<96 hex>" }`
Response 200: `{ "token": "<new access token>", "refresh_token": "<new refresh token>", "expires_in": 28800 }`
The old refresh token is atomically rotated and replay returns
`401 akf_app_bad_refresh`.

### `POST /akf/v1/app/device/register`
Headers: `X-AKF-Token`. Request: `{ "label": "AKF Gate App" }`
Response: `{ "device_id": "<64 hex>" }` — server-issued, stored only as a hash,
bound to the staff account and sent as `device_id` on every scan.

### `POST /akf/v1/app/admin/assign-event`
WordPress admin cookie + REST nonce (not a staff token). Requires
`akf_manage_events`. Request:
`{ "user_id": 4, "event_id": 1, "action": "assign"|"revoke" }`
Response: `{ "ok": true, "assigned": true }`.
Staff accounts only receive events assigned to them; admins receive all active
events. `stats`, `history`, `validate` and `checkin` enforce the same assignment
server-side (`403 akf_app_bad_event`).

### `POST /akf/v1/app/logout`
Headers: `X-AKF-Token`. Revokes all refresh tokens and expires the current access
session. Response: `{ "ok": true }`.

### `GET /akf/v1/app/me`
Response: `{ "ok": true, "name": "...", "gate": 1, "can_undo": false }`
Errors: `401 akf_app_unauthorized`, `403 akf_app_forbidden`.

### `GET /akf/v1/app/online`
Response: `{ "online": true, "time": "2026-11-25 10:00:00" }`
Used by strict online mode before enabling scanning.

## Staff context

### `GET /akf/v1/app/gates`
Response: `{ "gates": [ { "id": 1, "name": "Haupteingang" } ] }`

### `GET /akf/v1/app/events`
Response: `{ "events": [ { "id": 1, "title": "...", "date": "2026-12-04", "start_time": "18:00", "end_time": "21:00", "venue": "...", "address": "...", "capacity": 0, "status": "published" } ] }`
Only `published` / `sales_open` events are returned.

## Admission

### `POST /akf/v1/app/validate`
Request:
```json
{ "code": "<raw token OR full verification URL>", "event": 1, "gate": 1, "request_id": "uuid", "device_id": "server-issued-id" }
```
Response:
```json
{
  "result": "valid|already_used|invalid|cancelled|refunded|wrong_event|error",
  "message": "...",
  "ticket": { "number":"AKF-2026-000007", "name":"Guest", "persons":2, "event":1, "type":1, "status":"valid", "checked_in":false },
  "checkin": { "number":"...", "checked_in":"...", "gate":1, "staff":"...", "persons":2 }
}
```
`checkin` is present only for `already_used`. No raw token or payment data is returned.
Validation does not change state.

### `POST /akf/v1/app/checkin`
Same request schema. Atomic state transition `valid → used` via conditional
`UPDATE ... WHERE id = ? AND status = 'valid'`. Only one concurrent caller wins.
Losers receive `already_used` with the original check-in details.
Response on success: `result: "valid"`, `message`, minimal `ticket` fields.
Server audit records: staff ID, event, gate, persons, request_id, device_id.

### `POST /akf/v1/app/undo`
Request: `{ "code": "...", "reason": "mandatory text" }`
Requires `akf_undo_checkin`. Conditional `used → valid`, audit entry with reason,
supervisor identity and previous state. Historical scan rows are never deleted.

## Reporting

### `GET /akf/v1/app/sessions?event=1`
Active sessions for an authorized event: `id`, `title`, `starts_at`, `ends_at`,
`venue`, `capacity`.

### `GET /akf/v1/app/attendance?event=1`
Per-ticket attendance list (max 500): `number`, `name`, `persons`, `status`,
`checked_in`, `gate`, `present`. Updated by server check-in; no local state.

### `GET /akf/v1/app/attendance/export?event=1&format=csv|xls`
Exports the same attendance list as UTF-8 CSV (Excel opens it) or an
Excel-compatible `.xls` HTML table. Requires `X-AKF-Token` and event assignment.

### `GET /akf/v1/app/stats?event=1`
```json
{
  "event":1, "tickets":10, "persons":14, "checked_in":3, "admitted_persons":5,
  "remaining_persons":9, "percent":36,
  "by_gate":[{"name":"Haupteingang","total":3}],
  "by_hour":[{"hour":"2026-12-04 18:00:00","total":3}]
}
```

### `GET /akf/v1/app/history?event=1&filter=valid&limit=50`
Filters: `valid`, `already_used`, `invalid`, `refunded`, `cancelled`. Max limit 100.
Rows: `created_at`, `result`, `gate_id`, `user_id`, `persons_admitted`,
`ticket_number`, `staff_name`.

## Core routes (reference only — require WP cookie/nonce)
`POST /akf/v1/validate`, `POST /akf/v1/checkin`, `POST /akf/v1/undo`, `GET /akf/v1/online`.
These are used internally by the app routes and by the web scanner.

## Session and idempotency behaviour
- `validate` and `checkin` accept an optional `session` ID. A ticket bound to a
  different session returns `wrong_session` before any state change. If the
  selected session has a capacity, admission is refused with `session_full`
  once admitted persons would exceed it.
- `request_id` is recorded on every scan. A replayed request with the same ID
  from the same staff member returns its original decision with
  `"replayed": true` and never performs a second admission.

## Barcode payload
The printed QR encodes a verification URL:
`https://arab-kulturfestival.de/?akf_verify=1&code=<64-hex-token>`.
The server extracts `code` from a URL and strips non-hex characters, so both the
raw token and the full URL are accepted. The approved PDF currently draws a
**Code 39** vertical barcode from the ticket number — Code 128 is accepted by
the scanner, but AKF-issued tickets do not print Code 128 today (gap).
