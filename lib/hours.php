<?php

declare(strict_types=1);

/**
 * Formata weekdayDescriptions do Google Maps para o rodapé (pt-BR).
 *
 * - Todos os dias iguais → 1 linha ("Aberto 24 horas" ou "Aberto de 9h às 18h")
 * - Horários diferentes → linhas em português (agrupa dias consecutivos iguais)
 *
 * @return list<string>
 */
function hours_format_pt(string $raw): array
{
    $raw = trim($raw);
    if ($raw === '') {
        return [];
    }

    $parsed = hours_parse_weekday_lines($raw);
    if ($parsed === []) {
        // Fallback: traduz texto bruto linha a linha
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            $out[] = hours_translate_free_text($line);
        }
        return $out;
    }

    $schedules = array_column($parsed, 'schedule');
    $unique = array_values(array_unique($schedules));
    if (count($unique) === 1) {
        return [hours_summary_same_all_days($unique[0])];
    }

    return hours_group_consecutive_pt($parsed);
}

/**
 * @return list<array{day:string, schedule:string}>
 */
function hours_parse_weekday_lines(string $raw): array
{
    $dayKeys = [
        'monday' => 'seg',
        'tuesday' => 'ter',
        'wednesday' => 'qua',
        'thursday' => 'qui',
        'friday' => 'sex',
        'saturday' => 'sab',
        'sunday' => 'dom',
        'segunda' => 'seg',
        'terça' => 'ter',
        'terca' => 'ter',
        'quarta' => 'qua',
        'quinta' => 'qui',
        'sexta' => 'sex',
        'sábado' => 'sab',
        'sabado' => 'sab',
        'domingo' => 'dom',
    ];

    $rows = [];
    foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }
        if (!preg_match('/^([A-Za-zçÇáàãâéêíóôõúÁÀÃÂÉÊÍÓÔÕÚ]+(?:-feira)?)\s*:\s*(.+)$/u', $line, $m)) {
            continue;
        }
        $dayRaw = function_exists('mb_strtolower')
            ? mb_strtolower($m[1], 'UTF-8')
            : strtolower($m[1]);
        $dayRaw = str_replace('-feira', '', $dayRaw);
        $dayKey = $dayKeys[$dayRaw] ?? null;
        if ($dayKey === null) {
            continue;
        }
        $rows[] = [
            'day' => $dayKey,
            'schedule' => hours_normalize_schedule($m[2]),
        ];
    }

    return $rows;
}

function hours_normalize_schedule(string $text): string
{
    $t = trim($text);
    $tl = function_exists('mb_strtolower') ? mb_strtolower($t, 'UTF-8') : strtolower($t);

    if (preg_match('/open\s*24\s*hours|24\s*hours|aberto\s*24|24\s*h\b/u', $tl)) {
        return '24h';
    }
    if (preg_match('/^(closed|fechado|closed\s*all\s*day)$/u', $tl)) {
        return 'closed';
    }

    // 6:00 AM – 10:00 PM  |  9:00–18:00  |  9h – 18h
    if (preg_match(
        '/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s*[–—\-àas]+\s*(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/ui',
        $t,
        $m
    )) {
        $start = hours_to_pt_clock((int) $m[1], (int) ($m[2] !== '' ? $m[2] : 0), strtolower((string) ($m[3] ?? '')));
        $end = hours_to_pt_clock((int) $m[4], (int) ($m[5] !== '' ? $m[5] : 0), strtolower((string) ($m[6] ?? '')));
        return $start . '|' . $end;
    }

    return hours_translate_free_text($t);
}

function hours_to_pt_clock(int $h, int $min, string $ampm): string
{
    if ($ampm === 'pm' && $h < 12) {
        $h += 12;
    }
    if ($ampm === 'am' && $h === 12) {
        $h = 0;
    }
    if ($min > 0) {
        return $h . 'h' . str_pad((string) $min, 2, '0', STR_PAD_LEFT);
    }
    return $h . 'h';
}

