<?php

declare(strict_types=1);

namespace Freento\SqlLog\Controller\Adminhtml\ElasticSearch\Action;

use Freento\SqlLog\Model\Logs\Elasticsearch\ResourceModel\Logs;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Psr\Log\LoggerInterface;

class Clear extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @param Logs $logsResourceModel
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        private readonly Logs $logsResourceModel,
        private readonly LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Clear logs from elasticsearch
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $this->logsResourceModel->clear();
            $this->messageManager->addSuccessMessage((string)__('Logs has been cleared.'));
        } catch (\Exception $e) {
            $this->logger->warning((string)$e);
            $this->messageManager->addErrorMessage(
                (string)__('Couldn\'t clear logs, please contact the administrator.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath($this->getUrl('*/index/elasticsearch'));
    }
}
