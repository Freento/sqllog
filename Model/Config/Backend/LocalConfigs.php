<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Config\Backend;

use Freento\SqlLog\Helper\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\FileSystemException;

class LocalConfigs extends Value
{
    /**
     * @param Config $localConfig
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param mixed[] $data
     */
    public function __construct(
        private readonly Config $localConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return LocalConfigs
     * @throws FileSystemException
     */
    public function beforeSave(): LocalConfigs
    {
        $value = $this->getValue();
        $this->localConfig->setForConfigPath($this->getPath(), $value);
        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    public function isValueChanged(): bool
    {
        return $this->getValue() !== $this->localConfig->getForConfigPath($this->getPath());
    }

    /**
     * @return LocalConfigs
     */
    public function afterLoad(): LocalConfigs
    {
        $this->setValue($this->localConfig->getForConfigPath($this->getPath()));
        return parent::afterLoad();
    }
}
