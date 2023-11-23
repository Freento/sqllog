<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch;

use Freento\SqlLog\Exception\CouldNotCreateElasticClient;
use Freento\SqlLog\Exception\IncorrectLocalElasticConfigs;
use Freento\SqlLog\Exception\NotFoundLocalElasticConfigs;
use Freento\SqlLog\Model\Elasticsearch\Client\ClientFactory;
use Freento\SqlLog\Model\Elasticsearch\Client\ClientProxyInterface;

class ClientResolver implements ClientResolverInterface
{
    public const ELASTIC_INDEX = Index::INDEX_NAME;

    /**
     * @param ClientFactory $clientFactory
     * @param Config $config
     */
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Config $config
    ) {
    }

    /**
     * Get proper elastic client
     *
     * @return ClientProxyInterface
     * @throws CouldNotCreateElasticClient
     * @throws IncorrectLocalElasticConfigs
     * @throws NotFoundLocalElasticConfigs
     */
    public function resolve(): ClientProxyInterface
    {
        return $this->getClient();
    }

    /**
     * Get elastic client
     *
     * @return ClientProxyInterface
     * @throws CouldNotCreateElasticClient
     * @throws NotFoundLocalElasticConfigs
     * @throws IncorrectLocalElasticConfigs
     */
    private function getClient(): ClientProxyInterface
    {
        $engine = $this->config->getEngine();
        $esConfig = $this->config->getESConfig();
        return $this->clientFactory->create($engine, $esConfig);
    }
}
