# تقرير تسجيل الدخول الدائم — Yalla Events

## التغييرات
- `app/app.dart`: استعادة الجلسة بعد أول إطار عبر `addPostFrameCallback`، قراءة التوكن من `flutter_secure_storage`، جلب `/me`، وت application de los providers. عند 401/403 يتم تنظيف الجلسة والعودة للدخول؛ عند تعذر الخادم تُحفظ الجلسة ويُعرض وضع Offline مع Retry.
- `core/api/api_client.dart` + `core/auth/auth_repository.dart`: تخزين access/refresh في secure storage، وتجديد تلقائي عند 401.
- `core/utils/logout.dart` + Settings + Dashboard: تأكيد «Are you sure you want to log out?» ثم إلغاء الجلسة على الخادم ومسح التخزين المحلي.

## ما نُفّذ فعلياً
| الاختبار | النتيجة |
|---|---|
| أول تسجيل دخول (gate/supervisor/admin) | 200، access + refresh tokens ✅ |
| `/me` بالتوكن | 200، اسم/صلاحيات/بوابة ✅ |
| refresh token | 200، توكنان جديدان، دوران صحيح ✅ |
| إعادة refresh القديم | 401 `akf_app_bad_refresh` ✅ |
| تسجيل خروج محلي/خادم | يمسح التخزين ويعيد شاشة الدخول (منطق مطبّق) ✅ |
| `flutter analyze` | 0 errors / 0 warnings ✅ |
| `flutter test` | 12/12 passed ✅ |
| APK arm64 | مبني ومرفوع ✅ |

## لم تُنفّذ على الأجهزة
- إغلاق/فتح التطبيق واستعادة الجلسة فعلياً (المنطق مطبّق لكن لا جهاز اختبار).
- إعادة تشغيل الهاتف.
- انتهاء access token تلقائياً على الجهاز (اختُبر عبر API).
- قطع إنترنت فعلي أثناء المسح.
- إلغاء جلسة الموظف من الأدمن: نقطة API مطلوبة لم تُنشر لأن محرر الإضافات لم يحفظ الملف؛ السلوك المتاح حالياً هو أن أي 401/403 من الخادم يعيد التطبيق لشاشة الدخول تلقائياً. لا يُدعى نجاح اختبار admin revoke.

## الأمان
- لا كلمات مرور مخزّنة؛ التوكنات في `flutter_secure_storage` (Keystore/Keychain).
- كل الطلبات HTTPS.
- تسجيل الخروج لا يحذف أي بيانات خادم.

APK: `builds/yalla-events-v14.apk` — https://arab-kulturfestival.de/wp-json/akf/v1/app/apk
