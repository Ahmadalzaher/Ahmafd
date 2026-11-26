# Installation and build

## Toolchain (installed during this session)
- Flutter 3.50.1 / Dart 3.14.0 at `D:\oben code\tools\flutter`
- JDK 17 (Eclipse Temurin)
- Android SDK 36, build-tools 36.0.0, platform-tools, CMake 3.22.1
  at `C:\Users\ahmad\AppData\Local\Android\Sdk`

## Produced artifacts
- `builds/yalla-events-v1.0.0-arm64.apk` — recommended for Samsung/modern ARM64
  phones. The universal APK showed a gray screen on a Galaxy S22 Ultra; the
  ARM64-only build (`--target-platform android-arm64`) works.
- `builds/yalla-events-v1.0.0.apk` — universal build (all ABIs); currently
  signed with the default Flutter debug keystore — configure a production
  keystore before Play submission.
- `builds/yalla-events-v1.0.0.aab` (Google Play format; also needs production
  signing)
- Display name: **Yalla Events**; Android package `de.arabkulturfestival.yalla_events`.
- Startup fixes after isolation testing: `main()` uses plain
  `runApp(const ProviderScope(child: AkfApp()))`; the original 1989px logo PNG
  caused a gray launch screen and was replaced with a clean 1000px version
  (`assets/logo/akf_logo.png`); the ARM64-only build is the tested, working one.
- **Auto session restore is disabled** in the shipped build. Calling secure
  storage at startup caused a gray/white screen and a
  `Null check operator used on a null value` build error on the test devices.
  Staff sign in manually each time. This is a known limitation, not a security
  downgrade: tokens are still stored securely and expire server-side.
- Working Android package: `de.arabkulturfestival.yalla_events_v5` (delivered as
  `yalla-events-v6.apk` / `yalla-events-v6.aab`).
- Staging APK download URL:
  `https://arab-kulturfestival.de/wp-json/akf/v1/app/apk`, with an HTML landing
  page at `https://arab-kulturfestival.de/akf-scan/download`.
- Official AKF logo from the approved ticket design is used on the splash,
  login screen, dashboard and launcher icons.

## Rebuild
Build from a path **without spaces** (e.g. `D:\akfbuild\akf-mobile`); the
original `D:\oben code` path caused Kotlin incremental-cache failures.
1. `flutter pub get`
2. `flutter analyze` — 0 errors/warnings expected.
3. `flutter test` — 8/8 expected.
4. `flutter build apk --release` and `flutter build appbundle --release`.
5. iOS: still requires macOS + Xcode + CocoaPods. Not attempted here.

## Still missing before production
- Official AKF logo artwork (place in `assets/logo/`; no substitute generated).
- Real device tests, concurrency/security tests, staging rehearsal.
- Server-side gaps in `docs/BACKEND-GAPS.md` (event assignments, device
  registration, refresh tokens, session enforcement).

## Backend prerequisites
The live WordPress site must have these active:
- Yalla Engage (AKF layer) version with `class-akf-rest.php` including event
  checks and verification-URL parsing.
- akf-gate-app plugin with `/akf/v1/app/*` routes.
- akf-gate-scanner (web fallback) optional.

See `API.md` for the exact contract and `BACKEND-GAPS.md` for known limitations.
