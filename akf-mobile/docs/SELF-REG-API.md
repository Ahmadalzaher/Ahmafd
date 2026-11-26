# Self-Registration REST API — `akf/v1/reg`

Base: `https://arab-kulturfestival.de/wp-json/akf/v1/reg/`
All endpoints are HTTPS only. No endpoint returns personal data in a QR code.

## Public
### `GET /event?token=<64-hex>`
200: `{event_id,title,date,start_time,end_time,venue,address,languages,max_persons}`
403: `akf_reg_invalid_link` when link is disabled/expired/invalid.

### `POST /start`
Body: `{token,name,email,persons,lang,consent}`
200: `{status:"pending"|"registered",message}`
400: `akf_reg_bad_data|akf_reg_consent`; 403: `akf_reg_invalid_link|akf_reg_full`; 429: `akf_reg_rate`.
Same email + event returns pending (resends verification) or registered — never a second ticket.

### `GET /verify?code=<64-hex>`
200: `{status:"confirmed",number,name,persons,ticket_url}`
400: `akf_reg_bad_code` for expired, replayed or invalid verification links.
Creates the AKF ticket atomically once, generates the approved PDF, emails it.

### `POST /resend`
Body: `{token,email}`; 200 pending; 429 after 3/hour.

## Staff (mobile app)
### `POST /staff/qr`
Headers: `X-AKF-Token` (gate staff session). Body: `{event}`
200: `{event_id,title,date,venue,status,qr_data,counts,confirmed,pending,available,recent}`
`qr_data` is `data:image/png;base64,...`. 401 invalid session, 403 unassigned event or registration closed.

## Admin (WordPress cookie/nonce or Application Password)
### `POST /admin/config`
`{event_id,enabled,max_registrations,max_persons,opens_at,closes_at,languages,require_verification}`
Creates/reuses the free `Self-Registration` ticket type and returns the registration URL.

### `GET /admin/list?event=ID`
Returns up to 500 registrations with status, verification time and ticket ID.

QR tokens are stored raw only for the event config (to generate the QR) and as SHA-256 for lookup.
Verification tokens are stored only as SHA-256 and expire after 24 hours.
