<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\ActionInfoType\Resolver\ConfigDriven;

use Freento\SqlLog\Model\ActionInfoType\ActionInfoCacheableInterface;
use Freento\SqlLog\Model\ActionInfoType\ConfigDriven\UrlFactory;
use Freento\SqlLog\Model\ActionInfoType\Resolver\ResolverInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;

class Url implements ResolverInterface
{
    /**
     * @param Request $request
     * @param UrlFactory $urlFactory
     */
    public function __construct(private readonly Request $request, private readonly UrlFactory $urlFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function isCurrentType(): bool
    {
        return $this->request->getUri()->getHost() !== null;
    }

    /**
     * @inheritDoc
     */
    public function getAction(): ActionInfoCacheableInterface
    {
        return $this->urlFactory->create();
    }
}
