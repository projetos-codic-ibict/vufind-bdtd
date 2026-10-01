<?php

/**
 * Tema da BDTD (VuFind 11, Bootstrap 5).
 *
 * Estende o bootstrap5 do VuFind. O CSS do legado (style.css, compilado de
 * scss/style.scss, e custom.css) é carregado depois do compiled.css do
 * bootstrap5, sem alteração. bdtd-bs3.css (antes dele) repõe padrões do Bootstrap 3 que
 * o CSS legado pressupõe; bdtd-bs5.css (depois) traz os ajustes de compatibilidade.
 * As fontes são servidas pelo próprio site (bdtd-fonts.css).
 */
return [
    'extends' => 'bootstrap5',
    // Sem 'priority': saem depois do CSS do tema pai, na ordem abaixo.
    'css' => [
        ['file' => 'vendor/unicons/css/line.css'],
        ['file' => 'bdtd-fonts.css'],
        ['file' => 'bdtd-bs3.css'],
        ['file' => 'style.css'],
        ['file' => 'custom.css'],
        ['file' => 'bdtd-bs5.css'],
    ],
    'js' => [
        ['file' => 'bdtd-modal.js'],
    ],
    'favicon' => 'icons/favicon.ico',
    // Matomo com os campos da LA Referencia (identificador OAI, país, repositório)
    'helpers' => [
        'factories' => [
            'Bdtd\View\Helper\Root\Matomo' => 'VuFind\View\Helper\Root\MatomoFactory',
        ],
        'aliases' => [
            'matomo' => 'Bdtd\View\Helper\Root\Matomo',
        ],
    ],
    // Ícones do tema sobre o FontAwesome 7 que já vem no bootstrap5. Uso: $this->icon('bdtd-advanced').
    // Os Unicons do legado (vendor/unicons, carregados acima) são usados direto nos templates,
    // com o markup do legado (<i class="uil uil-...">), para manter as medidas.
    'icons' => [
        'aliases' => [
            'bdtd-advanced' => 'FontAwesome:circle-plus',
        ],
    ],
];
