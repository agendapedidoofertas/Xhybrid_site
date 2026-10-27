<?php

declare(strict_types=1);

require_once __DIR__ . '/extract_frames.php';

$ffmpeg = find_ffmpeg_binary();
$inImg = dirname(__DIR__) . '/assets/presets/eletricista/eletrica_05_quadro_disjuntores.jpg';
$outDir = dirname(__DIR__) . '/assets/scrollytelling/eletrica';

if (!is_file($inImg)) {
    echo "Arquivo base não encontrado: {$inImg}\n";
    exit(1);
}

if (!is_dir($outDir)) {
    @mkdir($outDir, 0755, true);
}

echo "Gerando 28 frames WebP com interpolação ótica...\n";

for ($i = 1; $i <= 28; $i++) {
    $idx = sprintf('%03d', $i);
    $w = (int) round(1280 - ($i * 7));
    $h = (int) round(720 - ($i * 3.93));
    $x = (int) round($i * 3.5);
    $y = (int) round($i * 1.96);
    $outPath = $outDir . "/frame-{$idx}.webp";

    $cmd = escapeshellcmd($ffmpeg)
        . ' -y -i ' . escapeshellarg($inImg)
        . ' -vf "crop=' . $w . ':' . $h . ':' . $x . ':' . $y . ',scale=1280:720"'
        . ' -vframes 1 -c:v libwebp -quality 85 '
        . escapeshellarg($outPath)
        . ' 2>&1';

    exec($cmd);
}

$files = glob($outDir . '/frame-*.webp') ?: [];
$bytes = array_sum(array_map('filesize', $files));
echo 'Sucesso! ' . count($files) . ' frames gerados | Total: ' . round($bytes / 1024, 1) . " KB\n";
