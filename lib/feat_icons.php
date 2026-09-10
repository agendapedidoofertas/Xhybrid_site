<?php

declare(strict_types=1);

/**
 * Catálogo de ícones dos cards “O que fazemos”.
 * IDs devem bater com js/feat-icons.js
 *
 * @return array<string, string> id => rótulo
 */
function feat_icon_catalog(): array
{
    return [
        'layout' => 'Layout / site',
        'globe' => 'Globo / web',
        'code' => 'Código',
        'cpu' => 'Chip / TI',
        'server' => 'Servidor',
        'wrench' => 'Ferramenta / manutenção',
        'bolt' => 'Raio / eletricista',
        'plug' => 'Tomada / elétrica',
        'sparkles' => 'Brilho / limpeza',
        'droplets' => 'Gotas / limpeza',
        'flame' => 'Chama / extintores',
        'shield' => 'Escudo / segurança',
        'utensils' => 'Talheres / restaurante',
        'coffee' => 'Café / food',
        'pill' => 'Comprimido / farmácia',
        'cross' => 'Cruz / saúde',
        'stethoscope' => 'Estetoscópio / clínica',
        'scale' => 'Balança / advocacia',
        'briefcase' => 'Pasta / escritório',
        'gavel' => 'Martelo / jurídico',
        'scissors' => 'Tesoura / beleza',
        'paw' => 'Pata / pet',
        'home' => 'Casa / imóveis',
        'building' => 'Prédio',
        'car' => 'Carro / auto',
        'shopping' => 'Sacola / loja',
        'package' => 'Pacote / entrega',
        'wifi' => 'Wi‑Fi / rede',
        'phone' => 'Telefone',
        'camera' => 'Câmera',
        'dumbbell' => 'Academia',
        'leaf' => 'Folha / eco',
        'truck' => 'Caminhão / logística',
        'hammer' => 'Martelo / obras',
        'paintbrush' => 'Pincel / pintura',
    ];
}

/**
 * Grupos da biblioteca visual (admin).
 *
 * @return array<string, array{label: string, icons: list<string>}>
 */
function feat_icon_categories(): array
{
    return [
        'digital' => [
            'label' => 'Digital / TI',
            'icons' => ['layout', 'globe', 'code', 'cpu', 'server', 'wifi', 'phone', 'camera'],
        ],
        'servicos' => [
            'label' => 'Serviços',
            'icons' => ['wrench', 'hammer', 'paintbrush', 'truck', 'package', 'building', 'home'],
        ],
        'eletrica' => [
            'label' => 'Elétrica / segurança',
            'icons' => ['bolt', 'plug', 'flame', 'shield'],
        ],
        'limpeza' => [
            'label' => 'Limpeza',
            'icons' => ['sparkles', 'droplets', 'leaf'],
        ],
        'saude' => [
            'label' => 'Saúde',
            'icons' => ['stethoscope', 'cross', 'pill', 'dumbbell'],
        ],
        'food' => [
            'label' => 'Food',
            'icons' => ['utensils', 'coffee'],
        ],
        'juridico' => [
            'label' => 'Jurídico / negócios',
            'icons' => ['scale', 'gavel', 'briefcase', 'shopping'],
        ],
        'outros' => [
            'label' => 'Outros',
            'icons' => ['scissors', 'paw', 'car'],
        ],
    ];
}

/** @return list<string> */
function feat_icon_ids(): array
{
    return array_keys(feat_icon_catalog());
}
