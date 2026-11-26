# AKF Gate App — Mobile Quick Guide

**Install / open:** https://arab-kulturfestival.de/akf-scan/

This is an installable web app (PWA). It connects directly to the AKF REST API in Yalla Engage; it does not require a separate database or app store account.

## Install on a phone
- **Android (Chrome):** open the link → menu ⋮ → *Install app* or *Add to Home screen*.
- **iPhone (Safari):** open the link → Share → *Add to Home Screen*.
- The icon “AKF Gate” then opens full-screen.

## Sign in
- Username: the staff WordPress account (e.g. `akf.gate1`).
- Password: that account's **Application Password** (WordPress Users → Profile → Application Passwords).
- The app never stores the password. It stores only a short-lived session token (8 hours) in browser local storage; the server keeps only its SHA-256 hash.

After sign-in:
1. Choose the gate from the dropdown.
2. Press **Start camera** and point at the ticket QR/barcode, or paste the token into **Manual token**.
3. Press **Check ticket**. The server validates, then performs an atomic check-in. A second scan returns *Already used*.
4. Supervisors may press **Undo check-in** and must enter a reason.

## Offline behaviour (strict)
- If a real network failure occurs, **Check ticket** and **Undo** are disabled and a warning with a **Retry** button is shown. The app does not trust the browser's `navigator.onLine` flag (unreliable in installed PWAs); it always attempts the server call and clears the warning as soon as the server answers.
- No admission decision is ever made locally; the service worker does not cache API responses.
- The app shell (UI, jsQR library) is cached so it still opens offline, but scanning stays disabled until the server is reachable again.

## Camera rehearsal
Open this on a computer or a second phone (large QR target):
`https://arab-kulturfestival.de/akf-scan/test-qr/?code=<64-character-ticket-token>`

Then on the gate phone: sign in — the camera starts automatically — and point at the screen. It shows *QR erkannt — wird geprüft …* and then the admission result. The app (and now also the server) extracts the token from the verification URL inside the QR and performs the atomic check-in. The target URL is `noindex` and the token must be supplied in the link.

## Roles
- `AKF Gate Staff`: scan / check in.
- `AKF Gate Supervisor`: scan + undo.
- Administrators also have both permissions.

## Troubleshooting
- **“Too many attempts. Try again later.”** → this counter is cleared automatically when the correct username + Application Password are submitted, so simply try again. If it persists, wait up to 15 minutes; wrong passwords only are throttled (20 per 15 minutes per IP).
- **“Session expired”** → sign in again; tokens expire after 8 hours.
- **“Invalid credentials or insufficient permissions”** → confirm the username and that the Application Password was copied with the spaces exactly as shown; confirm the role is Gate Staff/Supervisor.
- **Camera does not start** → allow camera permission in the browser settings; HTTPS is required (the live site is HTTPS).
- **Gate list empty** → an administrator must add a gate under Yalla Engage → AKF Gates.
- **Ticket not found** → the ticket was not issued into the AKF tickets table (free RSVPs in the old form are not AKF tickets). Use Yalla Engage → AKF Tickets for invitations or the verified WooCommerce paid flow.

## Security notes
- No raw token is returned by any API response or written to audit logs.
- Login attempts are rate-limited (10 per 15 minutes per IP).
- Session rows in `akf_staff_sessions` expire and are never deleted by the app.
