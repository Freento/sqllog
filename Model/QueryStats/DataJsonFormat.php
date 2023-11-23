<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\QueryStats;

use Exception;
use Freento\SqlLog\Model\Elasticsearch\Index;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Zend_Db_Statement_Pdo;

class DataJsonFormat implements QueryDataInterface
{
    public const QUERY_KEY = Index::INDEX_FIELD_QUERY;
    public const TYPE_KEY = Index::INDEX_FIELD_TYPE;
    public const START_TIME_KEY = Index::INDEX_FIELD_START_TIME;
    public const EXECUTING_TIME_KEY = Index::INDEX_FIELD_EXECUTING_TIME;
    public const BIND_KEY = Index::INDEX_FIELD_BIND;
    public const TRACE_KEY = Index::INDEX_FIELD_TRACE;
    public const RESULT_KEY = 'result';
    public const ERROR_KEY = Index::INDEX_FIELD_ERROR;

    /**
     * @var string[]
     */
    private array $excludedFields = [];

    /**
     * @var mixed[]
     */
    private array $data = [];

    /**
     * @var mixed[]
     */
    private array $preparedData = [];

    /**
     * @param JsonSerializer $json
     */
    public function __construct(private readonly JsonSerializer $json)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatString(): string
    {
        $preparedData = $this->getPreparedData();
        $finalData = $this->excludeFields($preparedData);
        $serializedData = $this->json->serialize($finalData);
        if (!is_string($serializedData)) {
            $serializedData = '';
        }

        return $serializedData;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(string $type): static
    {
        $this->data[self::TYPE_KEY] = $type;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->data[self::TYPE_KEY] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery(string $query): static
    {
        $this->data[self::QUERY_KEY] = $query;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->data[self::QUERY_KEY] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function setBind(array $bind): static
    {
        $this->data[self::BIND_KEY] = $bind;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBind(): array
    {
        return $this->data[self::BIND_KEY] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function setResult(?Zend_Db_Statement_Pdo $result): static
    {
        $this->data[self::RESULT_KEY] = $result;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResult(): ?Zend_Db_Statement_Pdo
    {
        return $this->data[self::RESULT_KEY];
    }

    /**
     * {@inheritdoc}
     */
    public function setTrace(array $trace): static
    {
        $this->data[self::TRACE_KEY] = $trace;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrace(): array
    {
        return $this->data[self::TRACE_KEY] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStartTime(string $startTime): static
    {
        $this->data[self::START_TIME_KEY] = $startTime;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartTime(): string
    {
        return $this->data[self::START_TIME_KEY] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function setExecutingTime(string $executingTime): static
    {
        $this->data[self::EXECUTING_TIME_KEY] = $executingTime;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutingTime(): string
    {
        return $this->data[self::EXECUTING_TIME_KEY] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function setError(Exception $exception): static
    {
        $this->data[self::ERROR_KEY] = $exception;
        $this->preparedData = [];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): Exception
    {
        return $this->data[self::ERROR_KEY];
    }

    /**
     * {@inheritdoc}
     */
    public function setExcludedFields(array $excludedFields): static
    {
        $this->excludedFields = $excludedFields;
        return $this;
    }

    /**
     * @return mixed[]
     */
    protected function getPreparedData(): array
    {
        if (!$this->preparedData) {
            $data = $this->data;
            $this->prepareResult($data);
            $this->prepareTrace($data);
            $this->prepareError($data);
            $this->prepareBind($data);
            $this->preparedData = $data;
        }

        return $this->preparedData;
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    protected function excludeFields(array $data): array
    {
        foreach ($this->excludedFields as $excludedField) {
            unset($data[$excludedField]);
        }

        return $data;
    }

    /**
     * @param mixed[] $data
     * @return void
     */
    protected function prepareResult(array &$data): void
    {
        $result = $data[self::RESULT_KEY] ?? 0;
        if ($result instanceof Zend_Db_Statement_Pdo) {
            try {
                $data[self::RESULT_KEY] = $result->rowCount() ?: 0;
            } catch (Exception $e) {
                // optional data so just ignore
            } finally {
                $data[self::RESULT_KEY] = 0;
            }
        } else {
            $data[self::RESULT_KEY] = (int) $data[self::RESULT_KEY];
        }
    }

    /**
     * @param mixed[] $data
     * @return void
     */
    protected function prepareTrace(array &$data): void
    {
        if (!isset($data[self::TRACE_KEY])) {
            $data[self::TRACE_KEY] = [];
        }
    }

    /**
     * @param mixed[] $data
     * @return void
     */
    protected function prepareError(array &$data): void
    {
        $error = $data[self::ERROR_KEY] ?? [];
        if ($error instanceof Exception) {
            $error = [
                'message' => $error->getMessage(),
                'trace' => $this->getFormatTrace($error->getTrace())
            ];
        }

        $data[self::ERROR_KEY] = json_encode($error);
    }

    /**
     * @param mixed[] $trace
     * @return array<array{File: string, Line: string|int, Code: string}>
     */
    protected function getFormatTrace(array $trace): array
    {
        $formatTrace = [];
        foreach ($trace as $traceItem) {
            $className = $traceItem['class'] ?? '';
            $type = $traceItem['type'] ?? '';
            $function = $traceItem['function'] ?? '';
            $fileName = $traceItem['file'] ?? '';
            $line = $traceItem['line'] ?? '';

            $formatTrace[] = [
                'File' => $fileName,
                'Line' => $line,
                'Code' => $className . $type . $function . '()',
            ];
        }

        return $formatTrace;
    }

    /**
     * @param mixed[] $data
     * @return void
     */
    protected function preparebind(array &$data)
    {
        $data['bind'] = json_encode($data['bind'] ?? []);
    }
}
