<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Logs\Elasticsearch;

use Freento\SqlLog\Model\Elasticsearch\Index;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected const AGGREGATION_COUNT_FIELD = 'count';

    /**
     * @inheritDoc
     */
    protected function getRequestBody(): array
    {
        return [
            'size' => 0,
            'aggs' => [
                'request_data' => [
                    'multi_terms' => [
                        'terms' => [
                            ['field' => Index::INDEX_FIELD_REQUEST_NAME . '.keyword'],
                            ['field' => Index::INDEX_FIELD_REQUEST_DATE . '.keyword'],
                        ],
                        'size' => self::MAX_RESULTS
                    ]
                ]
            ],
            'query' => $this->getQuery()
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getItemsFromResponse(array $elasticResponse): array
    {
        $items = [];
        if (isset($elasticResponse['aggregations']['request_data']['buckets'])) {
            foreach ($elasticResponse['aggregations']['request_data']['buckets'] as $itemData) {
                $count = $itemData['doc_count'] ?? 0;
                if (isset($itemData['key'][0]) && $this->checkAggregationCountFilter($count)) {
                    $requestName = $itemData['key'][0];
                    $requestDate = $itemData['key'][1] ?? '';

                    $items[] = [
                        'id' => hash('xxh3', $requestName . $requestDate),
                        Index::INDEX_FIELD_REQUEST_NAME => $requestName,
                        Index::INDEX_FIELD_REQUEST_DATE => $requestDate,
                        'count' => $count
                    ];
                }
            }
        }

        return $items;
    }
}
