$file = 'D:/oben code/yalla-engage-extract/yalla-engage/includes/class-yen-admin.php'
$content = Get-Content $file -Raw

# Replace the broken function section
# The pattern: function closing brace followed by orphaned code
# We want to merge the function body with the orphaned code

# Find and replace the specific problematic pattern
# The function ends with } on line 771, then immediately orphaned code starts

# Pattern: closing brace of function, then tab + $logos line
$pattern = "`r?`n\t}\r?`n\t\$logos = get_post_meta"

$replacement = "`r?`nfunction yena_add_participant_logos_meta_box( \$post_type, \$post ) {"`r?`n`tif ( 'yd_event' !== \$post_type ) {"`r?`n`t`treturn;`r?`n`t}`r?`n`tadd_meta_box( 'yena_participant_logos_meta', __( 'شعارات المشاركين أو الشركاء', 'yalla-engage' ), 'yena_participant_logos_meta_box_callback', \$post_type, 'normal', 'default' );`r?`n`t\$logos = get_post_meta( \$post->ID, 'yena_participant_logos', true );`r?`n`t\$logos = is_array( \$logos ) ? \$logos : array();`r?`n`tif ( empty( \$logos ) ) {"`r?`n`t`t\$logos = array( array( 'url' => '', 'name' => '', 'link' => '' ) );`r?`n`t}`r?`n`twp_nonce_field( 'yena_participant_logos_meta', 'yena_participant_logos_nonce' );`r?`n`t?>`r?`n`t<p><?php echo esc_html__( 'اختياري: أضف شعارات المشاركين أو الشركاء. ستظهر أسفل نموذج الفعالية على الموقع فقط.', 'yalla-engage' ); ?></p>`r?`n`t<div id="yena-participant-logo-list">`r?`n`t\t<?php foreach ( \$logos as \$index => \$logo ) : ?>`r?`n`t\t\t<div class="yena-participant-logo-row" style="border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;">`r?`n`t\t\t\t<p><input type="text" class="widefat yena-participant-logo-url" name="yena_participant_logos[\<?php echo (int) \$index; ?>][url]" value="<?php echo esc_attr( isset( \$logo['url'] ) ? \$logo['url'] : '' ); ?>\" placeholder="رابط صورة الشعار"></p>`r?`n`t\t\t<p><input type="text" class="widefat" name="yena_participant_logos[\<?php echo (int) \$index; ?>][name]" value="<?php echo esc_attr( isset( \$logo['name'] ) ? \$logo['name'] : '' ); ?>\" placeholder="اسم المشارك أو الشريك"></p>`r?`n`t\t\t<p><input type="url" class="widefat" name="yena_participant_logos[\<?php echo (int) \$index; ?>][link]" value="<?php echo esc_attr( isset( \$logo['link'] ) ? \$logo['link'] : '' ); ?>\" placeholder="الرابط الاختياري للمشارك"></p>`r?`n`t\t\t<button type="button" class="button yena-upload-participant-logo">اختيار صورة</button>`r?`n`t\t\t<button type="button" class="button yena-remove-participant-logo">حذف</button>`r?`n`t\t</div>`r?`n`t\t<?php endforeach; ?>`r?`n`t`</div>`r?`n`t<button type="button" class="button button-secondary" id="yena-add-participant-logo">إضافة شعار آخر</button>`r?`n`t<script>`r?`n`tjQuery(function(\$){`r?`n`t\tvar list=\$($('#yena-participant-logo-list'));`r?`n`t\$('#yena-add-participant-logo').on('click',function(){"`r?`n`t\t\tvar i=list.find('.yena-participant-logo-row').length;`r?`n`t\t\tlist.append('<div class="yena-participant-logo-row" style="border:1px solid #ddd;padding:10px;margin:8px 0;background:#fafafa;"><p><input type="text" class="widefat yena-participant-logo-url" name="yena_participant_logos['+i+'][url]" placeholder="رابط صورة الشعار"></p><p><input type="text" class="widefat" name="yena_participant_logos['+i+'][name]" placeholder="اسم المشارك أو الشريك"></p><p><input type="url" class="widefat" name="yena_participant_logos['+i+'][link]" placeholder="الرابط الاختياري للمشارك"></p><button type="button" class="button yena-upload-participant-logo">اختيار صورة</button> <button type="button" class="button yena-remove-participant-logo">حذف</button></div>');`r?`n`t\t});`r?`n`t\t$(document).on('click','.yena-remove-participant-logo',function(){ $(this).closest('.yena-participant-logo-row').remove(); });`r?`n`t);`r?`n`t$(document).on('click','.yena-upload-participant-logo',function(){"`r?`n`t\tvar input=\$(this).closest('.yena-participant-logo-row').find('.yena-participant-logo-url');`r?`n`t\tvar frame=wp.media({title:'اختيار شعار',button:{text:'استخدام الشعار'},multiple:false});`r?`n`t\tframe.on('select',function(){ input.val(frame.state().get('selection').first().toJSON().url); });`r?`n`t\tframe.open();`r?`n`t});`r?`n`t`</script>`r?`n`t<?php`r?`n}`r?`n

if ($content -match $pattern) {
    $newContent = $content -replace $pattern, $replacement
    Set-Content $file $newContent -Encoding UTF8
    Write-Host "SUCCESS: Fixed class-yen-admin.php"
} else {
    Write-Host "PATTERN NOT FOUND - trying alternative"
    # Debug: print what we have around line 770-775
    $lines = $content -split "`r?`n"
    for ($i = 769; $i < 776; $i++) {
        Write-Host ("Line " + ($i+1) + ": " + $lines[$i])
    }
}