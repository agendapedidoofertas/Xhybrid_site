<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

try {
    $stmt = db()->query(
        'SELECT id, slug, url, title, description, price, promo_price
         FROM images
         WHERE active = 1
         ORDER BY position ASC, id ASC'
    );
    $rows = $stmt->fetchAll();
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao carregar imagens'], JSON_UNESCAPED_UNICODE);
}
