<?php

declare(strict_types=1);

namespace Freento\SqlLog\Ui\DataProvider\Elasticsearch\Detailed\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Action extends \Freento\SqlLog\Ui\DataProvider\Elasticsearch\Columns\AbstractActions
{
    /**
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param mixed[] $components
     * @param mixed[] $data
     */
    public function __construct(
        private readonly UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
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
        $name = $this->getData('name');
        if (isset($item['id'])) {
            $item[$name] = [
                'query_trace_link' => [
                    'href' => $this->urlBuilder->getUrl(
                        'freento_sql_log/elasticsearch_trace/view',
                        ['_query' => ['query_id' => $item['id']]]
                    ),
                    'label' => __('Trace'),
                    'target' => '_blank'
                ]
            ];
        }
    }
}
