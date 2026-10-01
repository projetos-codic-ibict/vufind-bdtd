<?php

/**
 * Matomo da BDTD.
 *
 * PHP version 8
 *
 * @category BDTD
 * @package  View_Helpers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */

namespace Bdtd\View\Helper\Root;

use VuFind\RecordDriver\AbstractBase as RecordDriverBase;

/**
 * Acrescenta aos dados da página do registro o identificador OAI, o país e o
 * repositório, usados pela LA Referencia nas estatísticas de uso por repositório
 * (o legado fazia o mesmo no helper Piwik).
 *
 * @category BDTD
 * @package  View_Helpers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */
class Matomo extends \VuFind\View\Helper\Root\Matomo
{
    /**
     * Código ISO do país (config.ini [Matomo] country_iso).
     *
     * @var string
     */
    protected string $countryIso;

    /**
     * Constructor.
     *
     * @param \VuFind\Config\Config                $config  VuFind configuration
     * @param \Laminas\Router\Http\TreeRouteStack  $router  Router
     * @param \Laminas\Http\PhpEnvironment\Request $request Request
     */
    public function __construct(
        \VuFind\Config\Config $config,
        \Laminas\Router\Http\TreeRouteStack $router,
        \Laminas\Http\PhpEnvironment\Request $request
    ) {
        parent::__construct($config, $router, $request);
        $this->countryIso = (string)($config->Matomo->country_iso ?? '');
    }

    /**
     * Dados da página do registro, com os campos da LA Referencia (só os que existem).
     *
     * @param RecordDriverBase $recordDriver Record driver
     *
     * @return array
     */
    protected function getRecordPageCustomData(RecordDriverBase $recordDriver): array
    {
        $extra = [
            'oaipmhID' => (string)$recordDriver->tryMethod('getIdentifierOAI'),
            'countryID' => $this->countryIso,
            'repositoryID' => (string)$recordDriver->tryMethod('getRepositoryID'),
        ];
        return parent::getRecordPageCustomData($recordDriver)
            + array_filter($extra, fn ($value) => $value !== '');
    }
}
