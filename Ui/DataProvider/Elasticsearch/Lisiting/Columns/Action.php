<?php

declare(strict_types=1);

namespace Freento\SqlLog\Ui\DataProvider\Elasticsearch\Lisiting\Columns;

use Freento\SqlLog\Model\Elasticsearch\Index;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Action extends \Freento\SqlLog\Ui\DataProvider\Elasticsearch\Columns\AbstractActions
{
    /**
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param mixed[] $links
     * @param mixed[] $components
     * @param mixed[] $data
     */
    public function __construct(
        private readonly UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly array $links = [],
        array $components = [],
        array $data = [],
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     * @param mixed[] $item
     */
    protected function addActions(array &$item): void
    {
        if (!isset($item['request_name'], $item['request_date'])) {
            return;
        }

        $name = $this->getData('name');
        foreach ($this->links as $id => $linkData) {
            if (!isset($linkData['label'], $linkData['route'])) {
                continue;
            }

            $item[$name][$id] = [
                'href' => $this->urlBuilder->getUrl(
                    $linkData['route'],
                    ['_query' => [
                        Index::INDEX_FIELD_REQUEST_NAME => $item['request_name'],
                        Index::INDEX_FIELD_REQUEST_DATE => $item['request_date']
                    ]]
                ),
                'label' => __($linkData['label']),
                'target' => '_blank'
            ];
        }
    }
}
