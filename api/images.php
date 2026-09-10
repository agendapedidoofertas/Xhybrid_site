<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/images.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

try {
    $pdo = db();
    images_deactivate_unavailable($pdo);
    // Ativas ou inativas: se ainda tem URL/arquivo válido, a API entrega a imagem.
    // Sem URL/arquivo → não entra na API → front usa o robô.
    $stmt = $pdo->query(
        'SELECT id, slug, url, title, description, price, promo_price, active
         FROM images
         ORDER BY position ASC, id ASC'
    );
    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        if (!images_url_available((string) ($row['url'] ?? ''))) {
            continue;
        }
        unset($row['active']);
        $rows[] = $row;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao carregar imagens'], JSON_UNESCAPED_UNICODE);
}
