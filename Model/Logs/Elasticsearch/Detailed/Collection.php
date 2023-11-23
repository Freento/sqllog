<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Logs\Elasticsearch\Detailed;

use Freento\SqlLog\Model\Elasticsearch\Index;
use Freento\SqlLog\Model\Logs\Elasticsearch\AbstractCollection;

class Collection extends AbstractCollection
{
    protected const QUERY_FIELD_NAME = Index::INDEX_FIELD_QUERY;

    /**
     * @var string|null
     */
    private ?string $requestName = null;

    /**
     * @var string|null
     */
    private ?string $requestDate = null;

    /**
     * @var mixed[]
     */
    private array $additionalData = [];

    /**
     * @inheritDoc
     */
    public function setData(array $data): static
    {
        if (isset($data[Index::INDEX_FIELD_REQUEST_NAME])) {
            $this->requestName = $data[Index::INDEX_FIELD_REQUEST_NAME];
        }

        if (isset($data[Index::INDEX_FIELD_REQUEST_DATE])) {
            $this->requestDate = $data[Index::INDEX_FIELD_REQUEST_DATE];
        }

        return parent::setData($data);
    }

    /**
     * @inheritDoc
     * @return array{size: int, query: mixed[], _source: string[]}
     */
    protected function getRequestBody(): array
    {
        return [
            'size' => self::MAX_RESULTS,
            'query' => $this->getQuery(),
            '_source' => [
                Index::INDEX_FIELD_TYPE,
                $this->getField(),
                Index::INDEX_FIELD_START_TIME,
                Index::INDEX_FIELD_EXECUTING_TIME,
                Index::INDEX_FIELD_REQUEST_NAME,
                Index::INDEX_FIELD_REQUEST_DATE
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): array
    {
        $query = parent::getQuery();
        $query['bool']['must'][] = [
            'match_phrase' => [
                Index::INDEX_FIELD_REQUEST_NAME . '.keyword' => $this->requestName
            ]
        ];
        $query['bool']['must'][] = [
            'match_phrase' => [
                Index::INDEX_FIELD_REQUEST_DATE . '.keyword' => $this->requestDate
            ]
        ];

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /**
     * @inheritDoc
     */
    protected function getItemsFromResponse(array $elasticResponse): array
    {
        $items = [];
        if (isset($elasticResponse['hits']['hits'])) {
            $data = $elasticResponse['hits']['hits'];
            $row = current($data)['_source'] ?? [];
            $this->additionalData = [
              'Action' => $row[Index::INDEX_FIELD_REQUEST_NAME] ?? '',
              'Date' => $row[Index::INDEX_FIELD_REQUEST_DATE] ?? ''
            ];

            foreach ($elasticResponse['hits']['hits'] as $itemData) {
                if (!isset($itemData['_id'])) {
                    continue;
                }

                $sourceData = $itemData['_source'] ?? [];
                $items[] = [
                    'id' => $itemData['_id'],
                    'type' => $sourceData[Index::INDEX_FIELD_TYPE] ?? '',
                    'query' => $sourceData[$this->getField()] ?? '',
                    'executing_time' => $sourceData[Index::INDEX_FIELD_EXECUTING_TIME] ?? '',
                    'start_time' => $sourceData[Index::INDEX_FIELD_START_TIME] ?? '',
                ];
            }
        }

        return $items;
    }

    /**
     * Get field to select
     *
     * @return string
     */
    private function getField(): string
    {
        return $this::QUERY_FIELD_NAME;
    }
}
