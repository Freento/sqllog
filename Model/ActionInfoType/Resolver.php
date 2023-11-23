<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\ActionInfoType;

use Freento\SqlLog\Exception\CouldNotResolveActionType;

class Resolver
{
    /**
     * @var ActionInfoCacheableInterface
     */
    private ActionInfoCacheableInterface $action;

    /**
     * @param Resolver\ResolverInterface[] $resolvers
     */
    public function __construct(private readonly array $resolvers)
    {
    }

    /**
     * Resolve action
     *
     * @return ActionInfoCacheableInterface
     * @throws CouldNotResolveActionType
     */
    public function resolve(): ActionInfoCacheableInterface
    {
        if (!isset($this->action)) {
            foreach ($this->resolvers as $resolver) {
                if ($resolver->isCurrentType()) {
                    $this->action = $resolver->getAction();
                    break;
                }
            }

            if (!isset($this->action)) {
                throw new CouldNotResolveActionType(__('Couldn\'t resolve type of current action'));
            }
        }

        return $this->action;
    }
}
