<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Logs\Elasticsearch\Trace;

use Freento\SqlLog\Model\Elasticsearch\Index;
use Freento\SqlLog\Model\Logs\Elasticsearch\AbstractCollection;
use Magento\Framework\Exception\LocalizedException;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    private string $queryId = '';

    /**
     * @var string[]
     */
    private array $additionalData = [];

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getAdditionalData(): array
    {
        if (!$this->isLoaded) {
            $this->load();
        }

        return $this->additionalData;
    }

    /**
     * @inheritDoc
     */
    public function setData(array $data): static
    {
        if (isset($data['query_id'])) {
            $this->queryId = $data['query_id'];
        }

        return parent::setData($data);
    }

    /**
     * @inheritDoc
     */
    protected function getRequestBody(): array
    {
        return  [
            'size' => self::MAX_RESULTS,
            'query' => [
                'match_phrase' => [
                    '_id' => $this->queryId
                ]
            ],
            '_source' => [
                Index::INDEX_FIELD_TRACE,
                Index::INDEX_FIELD_QUERY,
                Index::INDEX_FIELD_REQUEST_NAME,
                Index::INDEX_FIELD_REQUEST_DATE
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getItemsFromResponse(array $elasticResponse): array
    {
        $items = [];
        $data = $elasticResponse['hits']['hits'][0]['_source'] ?? [];

        $this->additionalData = [
                'query' => $data[Index::INDEX_FIELD_QUERY] ?? '',
                'action' => $data[Index::INDEX_FIELD_REQUEST_NAME] ?? '',
                'date' => $data[Index::INDEX_FIELD_REQUEST_DATE] ?? ''
            ];
        $trace = $data[Index::INDEX_FIELD_TRACE] ?? [];
        $id = count($trace);
        foreach ($trace as $traceItem) {
            $items[] = [
                'id' => $id--,
                'file' => $traceItem['file'] ?? '',
                'code' => ($traceItem['class'] ?? '') . ($traceItem['type'] ?? '') . ($traceItem['function'] ?? '') . '()',
                'line' => $traceItem['line'] ?? 0
            ];
        }

        return $items;
    }
}
