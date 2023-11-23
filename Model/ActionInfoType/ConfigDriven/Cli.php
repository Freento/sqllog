<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\ActionInfoType\ConfigDriven;

use Magento\Framework\Exception\FileSystemException;

class Cli extends AbstractType
{
    /**
     * @var string
     */
    private string $actionString;

    /**
     * @inheritDoc
     */
    public function getActionString(): string
    {
        if (!isset($this->actionString)) {
            $this->actionString = implode(' ', $_SERVER['argv'] ?? []);
        }

        return $this->actionString;
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    public function getAllowedPatterns(): array
    {
        return $this->config->getAllowedCommands();
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    protected function getDisallowedPatterns(): array
    {
        return $this->config->getDisallowedCommands();
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    protected function isConfigEnable(): bool
    {
        return $this->config->isEnableInCli();
    }
}
