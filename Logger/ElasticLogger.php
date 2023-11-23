<?php

declare(strict_types=1);

namespace Freento\SqlLog\Logger;

use Freento\SqlLog\Model\Elasticsearch\Client\ClientProxyInterface;
use Freento\SqlLog\Model\Elasticsearch\ClientResolverInterface;
use Freento\SqlLog\Model\Elasticsearch\Index;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class ElasticLogger implements SimpleLoggerInterface
{
    public const SQL_LOG_ELASTIC_INDEX_NAME = Index::INDEX_NAME;

    /**
     * @var ClientProxyInterface[]
     */
    private array $client;

    /**
     * @var string
     */
    private string $requestName;

    /**
     * @var string
     */
    private string $requestDate;

    /**
     * @var string
     */
    private string $caller;

    /**
     * @var int
     */
    private int $counter = 0;

    /**
     * @param ClientResolverInterface $clientResolver
     * @param JsonSerializer $json
     */
    public function __construct(
        private readonly ClientResolverInterface $clientResolver,
        private readonly JsonSerializer $json
    ) {
    }

    /**
     * @inheritDoc
     */
    public function log(string $str): void
    {
        $this->counter++;
        $queryData = $this->json->unserialize($str);
        if (!is_array($queryData)) {
            $queryData = [];
        }

        $document = [
            [
                'index' => [
                    '_index' => self::SQL_LOG_ELASTIC_INDEX_NAME
                ]
            ], [
                ...$queryData,
                Index::INDEX_FIELD_REQUEST_NAME => $this->getRequestName(),
                Index::INDEX_FIELD_REQUEST_DATE => $this->getRequestDate(),
                Index::INDEX_FIELD_CALLER => $this->getCaller(),
                Index::INDEX_FIELD_BIND_QUERY => $this->getBindQuery($queryData['query'] ?? '')
            ]
        ];

        $bulkArray = [
            'index' => self::SQL_LOG_ELASTIC_INDEX_NAME,
            'body' => $document,
            'refresh' => true,
        ];

        $this->getClient()->bulk($bulkArray);
    }

    /**
     * @return ClientProxyInterface
     */
    private function getClient(): ClientProxyInterface
    {
        $pid = getmypid();
        if (!isset($this->client[$pid])) {
            $this->client[$pid] = $this->clientResolver->resolve();
        }

        return $this->client[$pid];
    }

    /**
     * @return string
     */
    private function getRequestDate(): string
    {
        if (empty($this->requestDate)) {
            $this->requestDate = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? null);
        }

        return $this->requestDate;
    }

    /**
     * @return string
     */
    private function getRequestName(): string
    {
        if (empty($this->requestName)) {
            $requestName = '';
            if (isset($_SERVER['REQUEST_URI'])) {
                $path = parse_url($_SERVER['REQUEST_URI'])['path'] ?? $_SERVER['REQUEST_URI'];
                $requestName = trim($path, '/');

                if (!$requestName) {
                    $requestName = '/';
                } elseif (($pos = strrpos($requestName, '/key')) !== false) {
                    $requestName = substr($requestName, 0, $pos);
                }
            } elseif (isset($_SERVER['SCRIPT_NAME'])) {
                $requestName = implode(' ', $_SERVER['argv']);
            }

            $this->requestName = $requestName;
        }

        return $this->requestName;
    }

    /**
     * @return string
     */
    private function getCaller(): string
    {
        if (empty($this->caller)) {
            $caller = '';
            if (isset($_SERVER['REQUEST_URI'])) {
                $caller = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['SCRIPT_NAME'])) {
                $caller = implode(' ', $_SERVER['argv']);
            }

            $this->caller = $caller;
        }

        return $this->caller;
    }

    /**
     * @todo Experimental functional, improve and move somewhere
     * @param string $query
     * @return string
     */
    private function getBindQuery(string $query): string
    {
        $preg = "/\S*\s?(?:<?>?=|<|>|\sin\s?\(|\slike|is)\s?((?<=\().+?(?=\))|'.*?'|\".*?\"|\d+)/i";
        return preg_replace_callback(
            $preg,
            static function ($str) {
                $expression = $str[0] ?? '';
                $expressionValue = $str[1] ?? '';
                $expressionValuePosition = strrpos($expression, $expressionValue);
                if (!$expressionValuePosition) {
                    return $expression;
                }

                return substr_replace($expression, '?', $expressionValuePosition, strlen($expressionValue));
            },
            $query
        ) ?: $query;
    }
}
