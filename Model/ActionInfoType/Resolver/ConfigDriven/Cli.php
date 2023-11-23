<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\ActionInfoType\Resolver\ConfigDriven;

use Freento\SqlLog\Model\ActionInfoType\ActionInfoCacheableInterface;
use Freento\SqlLog\Model\ActionInfoType\ConfigDriven\CliFactory;
use Freento\SqlLog\Model\ActionInfoType\Resolver\ResolverInterface;

class Cli implements ResolverInterface
{
    public const TYPE = 'cli';

    /**
     * @param CliFactory $cliFactory
     */
    public function __construct(private readonly CliFactory $cliFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function isCurrentType(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * @inheritDoc
     */
    public function getAction(): ActionInfoCacheableInterface
    {
        return $this->cliFactory->create();
    }
}
