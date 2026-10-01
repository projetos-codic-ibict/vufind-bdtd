<?php

/**
 * Campos exibidos no registro da BDTD.
 *
 * PHP version 8
 *
 * @category BDTD
 * @package  RecordDataFormatter
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */

namespace Bdtd\RecordDataFormatter\Specs;

use VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder;

/**
 * Campos e ordem da página do registro, portados do RecordDataFormatterFactory
 * do sistema anterior. No VuFind 11 as especificações ficam numa classe
 * escolhida pelo driver (getRecordDataFormatterSpecClass).
 *
 * @category BDTD
 * @package  RecordDataFormatter
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */
class Bdtd extends \VuFind\RecordDataFormatter\Specs\DefaultRecord
{
    /**
     * Campos principais do registro.
     *
     * @return array
     */
    protected function getDefaultCoreSpecs(): array
    {
        $spec = new SpecBuilder();
        $lattes = [['name' => 'profile', 'prefix' => '']];
        $roles = [['name' => 'role', 'prefix' => 'CreatorRoles::']];

        $spec->setTemplateLine('Published in', 'getContainerTitle', 'data-containerTitle.phtml');
        $spec->setLine('New Title', 'getNewerTitles', null, ['recordLink' => 'title']);
        $spec->setLine('Previous Title', 'getPreviousTitles', null, ['recordLink' => 'title']);
        $spec->setLine('Defense year', 'getPublicationDates');
        $this->setPeopleLine(
            $spec,
            'Authors',
            'getDeduplicatedAuthors',
            'primary',
            'author',
            $lattes,
            fn ($data) => count($data['primary'] ?? []) > 1 ? 'Main Authors' : 'Main Author'
        );
        $this->setPeopleLine(
            $spec,
            'Corporate Authors',
            'getDeduplicatedAuthors',
            'corporate',
            'creator',
            $roles,
            fn ($data) => count($data['corporate'] ?? []) > 1 ? 'Corporate Authors' : 'Corporate Author'
        );
        $this->setPeopleLine($spec, 'Other Authors', 'getDeduplicatedAuthors', 'secondary', 'contributor', $roles);
        $this->setPeopleLine($spec, 'Advisors', 'getContributors', 'advisor', 'contributor', $lattes, fn () => 'Advisor');
        $this->setPeopleLine(
            $spec,
            'Co-advisors',
            'getContributors',
            'coadvisor',
            'contributor',
            $lattes,
            fn () => 'Co-advisor'
        );
        $this->setPeopleLine($spec, 'Referees', 'getContributors', 'referee', 'contributor', $lattes, fn () => 'Referee');
        $spec->setLine('Format', 'getFormats', 'RecordHelper', ['helperMethod' => 'getFormatList']);
        $spec->setLine('Access type', 'getAccessLevel', null, ['translate' => true]);
        $spec->setTemplateLine('dARK ID', 'getDarkID', 'data-darkId.phtml');
        // Código do idioma sem tradução ("por"), como no legado
        $spec->setLine('Language', 'getLanguages');
        $spec->setLine('Institution', 'getRootPublishers');
        $spec->setLine('Program', 'getProgramPublishers');
        $spec->setLine('Department', 'getDepartmentPublishers');
        $spec->setLine('Country', 'getCountryPublishers');
        $spec->setLine(
            'Edition',
            'getEdition',
            null,
            ['itemPrefix' => '<span property="bookEdition">', 'itemSuffix' => '</span>']
        );
        $spec->setTemplateLine('Series', 'getSeries', 'data-series.phtml');
        $spec->setTemplateLine('Portuguese Subjects', 'getPorSubjects', 'data-allSubjectHeadings.phtml');
        $spec->setTemplateLine('English Subjects', 'getEngSubjects', 'data-allSubjectHeadings.phtml');
        $spec->setTemplateLine('Spanish Subjects', 'getSpaSubjects', 'data-allSubjectHeadings.phtml');
        $spec->setTemplateLine('CNPq Subject', 'getCNPQSubjects', 'data-allSubjectHeadings.phtml');
        $spec->setLine('Abstract', 'getAbstractPor');
        $spec->setLine('English Abstract', 'getAbstractEng');
        $spec->setLine('Spanish Abstract', 'getAbstractSpa');
        $spec->setTemplateLine(
            'child_records',
            'getChildRecordCount',
            'data-childRecords.phtml',
            ['allowZero' => false]
        );
        $spec->setTemplateLine('Access link', true, 'data-onlineAccess.phtml');
        $spec->setTemplateLine('Related Items', 'getAllRecordLinks', 'data-allRecordLinks.phtml');
        // Resumo como última linha da tabela (o legado o tirou de baixo do título)
        $spec->setLine('Summary', 'getSummary');
        return $spec->getArray();
    }

    /**
     * Campos da aba de descrição.
     *
     * @return array
     */
    protected function getDefaultDescriptionSpecs(): array
    {
        $spec = new SpecBuilder();
        $spec->setLine('Citation', 'getCitation');
        $spec->setLine('Summary', 'getSummary');
        $spec->setLine('Portuguese Abstract', 'getAbstractPor');
        $spec->setLine('English Abstract', 'getAbstractEng');
        $spec->setLine('Spanish Abstract', 'getAbstractSpa');
        return $spec->getArray();
    }

    /**
     * Linha de pessoas (autores, orientadores, banca) no template de autores.
     *
     * @param SpecBuilder $spec       Especificação
     * @param string      $key        Rótulo da linha
     * @param string      $method     Método do driver
     * @param string      $type       Grupo de pessoas (primary, advisor etc.)
     * @param string      $schema     Propriedade schema.org
     * @param array       $dataFields Dados extras exibidos por pessoa
     * @param ?callable   $label      Rótulo dinâmico
     *
     * @return void
     */
    protected function setPeopleLine(
        SpecBuilder $spec,
        string $key,
        string $method,
        string $type,
        string $schema,
        array $dataFields,
        ?callable $label = null
    ): void {
        $options = [
            'useCache' => true,
            'context' => [
                'type' => $type,
                'schemaLabel' => $schema,
                'requiredDataFields' => $dataFields,
            ],
        ];
        if ($label) {
            $options['labelFunction'] = $label;
        }
        $spec->setTemplateLine($key, $method, 'data-authors.phtml', $options);
    }
}
