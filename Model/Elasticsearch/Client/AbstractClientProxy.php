<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch\Client;

use Elasticsearch\Client as ElasticClient;
use OpenSearch\Client as OpenSearchClient;

abstract class AbstractClientProxy implements ClientProxyInterface
{
    /**
     * Get elasticsearch client
     *
     * @return ElasticClient|OpenSearchClient
     */
    abstract protected function getClient(): OpenSearchClient|ElasticClient;

    /**
     * @inheritDoc
     */
    public function bulk(array $params): array
    {
        return $this->getClient()->bulk($params);
    }

    /**
     * @inheritDoc
     */
    public function search(array $params = []): array
    {
        return $this->getClient()->search($params);
    }

    /**
     * @inheritDoc
     */
    public function deleteByQuery(array $params = []): array
    {
        return $this->getClient()->deleteByQuery($params);
    }

    /**
     * @inheritDoc
     */
    public function recreateIndex(string $index, array $settings): void
    {
        $indices = $this->getClient()->indices();
        if ($indices->exists(['index' => $index])) {
            $indices->delete(['index' => $index]);
        }

        $indices->create(['index' => $index, 'body' => $settings]);
    }
}
