<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Logs\Elasticsearch\ResourceModel;

use Freento\SqlLog\Exception\CouldNotCreateElasticClient;
use Freento\SqlLog\Exception\IncorrectLocalElasticConfigs;
use Freento\SqlLog\Exception\NotFoundLocalElasticConfigs;
use Freento\SqlLog\Model\Elasticsearch\ClientResolver;

class Logs
{
    /**
     * @param ClientResolver $clientResolver
     */
    public function __construct(private readonly ClientResolver $clientResolver)
    {
    }

    /**
     * Clear data from elastic log index
     *
     * @throws CouldNotCreateElasticClient
     * @throws IncorrectLocalElasticConfigs
     * @throws NotFoundLocalElasticConfigs
     */
    public function clear(): void
    {
        $client = $this->clientResolver->resolve();
        $client->deleteByQuery([
            'index' => ClientResolver::ELASTIC_INDEX,
            'body' => [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ]
        ]);
    }
}
