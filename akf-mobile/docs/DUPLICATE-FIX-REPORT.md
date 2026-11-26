# تقرير إصلاح قراءة التذاكر المكرّرة — Yalla Events / AKF Gate Scanner

## السبب التقني
1. كان الخادم يمنع الدخول المزدوج فعلاً عبر `UPDATE ... WHERE id=? AND status='valid'` (atomic)، لكن استجابة `validate` للتذكرة المستخدمة كانت تُعيد `already_used` **بدون بيانات القراءة الأصلية**. لذلك كانت شاشة النتيجة لا تعرض الوقت/البوابة/الموظف.
2. كان التطبيق يعتمد على منع تكرار زمني محلي (4 ثوانٍ) وإيقاف الكاميرا أثناء فحص الاتصال —时许 causes عدم قراءة نفس الـ QR بعد الرجوع من شاشة النتيجة.
3. الرسائل لم تكن ثنائية اللغة كما طُلب.

## الإصلاح (أقل تغييرات)
- `class-akf-rest.php`: أُضيف حقل `checkin` إلى استجابة `validate` عندما تكون النتيجة `already_used` (نفس بيانات `used_fields` المستخدمة في `checkin`). لا تغيير في قاعدة البيانات ولا endpoint جديد.
- `scanner_screen.dart`: أُزال منع التكرار الزمني؛ يُمنع فقط طلب جديد أثناء طلب جاري (`_inFlight`). الكاميرا لا تُوقف بسبب فحص الاتصال، وتُعاد عند الرجوع للشاشة (`didChangeDependencies`).
- `strings.dart`: رسائل DE/EN/AR مطلوبة (`Check-in successful`, `Already checked in`, `Check-in unavailable`).
- `localization_test.dart`: تحديث التوقعات للرسائل الجديدة.
- (من الجولات السابقة المحفوظة): `main.dart` يستخدم `ErrorWidget.builder`، `app.dart` لا يستعيد الجلسة عند الإقلاع لتفادي فشل التخزين الآمن على الأجهزة.

## الملفات المعدّلة في هذه المهمة
- `wordpress/wp-content/plugins/yalla-engage/includes/class-akf-rest.php`
- `akf-mobile/lib/app/localization/strings.dart`
- `akf-mobile/lib/features/scanner/scanner_screen.dart`
- `akf-mobile/test/widget/localization_test.dart`
- (محفوظ من قبل) `akf-mobile/lib/main.dart`, `akf-mobile/lib/app/app.dart`

## اختبارات نُفّذت فعلياً (نتائج حقيقية)
| # | السيناريو | النتيجة |
|---|---|---|
| 1 | أول `checkin` لتذكرة جديدة (`AKF-2026-000010`) | `valid` — تسجيل واحد ✅ |
| 2 | `checkin` ثانٍ بنفس التوكن | `already_used` + بيانات `checkin` (الوقت/البوابة/الموظف) ✅ |
| 3 | `validate` بعد محاكاة إغلاق/فتح الكاميرا | `already_used` + بيانات القراءة الأصلية ✅ |
| 4 | تسجيل دخول جديد + بوابة مختلفة + `validate` | `already_used` + بيانات أصلية (gate=1) ✅ |
| 5 | توكن جلسة ثانٍ (هاتف آخر) + `validate` | `already_used` + بيانات أصلية ✅ |
| 6 | 20 طلب `checkin` متزامن لتذكرة واحدة (`AKF-2026-000011`) | `1 valid` + `19 already_used` ✅ |
| 7 | `undo` بحساب Gate Staff | `403 akf_app_forbidden` ✅ |
| 8 | طلب بدون توكن | `401 akf_app_unauthorized` ✅ |
| 9 | `flutter test` | **11/11 passed** ✅ |
| 10 | `flutter analyze` | 0 errors / 0 warnings ✅ |
| 11 | APK release arm64 | تم البناء والرفع ✅ |
| 12 | حساب Gate Staff يحاول undo | `403 akf_app_forbidden` ✅ |
| 13 | حساب Supervisor (دور `akf_gate_supervisor`) | login → `can_undo=true`، undo نجح، ثم `validate` أعاد `valid` ✅ |
| 14 | حساب Administrator | login → `can_undo=true`، `/me` و`/events` 200 ✅ |
| 15 | اختبار 1 — تذكرة جديدة `AKF-2026-000012` | أول checkin `valid` ✅ |
| 16 | اختبار 2 — نفس التذكرة مباشرة | `already_used` + وقت/بوابة/موظف ✅ |
| 17 | اختبار 3 — إغلاق/فتح الكاميرا (validate) | `already_used` + بيانات أصلية ✅ |
| 18 | اختبار 4 — إعادة تشغيل التطبيق (جلسة جديدة) | `already_used` + بيانات أصلية ✅ |
| 19 | اختبار 5 — هاتف آخر + بوابة مختلفة | `already_used` + بيانات أصلية (gate=1) ✅ |
| 20 | اختبار 6 — 20 طلب متزامن على `AKF-2026-000013` | `1 valid` + `19 already_used`، كلها HTTP 200 ✅ |
| 21 | قائمة الحضور قبل المسح (`AKF-2026-000014`) | `present=false`, `persons=2`, لا وقت دخول ✅ |
| 22 | مسح التذكرة مع `persons=2` | `valid`، سُجّل شخصان ✅ |
| 23 | قائمة الحضور بعد المسح | `present=true`, `gate=Haupteingang`, وقت الدخول مسجّل ✅ |
| 24 | `flutter test` بعد إضافة نموذج الحضور | **12/12 passed** ✅ |
| 25 | تصدير CSV (`format=csv`) | 200، UTF-8 BOM، 1140 bytes ✅ |
| 26 | تصدير Excel (`format=xls`) | 200، `application/vnd.ms-excel`، 2003 bytes ✅ |
| 27 | APK بعد إضافة التصدير والطباعة | تم البناء؛ `analyze` نظيف، `test` 12/12 ✅ |

## اختبارات لم تُنفّذ (لا يُدعى نجاحها)
- Code128 على جهاز حقيقي: التذاكر المعتمدة تطبع Code39 حالياً، لا يوجد Code128 مطبوع لاختباره.
- إغلاق/فتح الكاميرا فعلياً على الهاتف: اختُبر منطقياً عبر REST، وأُبلغ من المستخدم جزئياً؛ لم أُعد تشغيل الكاميرا آلياً على الجهاز.
- إعادة تشغيل الهاتف بالكامل، وانتقال فيزيائي بين بوابتين، وفتح التطبيق من هاتف ثالث فعلي.
- قطع الإنترنت فعلياً أثناء المسح: السلوك مُنفّذ في الكود (لا قبول offline) ولم يُختبر بقطع الشبكة على الجهاز.
- اختبار المشرف/المدير على جهاز فعلي (اختُبر عبر REST: Gate Staff ممنوع،
  Supervisor وAdmin يملكان undo، وكلاهما يرى الأحداث المصرّح بها).

## التوصيل
- APK: `builds/yalla-events-v9.apk`
- رابط التنزيل: `https://arab-kulturfestival.de/wp-json/akf/v1/app/apk`
- صفحة التنزيل: `https://arab-kulturfestival.de/akf-scan/download`

لم يُحذف أيacy وظيفة موجودة. لم تُجرَ تغييرات على WooCommerce أو PayPal.
