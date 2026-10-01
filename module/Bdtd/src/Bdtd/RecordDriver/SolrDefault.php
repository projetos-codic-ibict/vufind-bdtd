<?php

/**
 * Driver de registro da BDTD.
 *
 * PHP version 8
 *
 * @category BDTD
 * @package  RecordDrivers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */

namespace Bdtd\RecordDriver;

use VuFind\RecordDriver\Response\PublicationDetails;

/**
 * Estende o SolrDefault do VuFind com os campos de teses e dissertações
 * (índice no formato LA Referencia / DSpace). Os métodos entram por partes.
 *
 * @category BDTD
 * @package  RecordDrivers
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/projetos-codic-ibict/vufind-bdtd
 */
class SolrDefault extends \VuFind\RecordDriver\SolrDefault
{
    /**
     * Texto exibido quando a instituição não informou um campo.
     *
     * @var string
     */
    public const NA_MESSAGE = 'Não Informado pela instituição';

    /**
     * Classe com os campos exibidos na página do registro.
     *
     * @return ?string
     */
    public function getRecordDataFormatterSpecClass(): ?string
    {
        return \Bdtd\RecordDataFormatter\Specs\Bdtd::class;
    }

    /**
     * Valores, sem repetição, de um conjunto de campos do Solr.
     *
     * @param array $fields Campos
     *
     * @return array
     */
    public function getFieldsValuesDefault(array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            if (isset($this->fields[$field])) {
                $values = array_merge($values, (array)$this->fields[$field]);
            }
        }
        return array_values(array_unique($values));
    }

    /**
     * Valores de um conjunto de campos; sem valores, devolve NA_MESSAGE.
     *
     * @param array $fields    Campos
     * @param bool  $naMessage Devolver NA_MESSAGE quando não houver valores?
     *
     * @return array
     */
    public function getFieldsValues(array $fields, bool $naMessage = true): array
    {
        $values = $this->getFieldsValuesDefault($fields);
        if (!$values && $naMessage) {
            $values[] = self::NA_MESSAGE;
        }
        return $values;
    }

    /**
     * Primeiro valor de um campo (ou NA_MESSAGE).
     *
     * @param string $field Campo
     *
     * @return string
     */
    public function getFieldValue(string $field): string
    {
        return $this->getFieldsValues([$field])[0];
    }

    /**
     * Autores sem repetição, com o perfil Lattes de cada um.
     *
     * @param array $dataFields Dados extras por autor (ver getAuthorDataFields)
     *
     * @return array
     */
    public function getDeduplicatedAuthors($dataFields = ['profile'])
    {
        return parent::getDeduplicatedAuthors($dataFields);
    }

    /**
     * Perfis Lattes dos autores.
     *
     * @return array
     */
    public function getPrimaryAuthorsProfiles(): array
    {
        return $this->getFieldsValues(['dc.contributor.authorLattes.fl_str_mv'], false);
    }

    /**
     * Orientadores, coorientadores e banca, cada um com os dados extras pedidos
     * (por padrão, o perfil Lattes). Usa get<Tipo>Authors e get<Tipo>AuthorsProfiles.
     *
     * @param array $dataFields Dados extras por pessoa
     *
     * @return array
     */
    public function getContributors(array $dataFields = ['profile']): array
    {
        $contributors = [];
        foreach (['advisor', 'coadvisor', 'referee'] as $type) {
            $contributors[$type] = $this->getAuthorDataFields($type, $dataFields);
        }
        return $contributors;
    }

    /**
     * Orientadores.
     *
     * @return array
     */
    public function getAdvisorAuthors(): array
    {
        return $this->getFieldsValues(
            ['dc.contributor.advisor1.fl_str_mv', 'dc.contributor.advisor2.fl_str_mv']
        );
    }

    /**
     * Perfis Lattes dos orientadores.
     *
     * @return array
     */
    public function getAdvisorAuthorsProfiles(): array
    {
        return $this->getFieldsValues(
            ['dc.contributor.advisor1Lattes.fl_str_mv', 'dc.contributor.advisor2Lattes.fl_str_mv'],
            false
        );
    }

    /**
     * Coorientadores.
     *
     * @return array
     */
    public function getCoadvisorAuthors(): array
    {
        return $this->getFieldsValues(['dc.contributor.co.fl_str_mv'], false);
    }

    /**
     * Perfis Lattes dos coorientadores.
     *
     * @return array
     */
    public function getCoadvisorAuthorsProfiles(): array
    {
        return $this->getFieldsValues(
            ['dc.contributor.advisor-co1Lattes.fl_str_mv', 'dc.contributor.advisor-co2Lattes.fl_str_mv'],
            false
        );
    }

    /**
     * Membros da banca.
     *
     * @return array
     */
    public function getRefereeAuthors(): array
    {
        return $this->getFieldsValues($this->numberedFields('dc.contributor.referee%d.fl_str_mv', 5));
    }

    /**
     * Perfis Lattes dos membros da banca.
     *
     * @return array
     */
    public function getRefereeAuthorsProfiles(): array
    {
        return $this->getFieldsValues(
            $this->numberedFields('dc.contributor.referee%dLattes.fl_str_mv', 5),
            false
        );
    }

    /**
     * Assuntos de um campo, no formato de getAllSubjectHeadings().
     *
     * @param string $field    Campo do Solr
     * @param string $type     Tipo do assunto
     * @param string $source   Vocabulário de origem
     * @param bool   $extended Formato estendido (heading, type, source)?
     *
     * @return array
     */
    public function getSubjectsByField(string $field, string $type, string $source, bool $extended = false): array
    {
        return array_map(
            fn ($heading) => $extended
                ? ['heading' => [$heading], 'type' => $type, 'source' => $source]
                : [$heading],
            $this->getFieldsValues([$field], false)
        );
    }

    /**
     * Todos os assuntos: CNPq, inglês, espanhol e português.
     *
     * @param bool $extended Formato estendido?
     *
     * @return array
     */
    public function getAllSubjectHeadings($extended = false)
    {
        return array_merge(
            $this->getSubjectsByField('dc.subject.cnpq.fl_str_mv', 'cnpq', 'cnpq', $extended),
            $this->getSubjectsByField('dc.subject.eng.fl_str_mv', 'original', 'eng', $extended),
            $this->getSubjectsByField('dc.subject.spa.fl_str_mv', 'original', 'spa', $extended),
            $this->getSubjectsByField('dc.subject.por.fl_str_mv', 'original', 'por', $extended)
        );
    }

    /**
     * Assuntos CNPq.
     *
     * @return array
     */
    public function getCNPQSubjects(): array
    {
        return $this->getSubjectsByField('dc.subject.cnpq.fl_str_mv', 'cnpq', 'cnpq');
    }

    /**
     * Assuntos em inglês.
     *
     * @return array
     */
    public function getEngSubjects(): array
    {
        return $this->getSubjectsByField('dc.subject.eng.fl_str_mv', 'original', 'eng');
    }

    /**
     * Assuntos em espanhol.
     *
     * @return array
     */
    public function getSpaSubjects(): array
    {
        return $this->getSubjectsByField('dc.subject.spa.fl_str_mv', 'original', 'spa');
    }

    /**
     * Assuntos em português.
     *
     * @return array
     */
    public function getPorSubjects(): array
    {
        return $this->getSubjectsByField('dc.subject.por.fl_str_mv', 'original', 'por');
    }

    /**
     * Converte nomes em objetos PublicationDetails (usados pelo template de
     * detalhes de publicação do VuFind).
     *
     * @param array $names Nomes
     *
     * @return PublicationDetails[]
     */
    public function getPublicationDetailsByPublishers(array $names): array
    {
        return array_map(fn ($name) => new PublicationDetails('', $name, ''), $names);
    }

    /**
     * Instituição de defesa.
     *
     * @return PublicationDetails[]
     */
    public function getRootPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.none.fl_str_mv');
    }

    /**
     * Programa de pós-graduação.
     *
     * @return PublicationDetails[]
     */
    public function getProgramPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.program.fl_str_mv');
    }

    /**
     * Departamento.
     *
     * @return PublicationDetails[]
     */
    public function getDepartmentPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.department.fl_str_mv');
    }

    /**
     * País.
     *
     * @return PublicationDetails[]
     */
    public function getCountryPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.country.fl_str_mv');
    }

    /**
     * Área do conhecimento.
     *
     * @return PublicationDetails[]
     */
    public function getKnowledgeareaPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.knowledgearea.fl_str_mv');
    }

    /**
     * Código do programa.
     *
     * @return PublicationDetails[]
     */
    public function getProgramIDPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.programID.fl_str_mv');
    }

    /**
     * Área de avaliação.
     *
     * @return PublicationDetails[]
     */
    public function getAreaAvaliacaoPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.areaavaliacao.fl_str_mv');
    }

    /**
     * Grande área.
     *
     * @return PublicationDetails[]
     */
    public function getGrandeAreaPublishers(): array
    {
        return $this->publishersFrom('dc.publisher.grandearea.fl_str_mv');
    }

    /**
     * Tipo de acesso (aberto, embargado etc.).
     *
     * @return PublicationDetails[]
     */
    public function getAccessType(): array
    {
        return $this->publishersFrom('eu_rights_str_mv');
    }

    /**
     * Nível de acesso (primeiro valor).
     *
     * @return string
     */
    public function getAccessLevel(): string
    {
        return $this->getFieldValue('eu_rights_str_mv');
    }

    /**
     * Identificador persistente dARK. Registros recuperados da oasisbr-api
     * podem não ter o campo.
     *
     * @return string
     */
    public function getDarkID(): string
    {
        return $this->getFieldsValuesDefault(['dc.identifier.dark.fl_str_mv'])[0] ?? '';
    }

    /**
     * Resumo em português.
     *
     * @return array
     */
    public function getAbstractPor(): array
    {
        return $this->getFieldsValues(['dc.description.resumo.por.fl_txt_mv'], false);
    }

    /**
     * Resumo em inglês.
     *
     * @return array
     */
    public function getAbstractEng(): array
    {
        return $this->getFieldsValues(['dc.description.abstract.eng.fl_txt_mv'], false);
    }

    /**
     * Resumo em espanhol (no legado o método se chamava getAbstracSpa e o
     * resumo em espanhol nunca aparecia).
     *
     * @return array
     */
    public function getAbstractSpa(): array
    {
        return $this->getFieldsValues(['dc.description.abstract.spa.fl_txt_mv'], false);
    }

    /**
     * Citação informada pela instituição.
     *
     * @return array
     */
    public function getCitation(): array
    {
        return $this->getFieldsValues(['dc.identifier.citation.fl_str_mv']);
    }

    /**
     * Links de acesso ao documento. As URLs vêm dos metadados coletados das
     * instituições e vão direto para o href: só http e https são aceitos
     * (descarta javascript:, data: etc.).
     *
     * @return array
     */
    public function getURLs()
    {
        return array_values(
            array_filter(parent::getURLs(), fn ($link) => $this->isHttpUrl($link['url'] ?? ''))
        );
    }

    /**
     * Identificador OAI-PMH do registro na origem.
     *
     * @return ?string
     */
    public function getIdentifierOAI(): ?string
    {
        return $this->getFieldsValuesDefault(['oai_identifier_str'])[0] ?? null;
    }

    /**
     * Identificador do repositório de origem.
     *
     * @return ?string
     */
    public function getRepositoryID(): ?string
    {
        return $this->getFieldsValuesDefault(['repository_id_str'])[0] ?? null;
    }

    /**
     * Nomes de campos numerados (ex.: referee1 a referee5).
     *
     * @param string $pattern Padrão sprintf com %d
     * @param int    $count   Quantidade
     *
     * @return array
     */
    protected function numberedFields(string $pattern, int $count): array
    {
        return array_map(fn ($i) => sprintf($pattern, $i), range(1, $count));
    }

    /**
     * A URL é http ou https?
     *
     * @param mixed $url URL
     *
     * @return bool
     */
    protected function isHttpUrl($url): bool
    {
        return is_string($url) && preg_match('~^https?://~i', trim($url)) === 1;
    }

    /**
     * Valores de um campo como PublicationDetails (ou NA_MESSAGE).
     *
     * @param string $field Campo
     *
     * @return PublicationDetails[]
     */
    protected function publishersFrom(string $field): array
    {
        return $this->getPublicationDetailsByPublishers($this->getFieldsValues([$field]));
    }
}
