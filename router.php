<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = is_string($uri) ? rawurldecode($uri) : '/';

if (preg_match('#(^|/)\.\.(/|$)#', $uri)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Bad Request';
    exit;
}

// Bloqueia pasta data/ e bancos — exit (não return) para o php -S respeitar o 403
$dataRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'data');
$candidate = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uri);
$realCandidate = is_file($candidate) || is_dir($candidate) ? realpath($candidate) : false;
$blockedByPath = $dataRoot && $realCandidate && str_starts_with($realCandidate, $dataRoot);
$blockedByUri = (bool) preg_match('#^/data(/|$)#i', $uri) || (bool) preg_match('#\.(sqlite3?|db)$#i', $uri);

if ($blockedByPath || $blockedByUri) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo 'Forbidden';
    exit;
}

$path = __DIR__ . $uri;

if (is_dir($path)) {
    foreach (['index.php', 'index.html'] as $index) {
        if (is_file($path . DIRECTORY_SEPARATOR . $index)) {
            header('Location: ' . rtrim($uri, '/') . '/' . $index);
            exit;
        }
    }
}

if ($uri !== '/' && is_file($path)) {
    return false;
}

if ($uri === '/' || $uri === '') {
    header('Location: /index.html');
    exit;
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
$notFound = __DIR__ . '/404.html';
if (is_file($notFound)) {
    readfile($notFound);
} else {
    echo 'Not Found';
}
exit;
