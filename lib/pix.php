<?php

declare(strict_types=1);

/**
 * PIX estático (BR Code / EMV) — chave CNPJ da empresa.
 *
 * @return array{key:string,merchant_name:string,merchant_city:string,amount:string,support_whatsapp:string,txid_prefix:string}
 */
function pix_config(): array
{
    $defaults = [
        'key' => '68351639000155',
        'merchant_name' => 'XHYBRID',
        'merchant_city' => 'SAO PAULO',
        'amount' => '', // vazio = cliente informa o valor
        'support_whatsapp' => '5511999999999',
        'txid_prefix' => 'XH',
    ];
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'pix_config.php';
    if (is_file($path)) {
        $cfg = include $path;
        if (is_array($cfg)) {
            foreach ($defaults as $k => $v) {
                if (isset($cfg[$k]) && is_string($cfg[$k])) {
                    $defaults[$k] = trim($cfg[$k]);
                }
            }
        }
    }
    // Fallback: WhatsApp da agência no settings
    if ($defaults['support_whatsapp'] === '' || $defaults['support_whatsapp'] === '5511999999999') {
        try {
            require_once __DIR__ . '/db.php';
            require_once __DIR__ . '/settings.php';
            $wa = preg_replace('/\D+/', '', settings_get(db(), 'whatsapp_number')) ?? '';
            if ($wa !== '') {
                if (strlen($wa) >= 10 && strlen($wa) <= 11 && !str_starts_with($wa, '55')) {
                    $wa = '55' . $wa;
                }
                $defaults['support_whatsapp'] = $wa;
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
    $defaults['key'] = preg_replace('/\D+/', '', $defaults['key']) ?? $defaults['key'];
    return $defaults;
}

function pix_tlv(string $id, string $value): string
{
    $len = strlen($value);
    return $id . str_pad((string) $len, 2, '0', STR_PAD_LEFT) . $value;
}

function pix_crc16(string $payload): string
{
    $polynomial = 0x1021;
    $result = 0xFFFF;
    $len = strlen($payload);
    for ($offset = 0; $offset < $len; $offset++) {
        $result ^= (ord($payload[$offset]) << 8);
        for ($bit = 0; $bit < 8; $bit++) {
            if (($result & 0x8000) !== 0) {
                $result = (($result << 1) ^ $polynomial) & 0xFFFF;
            } else {
                $result = ($result << 1) & 0xFFFF;
            }
        }
    }
    return strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));
}

/**
 * Gera payload PIX copia-e-cola.
 *
 * @param array{txid?:string,amount?:string} $opts
 */
function pix_payload(array $opts = []): string
{
    $cfg = pix_config();
    $name = strtoupper(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cfg['merchant_name']) ?? 'XHYBRID', 0, 25));
    $city = strtoupper(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cfg['merchant_city']) ?? 'SAO PAULO', 0, 15));
    if ($name === '') {
        $name = 'XHYBRID';
    }
    if ($city === '') {
        $city = 'SAO PAULO';
    }

    $gui = pix_tlv('00', 'br.gov.bcb.pix');
    $key = pix_tlv('01', $cfg['key']);
    $mai = pix_tlv('26', $gui . $key);

    $txid = preg_replace('/[^A-Za-z0-9]/', '', (string) ($opts['txid'] ?? '')) ?? '';
    if ($txid === '') {
        $txid = $cfg['txid_prefix'] . 'SITE';
    }
    $txid = substr($txid, 0, 25);
    $additional = pix_tlv('62', pix_tlv('05', $txid));

    $payload = pix_tlv('00', '01');
    $payload .= $mai;
    $payload .= pix_tlv('52', '0000');
    $payload .= pix_tlv('53', '986');

    $amount = trim((string) ($opts['amount'] ?? $cfg['amount']));
    if ($amount !== '' && is_numeric($amount) && (float) $amount > 0) {
        $payload .= pix_tlv('54', number_format((float) $amount, 2, '.', ''));
    }

    $payload .= pix_tlv('58', 'BR');
    $payload .= pix_tlv('59', $name);
    $payload .= pix_tlv('60', $city);
    $payload .= $additional;
    $payload .= '6304';
    $payload .= pix_crc16($payload);
    return $payload;
}

function pix_qr_image_url(string $payload, int $size = 240): string
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
        . '&ecc=H&margin=10&data=' . rawurlencode($payload);
}
