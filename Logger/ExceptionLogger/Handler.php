<?php

declare(strict_types=1);

namespace Freento\SqlLog\Logger\ExceptionLogger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = \Monolog\Logger::ERROR;
}
