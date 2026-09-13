<?php

declare(strict_types=1);

/**
 * Upload de mídia para disco (nunca BLOB no SQLite).
 */
function uploads_dir(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads';
}

function uploads_ensure_dir(): void
{
    $dir = uploads_dir();
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/**
 * @return array{ok:bool,path?:string,error?:string}
 */
function uploads_handle(array $file, string $slugHint = 'file'): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'Nenhum arquivo enviado.'];
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Falha no upload (código ' . (int) $file['error'] . ').'];
    }

    $maxBytes = 40 * 1024 * 1024; // 40 MB (vídeos Signature)
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return ['ok' => false, 'error' => 'Arquivo inválido ou maior que 40 MB.'];
    }

    $name = (string) ($file['name'] ?? '');
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm'];
    if (!in_array($ext, $allowed, true)) {
        return ['ok' => false, 'error' => 'Tipo não permitido. Use: ' . implode(', ', $allowed)];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'Upload inválido.'];
    }

    uploads_ensure_dir();
    $slug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower($slugHint)) ?: 'file';
    $slug = trim($slug, '-') ?: 'file';
    $filename = $slug . '-' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $dest = uploads_dir() . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $dest)) {
        return ['ok' => false, 'error' => 'Não foi possível salvar o arquivo.'];
    }

    return ['ok' => true, 'path' => 'assets/uploads/' . $filename];
}
