<?php

declare(strict_types=1);

namespace Freento\SqlLog\Block\Adminhtml;

use Freento\SqlLog\Model\Elasticsearch\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;

class ElasticConfigsNotice extends Template
{
    /**
     * @var string
     */
    protected $_template = 'elastic_configs_notice.phtml';

    /**
     * @param Context $context
     * @param Config $config
     * @param array<Mixed> $data
     */
    public function __construct(
        private readonly Config $config,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Show message if configs incorrect.
     *
     * @return bool
     */
    public function isShow(): bool
    {
        return !$this->config->isValid();
    }

    /**
     * Get config page url.
     *
     * @return string
     */
    public function getConfigPageUrl(): string
    {
        return $this->getUrl('adminhtml/system_config/edit/section/freento_sqllog/');
    }
}
