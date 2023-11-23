<?php

declare(strict_types=1);

namespace Freento\SqlLog\Logger\DB;

use Freento\SqlLog\Logger\ExceptionLogger\Logger as ExceptionLogger;
use Freento\SqlLog\Logger\SimpleLoggerInterface;
use Freento\SqlLog\Model\ActionInfo;
use Freento\SqlLog\Model\QueryStats\QueryDataInterface;
use Freento\SqlLog\Model\QueryStats\QueryDataInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

class Logger implements \Magento\Framework\DB\LoggerInterface
{
    private const EXACT_TYPES = [
      'insert' => 'insert',
      'select' => 'select',
      'update' => 'update',
      'delete' => 'delete'
    ];

    /**
     * @var SimpleLoggerInterface[]
     */
    private array $loggerList;

    /**
     * @var null|bool
     */
    private ?bool $isActive;

    /**
     * @var float
     */
    private float $timer;

    /**
     * @var string
     */
    private string $startTime = '';

    /**
     * @param ActionInfo $actionInfo
     * @param QueryDataInterfaceFactory $queryDataFactory
     * @param ExceptionLogger $logger
     * @param array<string[]> $loggerExcludedFields
     * @param SimpleLoggerInterface[] $loggerList
     */
    public function __construct(
        private readonly ActionInfo $actionInfo,
        private readonly QueryDataInterfaceFactory $queryDataFactory,
        private readonly ExceptionLogger $logger,
        private readonly array $loggerExcludedFields = [],
        array $loggerList = []
    ) {
        $this->setLoggerList($loggerList);
    }

    /**
     * {@inheritdoc}
     */
    public function log($str)
    {
        if (!$this->isActive()) {
            return;
        }

        try {
            $this->getLogger()->log($str);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * @param QueryDataInterface $queryData
     * @return void
     */
    protected function logQueryData(QueryDataInterface $queryData): void
    {
        foreach ($this->getLoggerList() as $loggerKey => $logger) {
            $queryData->setExcludedFields($this->loggerExcludedFields[$loggerKey] ?? []);
            try {
                $logger->log($queryData->getFormatString());
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startTimer()
    {
        if (!$this->isActive()) {
            return;
        }

        $this->timer = microtime(true);
    }

    /**
     * @param string $microtime
     * @return string
     */
    private function getCurrentDateTimeString(string $microtime): string
    {
        $dateTime = \DateTime::createFromFormat('U.u', $microtime);
        if (!$dateTime) {
            return '';
        }

        $timeZone = new \DateTimeZone('GMT');
        $dateTime->setTimezone($timeZone);
        return $dateTime->format('Y-m-d H:i:s u');
    }

    /**
     * {@inheritdoc}
     * @param mixed[] $bind
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
        if (!$this->isActive()) {
            return;
        }

        $time = '-1';
        $startTime = '';
        if (isset($this->timer)) {
            $time = microtime(true) - $this->timer;
            $startTime = $this->getCurrentDateTimeString((string)$this->timer);
            $this->timer += $time;
            $time = sprintf('%.4f', $time);
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_pop($trace);
        array_shift($trace);

        if (strtolower($type) === self::TYPE_QUERY) {
            $type = $this->getExactType($sql);
        }

        $queryData = $this->queryDataFactory->create();
        $queryData->setType($type)
            ->setQuery($sql ?: $type)
            ->setBind($bind)
            ->setResult($result)
            ->setStartTime($startTime)
            ->setExecutingTime($time)
            ->setTrace($trace);
        $this->logQueryData($queryData);
    }

    /**
     * {@inheritdoc}
     */
    public function critical(\Exception $e)
    {
        if (!$this->isActive()) {
            return;
        }

        $time = sprintf('%.4f', microtime(true) - $this->timer);
        $queryData = $this->queryDataFactory->create();
        $queryData->setStartTime($this->startTime)->setExecutingTime($time)->setError($e);
        $this->logQueryData($queryData);
    }

    /**
     * @return bool
     */
    private function isActive(): bool
    {
        try {
            if ($this->actionInfo->isDataChanged() || !isset($this->isActive)) {
                $this->isActive = $this->actionInfo->isLoggingActive();
            }
        } catch (LocalizedException $e) {
            $this->logger->error(
                'Sql logger is inactive due to exception: ' . $e->getMessage(),
                ['trace' => $e->getTraceAsString(), 'action' => $this->getActionString()]
            );
            $this->isActive = false;
        }

        return $this->isActive;
    }

    /**
     * Get action string
     *
     * @return string
     */
    private function getActionString(): string
    {
        try {
            return $this->actionInfo->getActionString();
        } catch (\Exception) {
            return 'undefined action';
        }
    }

    /**
     * @param mixed[] $loggerList
     * @return void
     */
    private function setLoggerList(array $loggerList): void
    {
        foreach ($loggerList as $key => $logger) {
            if ($logger instanceof SimpleLoggerInterface) {
                continue;
            }

            unset($loggerList[$key]);
        }

        $this->loggerList = $loggerList;
    }

    /**
     * @return array|SimpleLoggerInterface[]
     */
    private function getLoggerList(): array
    {
        return $this->loggerList ?? [];
    }

    /**
     * @return SimpleLoggerInterface
     * @throws \Exception
     */
    private function getLogger(): SimpleLoggerInterface
    {
        if (!$this->getLoggerList()) {
            throw new \Exception('Logger list is empty');
        }

        return current($this->getLoggerList());
    }

    /**
     * @param string $sql
     * @return string
     */
    private function getExactType(string $sql): string
    {
        $type = '';
        $statement = strtok($sql, ' ');
        if ($statement) {
            $type = self::EXACT_TYPES[strtolower($statement)] ?? '';
        }

        return $type ?: self::TYPE_QUERY;
    }
}
