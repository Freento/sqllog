<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch;

use Freento\SqlLog\Model\Elasticsearch\Client\ClientProxyInterface;

interface ClientResolverInterface
{
    /**
     * @return ClientProxyInterface
     */
    public function resolve(): ClientProxyInterface;
}
