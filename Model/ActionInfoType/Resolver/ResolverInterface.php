<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\ActionInfoType\Resolver;

use Freento\SqlLog\Model\ActionInfoType\ActionInfoCacheableInterface;

interface ResolverInterface
{
    /**
     * Check whether the current global action is an action of this type
     *
     * @return bool
     */
    public function isCurrentType(): bool;

    /**
     * Get action
     *
     * @return ActionInfoCacheableInterface
     */
    public function getAction(): ActionInfoCacheableInterface;
}
