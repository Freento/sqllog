<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch\Client;

use Elasticsearch\Client;

class ElasticsearchClientProxy extends AbstractClientProxy
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param mixed[] $config
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @inheritDoc
     * @return Client
     */
    protected function getClient(): Client
    {
        if (!isset($this->client)) {
            $this->client = \Elasticsearch\ClientBuilder::fromConfig($this->config, true);
        }

        return $this->client;
    }
}
