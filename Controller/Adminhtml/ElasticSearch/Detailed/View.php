<?php

declare(strict_types=1);

namespace Freento\SqlLog\Controller\Adminhtml\ElasticSearch\Detailed;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class View extends Action implements HttpGetActionInterface
{
    protected const PAGE_TITLE = 'Detailed queries view';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Freento_SqlLog::sql_log_requests');
        $resultPage->getConfig()->getTitle()->prepend((string)__($this::PAGE_TITLE));
        return $resultPage;
    }
}
