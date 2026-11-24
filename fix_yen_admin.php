<?php
$file = "D:/oben code/yalla-engage-extract/yalla-engage/includes/class-yen-admin.php";
$content = file_get_contents($file);

// The old section to replace - from function start to the closing brace at line 809
// We need to replace lines 766-809 (1-indexed)

$oldFunc = "function yena_add_participant_logos_meta_box( \$post_type, \$post ) {\n";
$oldFunc .= "	if ( 'yd_event' !== \$post_type ) {\n";
$oldFunc .= "		return;\n";
$oldFunc .= "	}\n";
$oldFunc .= "	add_meta_box( 'yena_participant_logos_meta', __( 'شعارات المشاركين أو الشركاء', 'yalla-engage' ), 'yena_participant_logos_meta_box_callback', \$post_type, 'normal', 'default' );\n";
$oldFunc .= "}\n";
$oldFunc .= "\t\$logos = get_post_meta( \$post->ID, 'yena_participant_logos', true );\n";
$oldFunc .= "\t\$logos = is_array( \$logos ) ? \$logos : array();\n";
$oldFunc .= "\tif ( empty( \$logos ) ) {\n";
$oldFunc .= "\t\t\$logos = array( array( 'url' => '', 'name' => '', 'link' => '' ) );\n";
$oldFunc .= "\t}\n";
$oldFunc .= "\twp_nonce_field( 'yena_participant_logos_meta', 'yena_participant_logos_nonce' );\n";
$oldFunc .= "\t?>\n";
$oldFunc .= "\t<p><?php echo esc_html__( 'اختياري: أضف شعارات المشاركين أو الشركاء. ستظهر أسفل نموذج الفعالية على الموقع فقط.', 'yalla-engage' ); ?></p>\n";
$oldFunc .= "\t<div id=\"yena-participant-logo-list\">\n";
$oldFunc .= "\t\t<?php foreach ( \$logos as \$index => \$logo ) : ?>\n";
$oldFunc .= "\t\t\t<div class=\"yena-participant-logo-row\" style=\"border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;\">\n";
$oldFunc .= "\t\t\t\t<p><input type=\"text\" class=\"widefat yena-participant-logo-url\" name=\"yena_participant_logos[\<?php echo (int) \$index; ?>][url]\" value=\"<?php echo esc_attr( isset( \$logo['url'] ) ? \$logo['url'] : '' ); ?>\" placeholder=\"رابط صورة الشعار\"></p>\n";
$oldFunc .= "\t\t\t\t<p><input type=\"text\" class=\"widefat\" name=\"yena_participant_logos[\<?php echo (int) \$index; ?>][name]\" value=\"<?php echo esc_attr( isset( \$logo['name'] ) ? \$logo['name'] : '' ); ?>\" placeholder=\"اسم المشارك أو الشريك\"></p>\n";
$oldFunc .= "\t\t\t\t<p><input type=\"url\" class=\"widefat\" name=\"yena_participant_logos[\<?php echo (int) \$index; ?>][link]\" value=\"<?php echo esc_attr( isset( \$logo['link'] ) ? \$logo['link'] : '' ); ?>\" placeholder=\"الرابط الاختياري للمشارك\"></p>\n";
$oldFunc .= "\t\t\t\t<button type=\"button\" class=\"button yena-upload-participant-logo\">اختيار صورة</button>\n";
$oldFunc .= "\t\t\t\t<button type=\"button\" class=\"button yena-remove-participant-logo\">حذف</button>\n";
$oldFunc .= "\t\t\t</div>\n";
$oldFunc .= "\t\t<?php endforeach; ?>\n";
$oldFunc .= "\t</div>\n";
$oldFunc .= "\t<button type=\"button\" class=\"button button-secondary\" id=\"yena-add-participant-logo\">إضافة شعار آخر</button>\n";
$oldFunc .= "\t<script>\n";
$oldFunc .= "\tjQuery(function(\$){\n";
$oldFunc .= "\t\tvar list=\$('#yena-participant-logo-list');\n";
$oldFunc .= "\t\t\$('#yena-add-participant-logo').on('click',function(){\n";
$oldFunc .= "\t\t\tvar i=list.find('.yena-participant-logo-row').length;\n";
$oldFunc .= "\t\t\tlist.append('<div class=\"yena-participant-logo-row\" style=\"border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;\"><p><input type=\"text\" class=\"widefat yena-participant-logo-url\" name=\"yena_participant_logos['+i+'][url]\" placeholder=\"رابط صورة الشعار\"></p><p><input type=\"text\" class=\"widefat\" name=\"yena_participant_logos['+i+'][name]\" placeholder=\"اسم المشارك أو الشريك\"></p><p><input type=\"url\" class=\"widefat\" name=\"yena_participant_logos['+i+'][link]\" placeholder=\"الرابط الاختياري للمشارك\"></p><button type=\"button\" class=\"button yena-upload-participant-logo\">اختيار صورة</button> <button type=\"button\" class=\"button yena-remove-participant-logo\">حذف</button></div>');\n";
$oldFunc .= "\t\t});\n";
$oldFunc .= "\t\t$(document).on('click','.yena-remove-participant-logo',function(){ $(this).closest('.yena-participant-logo-row').remove(); });\n";
$oldFunc .= "\t});\n";
$oldFunc .= "\t$(document).on('click','.yena-upload-participant-logo',function(){\n";
$oldFunc .= "\t\tvar input=\$(this).closest('.yena-participant-logo-row').find('.yena-participant-logo-url');\n";
$oldFunc .= "\t\tvar frame=wp.media({title:'اختيار شعار',button:{text:'استخدام الشعار'},multiple:false});\n";
$oldFunc .= "\t\tframe.on('select',function(){ input.val(frame.state().get('selection').first().toJSON().url); });\n";
$oldFunc .= "\t\tframe.open();\n";
$oldFunc .= "\t});\n";
$oldFunc .= "\t</script>\n";
$oldFunc .= "\t<?php\n";
// Note: the closing brace } at line 809 is part of the old section

