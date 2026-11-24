<?php
$tag = '<script src="/x.min.js?ver=1" async></script>';
$out = preg_replace( '/<script src([^>]*)>/', '<script src$1 defer>', $tag, 1 );
echo $out, PHP_EOL;
$tag2 = '<script src="/y.js"></script>';
echo preg_replace( '/<script src([^>]*)>/', '<script src$1 defer>', $tag2, 1 ), PHP_EOL;
