<?php

declare(strict_types=1);

/**
 * Xhybrid AI Site Builder
 * Gera conteúdo inteligente e orquestra mídias para sites no xhybrid_site.
 */

require_once __DIR__ . '/images.php';

function xhybrid_ai_key(): string
{
    $env = getenv('GEMINI_API_KEY');
    if (is_string($env) && trim($env) !== '') {
        return trim($env);
    }

    $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'xhybrid_wa_bot' . DIRECTORY_SEPARATOR . '.env';
    if (is_file($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'GEMINI_API_KEY=')) {
                return trim(trim(substr($line, strlen('GEMINI_API_KEY='))), "\"' ");
            }
        }
    }
    return '';
}

/**
 * Gera pacote institucional completo para um site de cliente
 */
function xhybrid_ai_generate_site_data(string $businessName, string $segment, string $city = 'São Paulo'): ?array
{
    $key = xhybrid_ai_key();
    if ($key === '') {
        return null;
    }

    $prompt = "Você é o especialista em web design da Xhybrid. Crie a estrutura para um site de: '{$businessName}', ramo: '{$segment}', localidade: '{$city}'.";
    $sysPrompt = "Retorne estritamente um JSON com hero_title, hero_subtitle, about_text, services (array com title, description), e image_prompts.";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$key}";
    $payload = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]],
        'systemInstruction' => ['parts' => [['text' => $sysPrompt]]]
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 20,
    ]);

    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http !== 200 || !is_string($res)) {
        return null;
    }

    $data = json_decode($res, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $clean = preg_replace('/^```json|```$/m', '', $text);
    return json_decode(trim($clean), true);
}

/**
 * Aplica os dados gerados pela IA nas tabelas de imagens e presets do lead
 */
function xhybrid_ai_populate_lead_site(PDO $pdo, int $crmLeadId, string $businessName, string $segment, string $city = 'São Paulo'): bool
{
    $siteData = xhybrid_ai_generate_site_data($businessName, $segment, $city);
    if (!$siteData) {
        return false;
    }

    $imageRows = [];
    $imageRows[] = [
        'slug' => 'hero-banner',
        'url' => '/assets/lead-hero-default.jpg',
        'title' => $siteData['hero_title'] ?? $businessName,
        'description' => $siteData['hero_subtitle'] ?? '',
        'position' => 10,
        'active' => 1
    ];

    if (!empty($siteData['services']) && is_array($siteData['services'])) {
        $pos = 20;
        foreach ($siteData['services'] as $idx => $srv) {
            $imageRows[] = [
                'slug' => 'service-' . ($idx + 1),
                'url' => '/assets/service-default.jpg',
                'title' => $srv['title'] ?? 'Serviço ' . ($idx + 1),
                'description' => $srv['description'] ?? '',
                'position' => $pos,
                'active' => 1
            ];
            $pos += 10;
        }
    }

    images_upsert_by_slug_for_lead($pdo, $crmLeadId, $imageRows);
    return true;
}
