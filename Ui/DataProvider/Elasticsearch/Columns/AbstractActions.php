<?php

declare(strict_types=1);

namespace Freento\SqlLog\Ui\DataProvider\Elasticsearch\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

abstract class AbstractActions extends Column
{
    /**
     * @inheritDoc
     * @param mixed[] $dataSource
     * @return mixed[]
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $this->addActions($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param mixed[] $item
     * @return void
     */
    abstract protected function addActions(array &$item): void;
}
