<?php

namespace Freento\SqlLog\Model\ActionInfoType\ConfigDriven;

use Freento\SqlLog\Helper\Config;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\HTTP\PhpEnvironment\Request;

class Url extends AbstractType
{
    /**
     * @var string
     */
    private string $actionString;

    /**
     * @param Request $request
     * @param Config $config
     */
    public function __construct(private readonly Request $request, Config $config)
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getActionString(): string
    {
        if (!isset($this->actionString)) {
            $this->actionString = $this->request->getRequestUri();
        }

        return $this->actionString;
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    protected function getAllowedPatterns(): array
    {
        return $this->config->getAllowedUrls();
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    protected function getDisallowedPatterns(): array
    {
        return $this->config->getDisallowedUrls();
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    protected function isConfigEnable(): bool
    {
        return $this->config->isEnableInWeb();
    }
}
