<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Logs\Elasticsearch;

use Freento\SqlLog\Exception\CouldNotCreateElasticClient;
use Freento\SqlLog\Exception\IncorrectLocalElasticConfigs;
use Freento\SqlLog\Exception\NotFoundLocalElasticConfigs;
use Freento\SqlLog\Model\Elasticsearch\ClientResolver;
use Magento\Framework\Api\Filter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

abstract class AbstractCollection
{
    private const RANGE_CONDITIONS = [
        'gteq' => 'gte',
        'lteq' => 'lte',
    ];

    /**
     * Count based on aggregation result, will be filtered using range in code
     */
    protected const AGGREGATION_COUNT_FIELD = '';

    protected const MAX_RESULTS = 10000;

    /**
     * @var mixed[]
     */
    protected array $items = [];

    /**
     * @var array<string, string>
     */
    protected array $order = [];

    /**
     * @var int
     */
    protected int $from = 0;

    /**
     * @var int
     */
    protected int $size = 20;

    /**
     * @var string[]
     */
    private array $textFilters = [];

    /**
     * @var mixed[]
     */
    private array $rangeFilters = [];

    /**
     * @var int|null
     */
    private ?int $aggregationMinCount;

    /**
     * @var int|null
     */
    private ?int $aggregationMaxCount;

    /**
     * @var bool
     */
    protected bool $isLoaded = false;

    /**
     * @var mixed[]
     */
    protected array $data = [];

    /**
     * @param ClientResolver $clientResolver
     * @param LoggerInterface $logger
     * @param Json $json
     */
    public function __construct(
        protected readonly ClientResolver $clientResolver,
        private readonly LoggerInterface $logger,
        private readonly Json $json
    ) {
    }

    /**
     * Get items
     *
     * @return mixed[]
     * @throws LocalizedException
     */
    public function getItems(): array
    {
        if (!$this->isLoaded) {
            $this->load();
        }

        return array_slice($this->items, $this->from, $this->size);
    }

    /**
     * Get items count
     *
     * @return int
     * @throws LocalizedException
     */
    public function getCount(): int
    {
        if (!$this->isLoaded) {
            $this->load();
        }

        return count($this->items);
    }

    /**
     * Get additional system data
     *
     * @return mixed[]
     */
    public function getAdditionalData(): array
    {
        return [];
    }

    /**
     * Add data order
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function addOrder(string $field, string $direction): static
    {
        $this->order = [$field => strtolower($direction)];
        $this->sort();
        return $this;
    }

    /**
     * Load data from elastic
     *
     * @return void
     * @throws LocalizedException
     */
    protected function load(): void
    {
        try {
            $client = $this->clientResolver->resolve();
            $body = $this->getRequestBody();
            $query = [
                'index' => ClientResolver::ELASTIC_INDEX,
                'body' => $body
            ];
            $elasticResponse = $client->search($query);
            $this->items = $this->getItemsFromResponse($elasticResponse);
            $this->isLoaded = true;
            $this->sort();
        } catch (IncorrectLocalElasticConfigs | NotFoundLocalElasticConfigs $e) {
            $this->logger->info((string)$e);
            throw new LocalizedException(__('Incorrect Elastic Configuration.'));
        } catch (CouldNotCreateElasticClient $e) {
            $this->logger->warning((string)$e);
            throw new LocalizedException(__('Something wrong with elastic client, please contact the administrator.'));
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            if (!$this->isIndexNotFoundException($message)) {
                $this->logger->warning((string)$e);
                throw new LocalizedException(__('Something went wrong, please contact the administrator.'));
            }
        }
    }

    /**
     * Get elastic request body
     *
     * @return mixed[]
     */
    abstract protected function getRequestBody(): array;

    /**
     * Get items from elastic response array
     *
     * @param mixed[] $elasticResponse
     * @return mixed[]
     */
    abstract protected function getItemsFromResponse(array $elasticResponse): array;

    /**
     * Sort data
     *
     * @return void
     */
    protected function sort(): void
    {
        if (count($this->items) === 0) {
            return;
        }

        $direction = 'asc';
        if ($this->order) {
            $field = array_key_first($this->order);
            $direction = $this->order[$field];
        } else {
            $row = current($this->items);
            $field = current(array_keys($row));
        }

        usort($this->items, static function ($item1, $item2) use ($field, $direction) {
            $value1 = $item1[$field] ?? '';
            $value2 = $item2[$field] ?? '';

            $result = $value1 <=> $value2;
            if ($direction === 'desc') {
                $result *= -1;
            }

            return $result;
        });
    }

    /**
     * Set position and limit to show
     *
     * @param int $offset
     * @param int $size
     * @return $this
     */
    public function setLimit(int $offset, int $size): static
    {
        $this->from = ($offset - 1) * $size;
        $this->size = $size;
        return $this;
    }

    /**
     * Set collection data
     *
     * @param mixed[] $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Add filter
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter): void
    {
        $field = $filter->getField();
        $conditionType = $filter->getConditionType();
        $value = $filter->getValue();

        if ($field === $this::AGGREGATION_COUNT_FIELD && isset(self::RANGE_CONDITIONS[$conditionType])) {
            $filter->getConditionType() === 'gteq'
                ? $this->aggregationMinCount = (int)$filter->getValue()
                : $this->aggregationMaxCount = (int)$filter->getValue();
        } elseif (isset(self::RANGE_CONDITIONS[$conditionType])) {
            $condition = self::RANGE_CONDITIONS[$conditionType];
            $this->rangeFilters[$field][$condition] = $value;
        } elseif ($filter->getConditionType() === 'like') {
            $this->textFilters[$field] = trim($filter->getValue(), '%');
        }
    }

    /**
     * Prepare and return query based on filters
     *
     * @return mixed[]
     */
    protected function getQuery(): array
    {
        $query['bool']['must'] = [];
        foreach ($this->textFilters as $field => $text) {
            $query['bool']['must'][] = [
                'wildcard' => [
                    $field . '.keyword' => [
                        "value" => "*$text*",
                        "case_insensitive" => true
                    ]
                ]
            ];
        }

        foreach ($this->rangeFilters as $field => $condition) {
            $query['bool']['must'][] = [
                'range' => [
                    $field => $condition
                ]
            ];
        }

        return $query;
    }

    /**
     * Index is created automatically, so we don't need to know that it doesn't exist
     *
     * @param string $message
     * @return bool
     */
    protected function isIndexNotFoundException(string $message): bool
    {
        $result = false;
        try {
            $messageData = $this->json->unserialize($message);
            if (isset($messageData['error']['type']) && $messageData['error']['type'] === 'index_not_found_exception') {
                $result = true;
            }
        } catch (\InvalidArgumentException $e) {
        }

        return $result;
    }

    /**
     * Check filters for aggregation count value
     *
     * @param int $count
     * @return bool
     */
    protected function checkAggregationCountFilter(int $count): bool
    {
        return (empty($this->aggregationMinCount) || $count >= $this->aggregationMinCount)
            && (empty($this->aggregationMaxCount) || $count <= $this->aggregationMaxCount);
    }
}
