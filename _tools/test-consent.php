<?php
/**
 * Verify the YouTube embed rewriter.
 */

$html = '<p>x</p><iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ?si=abc" title="Demo" allowfullscreen></iframe><p>y</p>';
$out  = apply_filters( 'the_content', $html );

echo "HAS_IFRAME=" . ( false !== strpos( $out, '<iframe' ) ? 'yes' : 'no' ) . "\n";
echo "HAS_PLACEHOLDER=" . ( false !== strpos( $out, 'data-yd-embed="dQw4w9WgXcQ"' ) ? 'yes' : 'no' ) . "\n";
echo "HAS_THUMB=" . ( false !== strpos( $out, 'ytimg' ) ? 'yes' : 'no' ) . "\n";
echo "SHORTCODE=" . wp_kses_post( do_shortcode( '[yd_video id="dQw4w9WgXcQ" title="Test"]' ) ) . "\n";
