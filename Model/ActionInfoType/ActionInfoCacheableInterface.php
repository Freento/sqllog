<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\ActionInfoType;

interface ActionInfoCacheableInterface
{
    /**
     * Get action string
     *
     * @return string
     */
    public function getActionString(): string;

    /**
     * Check if logging is active
     *
     * @return bool
     */
    public function isLoggingActive(): bool;

    /**
     * Return true if something has changed
     *
     * @return bool
     */
    public function isDataChanged(): bool;
}
