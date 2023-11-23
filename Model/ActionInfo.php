<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model;

use Freento\SqlLog\Exception\CouldNotResolveActionType;
use Freento\SqlLog\Model\ActionInfoType\ActionInfoCacheableInterface;
use Freento\SqlLog\Model\ActionInfoType\Resolver;
use Magento\Framework\Exception\LocalizedException;

class ActionInfo
{
    /**
     * @var ActionInfoCacheableInterface
     */
    private ActionInfoCacheableInterface $action;

    /**
     * @param Resolver $typeResolver
     */
    public function __construct(private readonly Resolver $typeResolver)
    {
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isLoggingActive(): bool
    {
        return $this->getAction()->isLoggingActive();
    }

    /**
     * @return bool
     * @throws CouldNotResolveActionType
     */
    public function isDataChanged(): bool
    {
        return $this->getAction()->isDataChanged();
    }

    /**
     * Get action string
     *
     * @return string
     * @throws CouldNotResolveActionType
     */
    public function getActionString(): string
    {
        return $this->getAction()->getActionString();
    }

    /**
     * @return ActionInfoCacheableInterface
     * @throws CouldNotResolveActionType
     */
    protected function getAction(): ActionInfoCacheableInterface
    {
        if (!isset($this->action)) {
            $this->action = $this->typeResolver->resolve();
        }

        return $this->action;
    }
}
