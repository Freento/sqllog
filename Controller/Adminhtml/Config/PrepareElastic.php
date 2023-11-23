<?php

declare(strict_types=1);

namespace Freento\SqlLog\Controller\Adminhtml\Config;

use Freento\SqlLog\Helper\Config;
use Freento\SqlLog\Logger\ExceptionLogger\Logger;
use Freento\SqlLog\Model\Elasticsearch\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;

class PrepareElastic extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Freento_SqlLog::sqllog_config';

    /**
     * @param Config $config
     * @param Index $index
     * @param Logger $logger
     * @param Context $context
     */
    public function __construct(
        private readonly Config $config,
        private readonly Index $index,
        private readonly Logger $logger,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): ResultInterface
    {
        try {
            $this->config->updateElasticConfigs();
            $this->messageManager->addSuccessMessage((string)__('Elastic configs successfully updated'));
            $this->index->reCreate();
        } catch (\Exception $e) {
            $this->logger->error(
                'Exception during prepare elastic config action : ' . $e->getMessage(),
                ['trace' => $e->getTrace()]
            );
            $this->messageManager->addErrorMessage((string)__('Something went wrong'));
        }

        return $this->resultRedirectFactory->create()->setPath(
            'adminhtml/system_config/edit',
            [
                '_current' => ['section', 'website', 'store'],
                '_nosid' => true
            ]
        );
    }
}
