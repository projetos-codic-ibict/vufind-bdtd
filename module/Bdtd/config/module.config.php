<?php

/**
 * Configuração do módulo Bdtd.
 */

namespace Bdtd\Module\Configuration;

return [
    // Páginas institucionais: mesmos endereços do legado, exibidas pelo ContentController
    // do VuFind (templates em themes/bdtd/templates/content/<página>.phtml)
    'router' => [
        'routes' => [
            'about-home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/about/home',
                    'defaults' => ['controller' => 'Content', 'action' => 'Content', 'page' => 'about'],
                ],
            ],
            'participate-home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/Participate/Home',
                    'defaults' => ['controller' => 'Content', 'action' => 'Content', 'page' => 'participate'],
                ],
            ],
            'diretrizes-home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/Diretrizes/Home',
                    'defaults' => ['controller' => 'Content', 'action' => 'Content', 'page' => 'diretrizes'],
                ],
            ],
            'technology-home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/Technology/Home',
                    'defaults' => ['controller' => 'Content', 'action' => 'Content', 'page' => 'technology'],
                ],
            ],
            'faq-home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/faq/home',
                    'defaults' => ['controller' => 'Content', 'action' => 'Content', 'page' => 'faq'],
                ],
            ],
            'datasources-home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/datasources/home',
                    'defaults' => ['controller' => 'Content', 'action' => 'Content', 'page' => 'datasources'],
                ],
            ],
            'indicators-home' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/indicators/home',
                    'defaults' => ['controller' => 'Content', 'action' => 'Content', 'page' => 'indicators'],
                ],
            ],
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            // Registros do Solr passam a usar o driver da BDTD
            'recorddriver' => [
                'factories' => [
                    \Bdtd\RecordDriver\SolrDefault::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
                ],
                'aliases' => [
                    'solrdefault' => \Bdtd\RecordDriver\SolrDefault::class,
                    \VuFind\RecordDriver\SolrDefault::class => \Bdtd\RecordDriver\SolrDefault::class,
                ],
            ],
            // Meta tags Dublin Core com o resumo (DC.description)
            'metadatavocabulary' => [
                'factories' => [
                    \Bdtd\MetadataVocabulary\DublinCore::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                ],
                'aliases' => [
                    'dublincore' => \Bdtd\MetadataVocabulary\DublinCore::class,
                ],
            ],
            // Campos exibidos na página do registro (escolhidos pelo driver)
            'recorddataformatter_specs' => [
                'factories' => [
                    \Bdtd\RecordDataFormatter\Specs\Bdtd::class
                        => \VuFind\RecordDataFormatter\Specs\DefaultRecordFactory::class,
                ],
            ],
        ],
    ],
];
