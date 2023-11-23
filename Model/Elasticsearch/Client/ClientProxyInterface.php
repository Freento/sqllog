<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch\Client;

/**
 * We need proxy interface, because used clients don't have common interface
 */
interface ClientProxyInterface
{
    /**
     * Proxy bulk method
     *
     * @param mixed[] $params
     * @return mixed[]
     */
    public function bulk(array $params): array;

    /**
     * Proxy search method
     *
     * @param mixed[] $params
     * @return mixed[]
     */
    public function search(array $params = []): array;

    /**
     * Proxy deleteByQuery method
     *
     * @param mixed[] $params
     * @return mixed[]
     */
    public function deleteByQuery(array $params = []): array;

    /**
     * Create index, if index exist - delete it and create a new one
     *
     * @param string $index
     * @param mixed[] $settings
     * @return void
     */
    public function recreateIndex(string $index, array $settings): void;
}
