<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/security.php';
require_once dirname(__DIR__) . '/lib/public_offer.php';
require_once dirname(__DIR__) . '/lib/roboto.php';

security_send_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

try {
    $pdo = db();
    $offer = public_offer_get($pdo);
    $payload = [
        'intro' => $offer['intro'],
        'eyebrow' => $offer['eyebrow'],
        'title' => $offer['title'],
        'highlight' => $offer['highlight'],
        'referral_note' => $offer['referral_note'],
        'terms_summary' => $offer['terms_summary'],
        'terms_url' => $offer['terms_url'],
        'privacy_summary' => $offer['privacy_summary'],
        'privacy_url' => $offer['privacy_url'],
        'plans' => $offer['plans'],
        'roboto' => roboto_planos_public_payload($pdo),
    ];
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    if (function_exists('api_json_error')) {
        api_json_error('Falha ao carregar oferta', $e);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Falha ao carregar oferta'], JSON_UNESCAPED_UNICODE);
    }
}