function hours_summary_same_all_days(string $schedule): string
{
    if ($schedule === '24h') {
        return 'Aberto 24 horas';
    }
    if ($schedule === 'closed') {
        return 'Fechado';
    }
    if (str_contains($schedule, '|')) {
        [$a, $b] = explode('|', $schedule, 2);
        return 'Aberto de ' . $a . ' às ' . $b;
    }
    return 'Aberto: ' . $schedule;
}

/**
 * @param list<array{day:string, schedule:string}> $parsed
 * @return list<string>
 */
function hours_group_consecutive_pt(array $parsed): array
{
    $order = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];
    $labels = [
        'seg' => 'Segunda',
        'ter' => 'Terça',
        'qua' => 'Quarta',
        'qui' => 'Quinta',
        'sex' => 'Sexta',
        'sab' => 'Sábado',
        'dom' => 'Domingo',
    ];

    $byDay = [];
    foreach ($parsed as $row) {
        $byDay[$row['day']] = $row['schedule'];
    }

    $ordered = [];
    foreach ($order as $d) {
        if (isset($byDay[$d])) {
            $ordered[] = ['day' => $d, 'schedule' => $byDay[$d]];
        }
    }
    if ($ordered === []) {
        return [];
    }

    $groups = [];
    $start = $ordered[0]['day'];
    $end = $start;
    $sched = $ordered[0]['schedule'];
    for ($i = 1, $n = count($ordered); $i < $n; $i++) {
        $cur = $ordered[$i];
        $prevIdx = array_search($end, $order, true);
        $curIdx = array_search($cur['day'], $order, true);
        $consecutive = is_int($prevIdx) && is_int($curIdx) && $curIdx === $prevIdx + 1;
        if ($consecutive && $cur['schedule'] === $sched) {
            $end = $cur['day'];
            continue;
        }
        $groups[] = [$start, $end, $sched];
        $start = $end = $cur['day'];
        $sched = $cur['schedule'];
    }
    $groups[] = [$start, $end, $sched];

    $lines = [];
    foreach ($groups as [$a, $b, $s]) {
        $dayLabel = $a === $b
            ? $labels[$a]
            : $labels[$a] . ' a ' . $labels[$b];
        $lines[] = $dayLabel . ': ' . hours_schedule_label_pt($s);
    }
    return $lines;
}

function hours_schedule_label_pt(string $schedule): string
{
    if ($schedule === '24h') {
        return 'Aberto 24 horas';
    }
    if ($schedule === 'closed') {
        return 'fechado';
    }
    if (str_contains($schedule, '|')) {
        [$a, $b] = explode('|', $schedule, 2);
        return $a . ' – ' . $b;
    }
    return $schedule;
}

function hours_translate_free_text(string $text): string
{
    $map = [
        'Monday' => 'Segunda',
        'Tuesday' => 'Terça',
        'Wednesday' => 'Quarta',
        'Thursday' => 'Quinta',
        'Friday' => 'Sexta',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo',
        'Open 24 hours' => 'Aberto 24 horas',
        'Closed' => 'Fechado',
    ];
    $out = strtr($text, $map);
    // 6:00 AM – 10:00 PM residual
    if (preg_match(
        '/(\d{1,2})(?::(\d{2}))?\s*(AM|PM)\s*[–—\-]\s*(\d{1,2})(?::(\d{2}))?\s*(AM|PM)/i',
        $out,
        $m
    )) {
        $start = hours_to_pt_clock((int) $m[1], (int) ($m[2] !== '' ? $m[2] : 0), strtolower($m[3]));
        $end = hours_to_pt_clock((int) $m[4], (int) ($m[5] !== '' ? $m[5] : 0), strtolower($m[6]));
        $out = preg_replace(
            '/(\d{1,2})(?::(\d{2}))?\s*(AM|PM)\s*[–—\-]\s*(\d{1,2})(?::(\d{2}))?\s*(AM|PM)/i',
            $start . ' – ' . $end,
            $out,
            1
        ) ?? $out;
    }
    return $out;
}
