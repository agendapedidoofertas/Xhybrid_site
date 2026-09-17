<?php
require dirname(__DIR__) . '/lib/presets.php';
$map = preset_function_map();
foreach (['extintores', 'mobilidade', 'esquadrias', 'limpeza', 'eletricista'] as $id) {
    if (!isset($map[$id])) {
        fwrite(STDERR, "missing map $id\n");
        exit(1);
    }
    $fn = $map[$id];
    $p = $fn();
    $hero = dirname(__DIR__) . '/assets/presets/' . $id . '/hero.jpg';
    echo $id
        . ' settings=' . count($p['settings'])
        . ' images=' . count($p['images'] ?? [])
        . ' hero=' . (is_file($hero) ? 'ok' : 'MISSING')
        . PHP_EOL;
}
echo "OK\n";
