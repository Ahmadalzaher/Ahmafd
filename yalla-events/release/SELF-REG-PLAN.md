# خطة تسجيل الحضور الذاتي عبر QR — AKF

## فحص البنية الحالية (تم)
- `class-yen-db.php`: جدول `yena_registrations` فيه `UNIQUE KEY event_email (event_id,email)` — يمنع التكرار، لكنه لا ينشئ تذاكر بوابة.
- `class-akf-db.php`: جداول `akf_tickets`, `akf_ticket_types`, `akf_events`, `akf_scan_logs`. التذاكر تُصدر عبر `AKF_Tickets::issue()` فقط.
- `AKF_PDF::generate()` يستخدم التصميم المعتمد دون تغيير.
- `AKF_REST::validate/checkin` يعمل على `akf_tickets` (نفس ما يستخدمه الماسح) — لا حاجة لتعديل الماسح.
- لا يوجد حالياً تدفق تحقق بريد إلكتروني.

## النهج الآمن (أقل تغييرات)
1. **جداول جديدة** في `AKF_DB` (ترقية غير مدمرة):
   - `akf_selfreg_events`: إعدادات لكل فعالية + رمز QR فريد + حالة.
   - `akf_selfreg`: تسجيلات معلّقة/مؤكدة + رمز تحقق مشفر + انتهاء صلاحية.
2. **Lasers REST API** تحت `akf/v1/reg`:
   - `GET /event?token=…` — معلومات فعالية عامة فقط.
   - `POST /start` — إنشاء تسجيل معلّق وإرسال بريد تحقق (rate limit).
   - `GET /verify?token=…` — تحقق لمرة واحدة، إنشاء تذكرة `Self-Registration`، PDF، بريد التذكرة، صفحة تأكيد.
   - `POST /resend` — إعادة إرسال التحقق مع تحديد معدل.
   - نقاط أدمن: حفظ الإعدادات، عرض/تصدير/إلغاء التسجيلات.
3. **صفحة عامة** `/akf-register/?token=…` بثلاث لغات وRTL، بدون تثبيت تطبيق.
4. **صفحة أدمن** "Self-Service Registration" داخل Yalla Engage: QR/PNG/PDF/طباعة، إعدادات، تسجيلات، تصدير CSV، إلغاء.
5. عند التفعيل يُنشأ نوع تذكرة `Self-Registration` (سعر 0) للفعالية إن لم يكن موجوداً.
6. التذكرة الناتجة تُسجّل في `akf_tickets` → تعمل تلقائياً في AKF Gate Scanner بدون تعديله.

## الملفات المتأثرة
- `includes/class-akf-db.php` (جداول + إصدار)
- `includes/class-akf-tickets-admin.php` (إضافة الصنف ونقطة الدخول)
- `yalla-engage.php` (rewrite/template للصفحة العامة)
- `docs/SELF-REG-API.md` (توثيق نقاط API الجديدة)

## غير ملموس
- WooCommerce / PayPal / بوابة الدفع.
- تصميم التذكرة المعتمد.
- منطق الماسح الحالي.
