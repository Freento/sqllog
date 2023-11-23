<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch\Client;

use OpenSearch\Client;

class OpenSearchClientProxy extends AbstractClientProxy
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
            $this->client = \OpenSearch\ClientBuilder::fromConfig($this->config, true);
        }

        return $this->client;
    }
}
