<?php

/**
 * Meta tags Dublin Core da BDTD.
 *
 * PHP version 8
 *
 * @category BDTD
 * @package  Metadata_Vocabularies
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */

namespace Bdtd\MetadataVocabulary;

use VuFind\RecordDriver\AbstractBase as RecordDriver;

/**
 * Acrescenta DC.description (resumo do registro) às meta tags Dublin Core do
 * VuFind. No sistema anterior isso era uma edição do núcleo do VuFind.
 *
 * @category BDTD
 * @package  Metadata_Vocabularies
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */
class DublinCore extends \VuFind\MetadataVocabulary\DublinCore
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vocabFieldToGenericFieldsMap['DC.description'] = 'description';
    }

    /**
     * Dados genéricos do registro, com o resumo.
     *
     * @param RecordDriver $driver Driver do registro
     *
     * @return array
     */
    protected function getGenericData(RecordDriver $driver)
    {
        return parent::getGenericData($driver)
            + ['description' => $driver->tryMethod('getSummary')];
    }
}
