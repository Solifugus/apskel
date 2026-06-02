<?php
// Dev router for PHP's built-in server (replaces Apache + mod_rewrite).
//
//   Run from the project root:
//     php -S localhost:8000 router.php
//
// Apache's only job for Apskel was to funnel every URL to webroot/index.php
// with the working directory set to webroot/. We reproduce exactly that here,
// so no .htaccess or mod_rewrite is needed.

$webroot = __DIR__ . '/webroot';

// Match the working directory Apache gave the script (webroot/), so the
// framework's relative path offsets ("../", "../../") resolve correctly.
chdir($webroot);

require $webroot . '/index.php';
