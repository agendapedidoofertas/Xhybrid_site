<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = is_string($uri) ? rawurldecode($uri) : '/';

if (preg_match('#(^|/)\.\.(/|$)#', $uri)) {
    http_response_code(400);
    echo 'Bad Request';
    return true;
}

if (preg_match('#^/data(/|$)#i', $uri) || preg_match('#\.(sqlite3?|db)$#i', $uri)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

$path = __DIR__ . $uri;

if (is_dir($path)) {
    foreach (['index.php', 'index.html'] as $index) {
        if (is_file($path . DIRECTORY_SEPARATOR . $index)) {
            header('Location: ' . rtrim($uri, '/') . '/' . $index);
            return true;
        }
    }
}

if ($uri !== '/' && is_file($path)) {
    return false;
}

if ($uri === '/' || $uri === '') {
    header('Location: /index.html');
    return true;
}

http_response_code(404);
$notFound = __DIR__ . '/404.html';
if (is_file($notFound)) {
    readfile($notFound);
} else {
    echo 'Not Found';
}
return true;
