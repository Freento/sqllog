<?php

declare(strict_types=1);

namespace Freento\SqlLog\Ui\DataProvider\Elasticsearch;

use Freento\SqlLog\Model\Logs\Elasticsearch\AbstractCollection as Collection;
use Freento\SqlLog\Model\Logs\Elasticsearch\DataProvider\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class SqlLogDataProvider implements \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface
{
    /**
     * @var Collection|null
     */
    private ?Collection $collection;

    /**
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param mixed[] $meta
     * @param mixed[] $data
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly RequestInterface $request,
        private readonly LoggerInterface $logger,
        private readonly string $name,
        private readonly string $primaryFieldName,
        private readonly string $requestFieldName,
        private readonly array $meta = [],
        private array $data = []
    ) {
    }

    /**
     * Get collection
     *
     * @return Collection
     * @throws LocalizedException
     */
    public function getCollection(): Collection
    {
        if (empty($this->collection)) {
            try {
                $this->collection = $this->collectionFactory->getByName($this->name);
            } catch (\Exception $e) {
                $this->logger->warning((string)$e);
                throw new LocalizedException(__('Something wrong with collection, please contact the administrator'));
            }

            $this->collection->setData($this->request->getParams());
        }

        return $this->collection;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getConfigData()
    {
        $config = $this->data['config'] ?? [];
        $config['additionalData'] = $this->getCollection()->getAdditionalData();

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function setConfigData($config): void
    {
        $this->data['config'] = $config;
    }

    /**
     * @inheritDoc
     * @return mixed[]
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @inheritDoc
     * @return mixed[]
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return $this->meta[$fieldSetName]['children'][$fieldName] ?? [];
    }

    /**
     * @inheritDoc
     * @return mixed[]
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return $this->meta[$fieldSetName] ?? [];
    }

    /**
     * @inheritDoc
     * @return mixed[]
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return $this->meta[$fieldSetName]['children'] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryFieldName(): string
    {
        return $this->primaryFieldName;
    }

    /**
     * @inheritDoc
     */
    public function getRequestFieldName(): string
    {
        return $this->requestFieldName;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        try {
            return [
                'items' => $this->getCollection()->getItems(),
                'totalRecords' => $this->getCollection()->getCount(),
                ...$this->request->getParams()
            ];
        } catch (LocalizedException $e) {
            $this->logger->warning((string)$e);
            return [
                'items' => [],
                'totalRecords' => 0,
                'errorMessage' => $e->getMessage()
            ];
        }
    }

    /**
     * @inheritDoc
     * @return void
     * @throws LocalizedException
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->getCollection()->addFilter($filter);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function addOrder($field, $direction)
    {
        $this->getCollection()->addOrder($field, $direction);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function setLimit($offset, $size)
    {
        $this->getCollection()->setLimit($offset, $size);
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function getSearchCriteria() /* @phpstan-ignore-line we don't use SearchCriteriaInterface */
    {
        // TODO: Implement getSearchCriteria() method.
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function getSearchResult() /* @phpstan-ignore-line we don't use SearchResultInterface */
    {
        // TODO: Implement getSearchResult() method.
    }
}
