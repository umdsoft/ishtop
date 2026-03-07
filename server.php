<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Serve miniapp SPA - all /miniapp/* routes should serve miniapp/index.html
if (preg_match('#^/miniapp(/.*)?$#', $uri)) {
    $miniappIndex = $publicPath.'/miniapp/index.html';

    // If it's a static asset (js, css, etc.), serve it directly
    if ($uri !== '/miniapp' && $uri !== '/miniapp/' && file_exists($publicPath.$uri)) {
        return false;
    }

    // Otherwise serve the SPA index.html
    if (file_exists($miniappIndex)) {
        header('Content-Type: text/html');
        readfile($miniappIndex);
        return;
    }
}

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
