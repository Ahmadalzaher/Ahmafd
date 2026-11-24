<?php
/**
 * Help page template (Arabic).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'مساعدة — إدارة النشرة والتسجيل', 'yalla-engage' ); ?></h1>

	<h2><?php echo esc_html__( 'المهام اليومية', 'yalla-engage' ); ?></h2>

	<h3><?php echo esc_html__( '1. كتابة وإرسال نشرة', 'yalla-engage' ); ?></h3>
	<ol>
		<li><?php echo esc_html__( 'اذهب إلى "النشرات" وأضف حملة جديدة (الموضوع، اللغة، المحتوى).', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'اضغط "إرسال نسخة تجريبية إلى بريدي" لتجربة الرسالة على بريدك أولاً.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'اضغط "إرسال" وسيظهر لك تأكيد بعدد المستلمين المؤكدين فقط.', 'yalla-engage' ); ?></li>
	</ol>

	<h3><?php echo esc_html__( '2. فتح أو إغلاق التسجيل لفعالية', 'yalla-engage' ); ?></h3>
	<ol>
		<li><?php echo esc_html__( 'اذهب إلى "Termine" (الفعاليات) وافتح فعالية للتعديل.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'في مربع "التسجيل"، اختر "مفتوح" أو "مغلق" وحدد عدد المقاعد.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'اضغط "تحديث" لحفظ التغييرات.', 'yalla-engage' ); ?></li>
	</ol>

	<h3><?php echo esc_html__( '3. عرض قائمة المسجّلين وتصديرها', 'yalla-engage' ); ?></h3>
	<ol>
		<li><?php echo esc_html__( 'اذهب إلى "التسجيلات" لراجع جميع التسجيلات.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'اضغط "تصدير CSV" لتحميل القائمة بترميز UTF-8 صحيح للعربية.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'اضغط "إلغاء" لإلغاء تسجيل شخص ما.', 'yalla-engage' ); ?></li>
	</ol>

	<h3><?php echo esc_html__( '4. إرسال رسالة لمسجّلي فعالية', 'yalla-engage' ); ?></h3>
	<ol>
		<li><?php echo esc_html__( 'اذهب إلى "التسجيلات"، اختر الفعالية، واضغط "إرسال رسالة لمسجّلي هذه الفعالية".', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'سيظهر لك تأكيد بعدد من سيتأثر قبل الإرسال.', 'yalla-engage' ); ?></li>
	</ol>

	<h3><?php echo esc_html__( '5. فحص البريد', 'yalla-engage' ); ?></h3>
	<ol>
		<li><?php echo esc_html__( 'اذهب إلى "فحص البريد" وأرسل رسالة اختبار إلى بريدك.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'إذا وصلت الرسالة، يعمل الإرسال بشكل طبيعي. إذا لم تصل، تأكد من إعداد SMTP عبر إضافة WP Mail SMTP.', 'yalla-engage' ); ?></li>
	</ol>

	<h2><?php echo esc_html__( 'أدوار الصلاحيات', 'yalla-engage' ); ?></h2>
	<p><?php echo esc_html__( 'الدور "مدير النشرة والتسجيل" يتيح إدارة الإضافة دون إعطاء جميع صلاحيات الموقع.', 'yalla-engage' ); ?></p>

	<h2><?php echo esc_html__( 'ملاحظات مهمة', 'yalla-engage' ); ?></h2>
	<ul>
		<li><?php echo esc_html__( 'الإرسال يعتمد على wp_mail()، فأضف إضافة WP Mail SMTP لضبط SMTP الخاص بنطاقك.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'فعّل مهمة cron حقيقية في Hostinger كل 5 دقائق لضمان معالجة قائمة الإرسال والتذكيرات.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'لا تُحذف البيانات عند تعطيل الإضافة. عند الحذف الكامل، يُسأل إن كنت ترغب في حذفها (الافتراضي: لا).', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'تعتمد صورة البوستر على نوع المحتوى yd_event الوارد من الوحدة القديمة في القالب. إذا تم تعطيل القالب أو الوحدة، سيختفي مربع الصورة بصمت من شاشة تحرير الفعاليات.', 'yalla-engage' ); ?></li>
		<li><?php echo esc_html__( 'لا تظهر صورة البوستر في رسائل البريد ولا في تذكرة PDF، وهذا مقصود لأن بعض خدمات البريد مثل Gmail تحجب الصور البعيدة. وتظهر الصورة في نموذج التسجيل على الموقع فقط.', 'yalla-engage' ); ?></li>
	</ul>
</div>