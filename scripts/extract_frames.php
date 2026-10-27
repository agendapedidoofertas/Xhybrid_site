<?php

declare(strict_types=1);

/**
 * Script utilitário Xhybrid: Extração de Frames de Vídeo para Scrollytelling (WebP)
 * Uso: php scripts/extract_frames.php [caminho_do_video.mp4] [pasta_destino] [quantidade_de_frames]
 */

function find_ffmpeg_binary(): string
{
    // 1. Testa ffmpeg no PATH
    $output = [];
    $ret = -1;
    @exec('ffmpeg -version 2>&1', $output, $ret);
    if ($ret === 0) {
        return 'ffmpeg';
    }

    // 2. Busca no diretório do WinGet
    $localApp = getenv('LOCALAPPDATA') ?: '';
    if ($localApp !== '') {
        $wingetDir = $localApp . DIRECTORY_SEPARATOR . 'Microsoft' . DIRECTORY_SEPARATOR . 'WinGet' . DIRECTORY_SEPARATOR . 'Packages';
        if (is_dir($wingetDir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($wingetDir));
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getFilename()) === 'ffmpeg.exe') {
                    return $file->getRealPath();
                }
            }
        }
    }

    throw new RuntimeException('FFmpeg não encontrado no sistema.');
}

// Executa apenas se chamado diretamente via CLI
if (isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $videoFile = $argv[1] ?? (dirname(__DIR__) . '/assets/scrollytelling/raw_videos/video.mp4');
    $outputDir = $argv[2] ?? (dirname(__DIR__) . '/assets/scrollytelling/eletrica');
    $totalFrames = isset($argv[3]) ? max(10, min(120, (int) $argv[3])) : 28;

    if (!is_file($videoFile)) {
        echo "⚠️ Arquivo de vídeo não encontrado em: {$videoFile}\n";
        echo "Uso: php scripts/extract_frames.php [video.mp4] [pasta_destino] [quantidade_frames]\n";
        exit(1);
    }

    if (!is_dir($outputDir)) {
        @mkdir($outputDir, 0755, true);
    }

    $ffmpeg = find_ffmpeg_binary();
    echo "🚀 FFmpeg localizado: {$ffmpeg}\n";
    echo "🎬 Processando: " . basename($videoFile) . " -> {$totalFrames} frames WebP em " . basename($outputDir) . "...\n";

    // Limpa frames antigos na pasta de destino para evitar mistura
    $oldFiles = glob($outputDir . DIRECTORY_SEPARATOR . 'frame-*.webp') ?: [];
    foreach ($oldFiles as $f) {
        @unlink($f);
    }

    // Extrai frames com amostragem uniforme e compressão WebP de alta performance
    $outputPattern = $outputDir . DIRECTORY_SEPARATOR . 'frame-%03d.webp';
    $escapedFfmpeg = escapeshellcmd($ffmpeg);
    $escapedInput = escapeshellarg($videoFile);
    $escapedOutput = escapeshellarg($outputPattern);

    $cmd = "{$escapedFfmpeg} -y -i {$escapedInput} -vf \"select='not(mod(n\,1))',scale=1280:-1\" -vframes {$totalFrames} -c:v libwebp -quality 85 -compression_level 4 {$escapedOutput} 2>&1";

    exec($cmd, $outputLines, $exitCode);

    $generated = glob($outputDir . DIRECTORY_SEPARATOR . 'frame-*.webp') ?: [];
    $count = count($generated);

    if ($count > 0) {
        $totalBytes = 0;
        foreach ($generated as $f) {
            $totalBytes += filesize($f);
        }
        $totalKb = round($totalBytes / 1024, 1);
        $avgKb = round($totalKb / $count, 1);

        echo "✅ Sucesso! {$count} frames gerados com perfeição.\n";
        echo "📦 Tamanho total: {$totalKb} KB (Média: {$avgKb} KB por frame).\n";
        echo "⚡ Pronto para o motor de Scrollytelling da Xhybrid!\n";
    } else {
        echo "❌ Falha ao extrair frames. Detalhes:\n" . implode("\n", array_slice($outputLines, -10)) . "\n";
        exit(1);
    }
}
