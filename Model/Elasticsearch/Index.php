<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch;

use Freento\SqlLog\Exception\CouldNotCreateElasticClient;
use Freento\SqlLog\Exception\IncorrectLocalElasticConfigs;
use Freento\SqlLog\Exception\NotFoundLocalElasticConfigs;

class Index
{
    public const INDEX_NAME = 'sql_log_queries';
    public const INDEX_FIELD_AFFECTED_ROWS = 'affected_rows';
    public const INDEX_FIELD_BIND = 'bind';
    public const INDEX_FIELD_BIND_QUERY = 'bind_query';
    public const INDEX_FIELD_CALLER = 'caller';
    public const INDEX_FIELD_ERROR = 'error';
    public const INDEX_FIELD_EXECUTING_TIME = 'executing_time';
    public const INDEX_FIELD_QUERY = 'query';
    public const INDEX_FIELD_REQUEST_DATE = 'request_date';
    public const INDEX_FIELD_REQUEST_NAME = 'request_name';
    public const INDEX_FIELD_START_TIME = 'start_time';
    public const INDEX_FIELD_TRACE = 'trace';
    public const INDEX_FIELD_TYPE = 'type';

    /**
     * @param ClientResolver $clientResolver
     */
    public function __construct(private readonly ClientResolver $clientResolver)
    {
    }

    /**
     * Create index, if index exist - delete it and create a new one
     *
     * @return void
     * @throws CouldNotCreateElasticClient
     * @throws IncorrectLocalElasticConfigs
     * @throws NotFoundLocalElasticConfigs
     */
    public function reCreate(): void
    {
        $this->clientResolver->resolve()->recreateIndex(self::INDEX_NAME, $this->getIndexSettings());
    }

    /**
     * Get index settings
     *
     * @return mixed[]
     */
    private function getIndexSettings(): array
    {
        return [
            'mappings' => [
                'properties' => [
                    self::INDEX_FIELD_AFFECTED_ROWS => ['type' => 'long'],
                    self::INDEX_FIELD_BIND => ['type' => 'text'],
                    self::INDEX_FIELD_BIND_QUERY => $this->getBaseTextSettings(2048),
                    self::INDEX_FIELD_CALLER => $this->getBaseTextSettings(256),
                    self::INDEX_FIELD_ERROR => $this->getBaseTextSettings(256),
                    self::INDEX_FIELD_EXECUTING_TIME => $this->getBaseTextSettings(32),
                    self::INDEX_FIELD_QUERY => $this->getBaseTextSettings(2048),
                    self::INDEX_FIELD_REQUEST_DATE => $this->getBaseTextSettings(32),
                    self::INDEX_FIELD_REQUEST_NAME => $this->getBaseTextSettings(256),
                    self::INDEX_FIELD_START_TIME => $this->getBaseTextSettings(32),
                    self::INDEX_FIELD_TRACE => [
                        'properties' => [
                            'args' => $this->getBaseTextSettings(256),
                            'class' => $this->getBaseTextSettings(256),
                            'file' => $this->getBaseTextSettings(256),
                            'function' => $this->getBaseTextSettings(256),
                            'line' => ['type' => 'long'],
                            'type' => $this->getBaseTextSettings(256)
                        ],
                    ],
                    self::INDEX_FIELD_TYPE => $this->getBaseTextSettings(32),
                ]
            ]
        ];
    }

    /**
     * Get base text settings
     *
     * @param int $ignoreAbove
     * @return array{type: 'text', fields: array{keyword: array{type: 'keyword', ignore_above: int}}}
     */
    private function getBaseTextSettings(int $ignoreAbove): array
    {
        return [
            'type' => 'text',
            'fields' => [
                'keyword' => [
                    'type' => 'keyword',
                    'ignore_above' => $ignoreAbove
                ]
            ]
        ];
    }
}
