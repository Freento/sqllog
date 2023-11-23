<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\QueryStats;

use Exception;
use Zend_Db_Statement_Pdo;

interface QueryDataInterface
{
    /**
     * @return string
     */
    public function getFormatString(): string;

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): static;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $query
     * @return $this
     */
    public function setQuery(string $query): static;

    /**
     * @return string
     */
    public function getQuery(): string;

    /**
     * @param mixed[] $bind
     * @return $this
     */
    public function setBind(array $bind): static;

    /**
     * @return mixed[]
     */
    public function getBind(): array;

    /**
     * @param mixed[] $trace
     * @return $this
     */
    public function setTrace(array $trace): static;

    /**
     * @return mixed[]
     */
    public function getTrace(): array;

    /**
     * @param Zend_Db_Statement_Pdo|null $result
     * @return $this
     */
    public function setResult(?Zend_Db_Statement_Pdo $result): static;

    /**
     * @return Zend_Db_Statement_Pdo|null
     */
    public function getResult(): ?Zend_Db_Statement_Pdo;

    /**
     * @param string $startTime
     * @return $this
     */
    public function setStartTime(string $startTime): static;

    /**
     * @return string
     */
    public function getStartTime(): string;

    /**
     * @param string $executingTime
     * @return $this
     */
    public function setExecutingTime(string $executingTime): static;

    /**
     * @return string
     */
    public function getExecutingTime(): string;

    /**
     * @param Exception $exception
     * @return $this
     */
    public function setError(Exception $exception): static;

    /**
     * @return Exception|null
     */
    public function getError(): ?Exception;

    /**
     * @param string[] $excludedFields
     * @return $this
     */
    public function setExcludedFields(array $excludedFields): static;
}
