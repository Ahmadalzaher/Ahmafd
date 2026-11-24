<?php
$tags = array(
    '<script id="woovina-main-js" src="https://arap-library.de/x/main.min.js?ver=2.2"></script>',
    '<script src="https://a.min.js" defer></script>',
    '<script src="/y.js" async></script>',
    '<script type="module" src="/m.js"></script>',
);
foreach ($tags as $tag) {
    $excluded = array('jquery', 'jquery-migrate');
    $h = 'test';
    if (false === strpos($tag, 'src=')) { echo "no src: ", $tag, PHP_EOL; continue; }
    if (false !== strpos($tag, 'defer')) { echo "already: ", $tag, PHP_EOL; continue; }
    echo preg_replace('/^(<script[^>]*?)>/i', '$1 defer>', $tag, 1), PHP_EOL;
}
