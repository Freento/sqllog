<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch\Client;

use Freento\SqlLog\Exception\CouldNotCreateElasticClient;

class ClientFactory
{
    /**
     * Create elastic client by code with configs
     *
     * @param string $code
     * @param mixed[] $config
     * @return ClientProxyInterface
     * @throws CouldNotCreateElasticClient
     */
    public function create(string $code, array $config): ClientProxyInterface
    {
        $client = '';
        if (str_contains(strtolower($code), 'elasticsearch')) {
            $client = new ElasticsearchClientProxy($config);
        } elseif (str_contains(strtolower($code), 'opensearch') && class_exists('\OpenSearch\ClientBuilder')) {
            $client = new OpenSearchClientProxy($config);
        }

        if (!$client) {
            throw new CouldNotCreateElasticClient(__('Couldn\'t find elastic client class for code : %1', $code));
        }

        return $client;
    }
}
