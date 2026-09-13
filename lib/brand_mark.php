<?php

declare(strict_types=1);

/**
 * Extrai marca curta + complemento a partir do nome sujo do Maps/CRM.
 * Ex.: "SOLUS EMPREITEIRA // ELÉTRICA | …" → short=SOLUS, tag=Empreiteira
 *
 * @return array{short:string,tag:string}
 */
function brand_mark_split(string $name): array
{
    $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
    if ($name === '') {
        return ['short' => '', 'tag' => ''];
    }

    $parts = preg_split('/\s*(?:\/\/|\||·|•|–|—)\s*|\s+-\s+|,\s+/u', $name) ?: [];
    $primary = trim((string) ($parts[0] ?? $name));
    $primary = trim($primary, " \t\n\r\0\x0B.,;:|/\\");

    $words = preg_split('/\s+/u', $primary) ?: [];
    $words = array_values(array_filter($words, static fn ($w) => $w !== ''));
    if ($words === []) {
        return ['short' => '', 'tag' => ''];
    }

    $cut = static function (string $s, int $max): string {
        if (function_exists('mb_substr')) {
            return mb_substr($s, 0, $max, 'UTF-8');
        }
        if (preg_match('/^./us', $s) !== 1) {
            return substr($s, 0, $max);
        }
        $out = '';
        $n = 0;
        if (preg_match_all('/./us', $s, $m)) {
            foreach ($m[0] as $ch) {
                if ($n >= $max) {
                    break;
                }
                $out .= $ch;
                $n++;
            }
        }
        return $out;
    };

    $len = static function (string $s): int {
        if (function_exists('mb_strlen')) {
            return mb_strlen($s, 'UTF-8');
        }
        return preg_match_all('/./us', $s) ?: strlen($s);
    };

    // Upper/title sem corromper UTF-8 quando mbstring não existe
    $upper = static function (string $w) use ($cut): string {
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($w, 'UTF-8');
        }
        return preg_replace_callback('/[a-z]/u', static fn ($m) => strtoupper($m[0]), $w) ?? $w;
    };

    $title = static function (string $w): string {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case(mb_strtolower($w, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }
        $lower = preg_replace_callback('/[A-Z]/u', static fn ($m) => strtolower($m[0]), $w) ?? $w;
        if ($lower === '') {
            return '';
        }
        if (preg_match('/^(.)(.*)$/us', $lower, $m)) {
            $first = preg_replace_callback('/[a-z]/u', static fn ($x) => strtoupper($x[0]), $m[1]) ?? $m[1];
            return $first . $m[2];
        }
        return $lower;
    };

    $short = $cut($words[0], 18);
    $tag = '';
    if (isset($words[1]) && $len($words[1]) <= 18) {
        $tag = $cut($title($words[1]), 16);
    } elseif (count($words) === 1 && isset($parts[1])) {
        $rest = preg_split('/\s+/u', trim((string) $parts[1])) ?: [];
        $rest = array_values(array_filter($rest, static fn ($w) => $w !== ''));
        if ($rest !== [] && $len($rest[0]) <= 18) {
            $w0 = $rest[0];
            // Siglas curtas: maiúsculas; o resto title-case (CSS do site usa uppercase no mark)
            $tag = $len($w0) <= 4 ? $cut($upper($w0), 16) : $cut($title($w0), 16);
        }
    }

    return ['short' => $short, 'tag' => $tag];
}

/**
 * HTML seguro para listas admin (marca + tag).
 */
function brand_mark_html(string $companyName, callable $h): string
{
    $m = brand_mark_split($companyName);
    if ($m['short'] === '') {
        return $h($companyName);
    }
    $out = '<strong class="brand-mark">' . $h($m['short']) . '</strong>';
    if ($m['tag'] !== '') {
        $out .= ' <span class="brand-mark-tag text-muted">' . $h($m['tag']) . '</span>';
    }
    return $out;
}
