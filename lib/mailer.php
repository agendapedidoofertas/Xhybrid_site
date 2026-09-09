<?php

declare(strict_types=1);

/**
 * SMTP simples via sockets (STARTTLS / SSL). Sem Composer.
 *
 * @param array{host:string,port:int,user:string,pass:string,from:string,to:string,subject:string,body:string} $opts
 * @return array{ok:bool,error?:string}
 */
function mailer_send(array $opts): array
{
    $host = trim((string) ($opts['host'] ?? ''));
    $port = (int) ($opts['port'] ?? 587);
    $user = (string) ($opts['user'] ?? '');
    $pass = (string) ($opts['pass'] ?? '');
    $from = trim((string) ($opts['from'] ?? ''));
    $to = trim((string) ($opts['to'] ?? ''));
    $subject = (string) ($opts['subject'] ?? 'Contato do site');
    $body = (string) ($opts['body'] ?? '');

    if ($host === '' || $from === '' || $to === '') {
        return ['ok' => false, 'error' => 'SMTP incompleto (host, remetente ou destino).'];
    }
    if (!filter_var($from, FILTER_VALIDATE_EMAIL) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'E-mail remetente/destino inválido.'];
    }

    $remote = ($port === 465 ? 'ssl://' : '') . $host;
    $errno = 0;
    $errstr = '';
    $fp = @stream_socket_client(
        $remote . ':' . $port,
        $errno,
        $errstr,
        20,
        STREAM_CLIENT_CONNECT,
        stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]])
    );
    if (!$fp) {
        return ['ok' => false, 'error' => 'Não conectou ao SMTP: ' . $errstr];
    }
    stream_set_timeout($fp, 20);

    $read = static function () use ($fp): string {
        $data = '';
        while ($line = fgets($fp, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $cmd = static function (string $line) use ($fp, $read): string {
        fwrite($fp, $line . "\r\n");
        return $read();
    };

    $greet = $read();
    if (!str_starts_with($greet, '220')) {
        fclose($fp);
        return ['ok' => false, 'error' => 'SMTP rejeitou a conexão.'];
    }

    $ehloHost = 'localhost';
    $resp = $cmd('EHLO ' . $ehloHost);
    if ($port !== 465 && (str_contains($resp, 'STARTTLS') || str_contains($resp, 'starttls'))) {
        $tls = $cmd('STARTTLS');
        if (!str_starts_with($tls, '220')) {
            fclose($fp);
            return ['ok' => false, 'error' => 'STARTTLS falhou.'];
        }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp);
            return ['ok' => false, 'error' => 'TLS não iniciou.'];
        }
        $resp = $cmd('EHLO ' . $ehloHost);
    }

    if ($user !== '') {
        $cmd('AUTH LOGIN');
        $cmd(base64_encode($user));
        $auth = $cmd(base64_encode($pass));
        if (!str_starts_with($auth, '235')) {
            fclose($fp);
            return ['ok' => false, 'error' => 'Autenticação SMTP falhou.'];
        }
    }

    $mailFrom = $cmd('MAIL FROM:<' . $from . '>');
    if (!str_starts_with($mailFrom, '250')) {
        fclose($fp);
        return ['ok' => false, 'error' => 'MAIL FROM rejeitado.'];
    }
    $rcpt = $cmd('RCPT TO:<' . $to . '>');
    if (!str_starts_with($rcpt, '250') && !str_starts_with($rcpt, '251')) {
        fclose($fp);
        return ['ok' => false, 'error' => 'RCPT TO rejeitado.'];
    }
    $dataCmd = $cmd('DATA');
    if (!str_starts_with($dataCmd, '354')) {
        fclose($fp);
        return ['ok' => false, 'error' => 'DATA rejeitado.'];
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [
        'From: ' . $from,
        'To: ' . $to,
        'Subject: ' . $encodedSubject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'Date: ' . date('r'),
    ];
    $safeBody = str_replace(["\r\n.", "\n."], ["\r\n..", "\n.."], str_replace(["\r\n", "\r"], "\n", $body));
    fwrite($fp, implode("\r\n", $headers) . "\r\n\r\n" . $safeBody . "\r\n.\r\n");
    $final = $read();
    $cmd('QUIT');
    fclose($fp);

    if (!str_starts_with($final, '250')) {
        return ['ok' => false, 'error' => 'Servidor não aceitou a mensagem.'];
    }
    return ['ok' => true];
}

function mailer_configured(array $settings): bool
{
    return trim((string) ($settings['smtp_host'] ?? '')) !== ''
        && trim((string) ($settings['smtp_user'] ?? '')) !== '';
}