$newFunc = "function yena_add_participant_logos_meta_box( \$post_type, \$post ) {\n";
$newFunc .= "	if ( 'yd_event' !== \$post_type ) {\n";
$newFunc .= "		return;\n";
$newFunc .= "	}\n";
$newFunc .= "	add_meta_box( 'yena_participant_logos_meta', __( 'شعارات المشاركين أو الشركاء', 'yalla-engage' ), 'yena_participant_logos_meta_box_callback', \$post_type, 'normal', 'default' );\n";
$newFunc .= "\$logos = get_post_meta( \$post->ID, 'yena_participant_logos', true );\n";
$newFunc .= "\$logos = is_array( \$logos ) ? \$logos : array();\n";
$newFunc .= "if ( empty( \$logos ) ) {\n";
$newFunc .= "\t\$logos = array( array( 'url' => '', 'name' => '', 'link' => '' ) );\n";
$newFunc .= "}\n";
$newFunc .= "wp_nonce_field( 'yena_participant_logos_meta', 'yena_participant_logos_nonce' );\n";
$newFunc .= "?>\n";
$newFunc .= "\t<p><?php echo esc_html__( 'اختياري: أضف شعارات المشاركين أو الشركاء. ستظهر أسفل نموذج الفعالية على الموقع فقط.', 'yalla-engage' ); ?></p>\n";
$newFunc .= "\t<div id=\"yena-participant-logo-list\">\n";
$newFunc .= "\t\t<?php foreach ( \$logos as \$index => \$logo ) : ?>\n";
$newFunc .= "\t\t\t<div class=\"yena-participant-logo-row\" style=\"border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;\">\n";
$newFunc .= "\t\t\t\t<p><input type=\"text\" class=\"widefat yena-participant-logo-url\" name=\"yena_participant_logos[\<?php echo (int) \$index; ?>][url]\" value=\"<?php echo esc_attr( isset( \$logo['url'] ) ? \$logo['url'] : '' ); ?>\" placeholder=\"رابط صورة الشعار\"></p>\n";
$newFunc .= "\t\t\t\t<p><input type=\"text\" class=\"widefat\" name=\"yena_participant_logos[\<?php echo (int) \$index; ?>][name]\" value=\"<?php echo esc_attr( isset( \$logo['name'] ) ? \$logo['name'] : '' ); ?>\" placeholder=\"اسم المشارك أو الشريك\"></p>\n";
$newFunc .= "\t\t\t\t<p><input type=\"url\" class=\"widefat\" name=\"yena_participant_logos[\<?php echo (int) \$index; ?>][link]\" value=\"<?php echo esc_attr( isset( \$logo['link'] ) ? \$logo['link'] : '' ); ?>\" placeholder=\"الرابط الاختياري للمشارك\"></p>\n";
$newFunc .= "\t\t\t\t<button type=\"button\" class=\"button yena-upload-participant-logo\">اختيار صورة</button>\n";
$newFunc .= "\t\t\t\t<button type=\"button\" class=\"button yena-remove-participant-logo\">حذف</button>\n";
$newFunc .= "\t\t\t</div>\n";
$newFunc .= "\t\t<?php endforeach; ?>\n";
$newFunc .= "\t</div>\n";
$newFunc .= "\t<button type=\"button\" class=\"button button-secondary\" id=\"yena-add-participant-logo\">إضافة شعار آخر</button>\n";
$newFunc .= "\t<script>\n";
$newFunc .= "\tjQuery(function(\$){\n";
$newFunc .= "\t\tvar list=\$('#yena-participant-logo-list');\n";
$newFunc .= "\t\t\$('#yena-add-participant-logo').on('click',function(){\n";
$newFunc .= "\t\t\tvar i=list.find('.yena-participant-logo-row').length;\n";
$newFunc .= "\t\t\tlist.append('<div class=\"yena-participant-logo-row\" style=\"border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;\"><p><input type=\"text\" class=\"widefat yena-participant-logo-url\" name=\"yena_participant_logos['+i+'][url]\" placeholder=\"رابط صورة الشعار\"></p><p><input type=\"text\" class=\"widefat\" name=\"yena_participant_logos['+i+'][name]\" placeholder=\"اسم المشارك أو الشريك\"></p><p><input type=\"url\" class=\"widefat\" name=\"yena_participant_logos['+i+'][link]\" placeholder=\"الرابط الاختياري للمشارك\"></p><button type=\"button\" class=\"button yena-upload-participant-logo\">اختيار صورة</button> <button type=\"button\" class=\"button yena-remove-participant-logo\">حذف</button></div>');\n";
$newFunc .= "\t\t});\n";
$newFunc .= "\t\t$(document).on('click','.yena-remove-participant-logo',function(){ $(this).closest('.yena-participant-logo-row').remove(); });\n";
$newFunc .= "\t});\n";
$newFunc .= "\t$(document).on('click','.yena-upload-participant-logo',function(){\n";
$newFunc .= "\t\tvar input=\$(this).closest('.yena-participant-logo-row').find('.yena-participant-logo-url');\n";
$newFunc .= "\t\tvar frame=wp.media({title:'اختيار شعار',button:{text:'استخدام الشعار'},multiple:false});\n";
$newFunc .= "\t\tframe.on('select',function(){ input.val(frame.state().get('selection').first().toJSON().url); });\n";
$newFunc .= "\t\tframe.open();\n";
$newFunc .= "\t});\n";
$newFunc .= "\t</script>\n";
$newFunc .= "\t<?php\n";
$newFunc .= "}\n";

// Now find and replace
// Find position of old function
$pos = strpos($content, $oldFunc);
if ($pos !== false) {
    // Also need to find where the old section ends (after the closing brace)
    // The old section includes the function and the orphaned code and the closing brace
    $replacement = $newFunc;
    $newContent = substr_replace($content, $replacement, $pos, strlen($oldFunc));
    file_put_contents($file, $newContent);
    echo "SUCCESS: Fixed class-yen-admin.php\n";
} else {
    echo "ERROR: Could not find old function pattern\n";
    // Debug: print what we're looking for
    echo "Looking for pattern of length: " . strlen($oldFunc) . "\n";
    // Try to find function start
    $pos2 = strpos($content, "function yena_add_participant_logos_meta_box");
    echo "Function start found at: $pos2\n";
}
?>