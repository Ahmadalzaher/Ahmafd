# تقرير حسابات الزوار — AKF

## ما نُفّذ
- جدول `akf_visitors` (بريد فريد، Google sub فريد، بيانات الموافقة، تاريخ last registration، رمز حساب).
- ربط `akf_selfreg.visitor_id` بم ملف الزائر الدائم.
- عند نجاح التحقق (`akf_reg_verified`) يتم إنشاء أو تحديث ملف الزائر تلقائياً (hook).
- نقاط API:
  - `/akf/v1/visitor/me`, `/tickets`, `/export`, `/update`, `/delete-request`, `/ticket/resend`
  - `/akf/v1/visitor/admin/list`
  - `/akf/v1/visitor/google` verify عبر Google tokeninfo + Client ID من إعدادات الأدمن.
- صفحة حساب الزائر `/akf-visitor/?token=...`.
- صفحة أدمن **AKF Visitor Management** مع حقل Google OAuth Client ID.
- إلغاء جلسات الموظفين: `/akf/v1/app/admin/revoke-session`.
- إعادة إرسال التذكرة بدون创建新 تذكرة + تسجيل حالة البريد في `akf_email_logs`.

## اختبارات نُفّذت فعلياً
| # | السيناريو | النتيجة |
|---|---|---|
| 1 | تسجيل زائر جديد بالبريد | `pending` ✅ |
| 2 | التحقق → تذكرة `AKF-2026-000020` + PDF | `confirmed` ✅ |
| 3 | إنشاء ملف زائر دائم | visitor id=1 ✅ |
| 4 | نفس الزائر يسجّل لفعالية ثانية | `AKF-2026-000021` ✅ |
| 5 | عدد ملفات الزائر بعد التسجيلين | 1 (لا تكرار) ✅ |
| 6 | تحديث `last_registration_at` | نعم ✅ |
| 7 | `/visitor/me` بدون توكن | 401 ✅ |
| 8 | الأدمن يلغي جلسة موظف | 200، التوكن القديم 401، إعادة دخول 200 ✅ |
| 9 | Google Sign-In بدون Client ID | 503 `akf_google_not_configured` ✅ |
| 10 | `flutter analyze` / `flutter test` | نظيف / 12/12 ✅ |
| 11 | اختبار كامل: تسجيل `fullflow@example.de` فعالية 1 | `AKF-2026-000022`، persons=2 ✅ |
| 12 | نفس الزائر لفعالية 4 | `AKF-2026-000023`، ملف واحد ✅ |
| 13 | مسح التذكرة 22 | `valid` ثم `already_used` ✅ |
| 14 | 20 طلب متزامن للتذكرة 23 | 1 valid + 19 already_used ✅ |
| 15 | تسجيل بدون تحقق (`novhook@example.de`) | تذكرة `AKF-2026-000025` + ملف زائر جديد ✅ |
| 16 | حذف ملف الرمز المؤقت بعد الاختبار | 404 ✅ |

## لم تُنفّذ
- Google Sign-In حقيقي (لا توجد بيانات اعتماد Google Client ID/Secret).
- فتح صفحة حساب الزائر بتوكن حقيقي على جهاز (التوكن لا يُعرض في قائمة الأدمن).
- استلام البريد من صندوق حقيقي، حذف البيانات الفعلي (سُجّل الطلب فقط)، وتصدير GDPR_KML على جهاز.

## ملاحظة نشر
تم نشر الإضافة كاملة عبر ZIP باسم مجلد جديد `yalla-engage-v2` بعد تعذّر حفظ محرر الإضافات، ثم أُوقفت النسخة القديمة وحُذفت. لا توجد نسختان نشطتان الآن.

لا يُعلن اكتمال Google Sign-In أو GDPR دون بيانات الاعتماد والاختبار الفعلي.
