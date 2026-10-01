<?php

/**
 * Módulo da BDTD (Biblioteca Digital Brasileira de Teses e Dissertações).
 *
 * PHP version 8
 *
 * @category BDTD
 * @package  Module
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */

namespace Bdtd;

/**
 * Carregado pela variável VUFIND_LOCAL_MODULES=Bdtd. O autoload das classes vem
 * do composer.local.json (psr-4 "Bdtd\\" em module/Bdtd/src/Bdtd).
 *
 * @category BDTD
 * @package  Module
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */
class Module
{
    /**
     * Configuração do módulo.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
