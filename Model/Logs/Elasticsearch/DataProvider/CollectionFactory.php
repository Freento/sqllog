<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Logs\Elasticsearch\DataProvider;

use Exception;
use Freento\SqlLog\Model\Logs\Elasticsearch\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $collections
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly array $collections = []
    ) {
    }

    /**
     * Get collection by name
     *
     * @param string $name
     * @return AbstractCollection
     * @throws Exception
     */
    public function getByName(string $name): AbstractCollection
    {
        if (!isset($this->collections[$name])) {
            throw new Exception(sprintf('Not registered handle %s', $name));
        }
        $collection = $this->objectManager->create($this->collections[$name]);
        if (!$collection instanceof AbstractCollection) {
            throw new Exception(sprintf('Incorrect collection type %s.', $name));
        }
        return $collection;
    }
}
