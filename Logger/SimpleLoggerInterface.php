<?php

declare(strict_types=1);

namespace Freento\SqlLog\Logger;

interface SimpleLoggerInterface
{
    /**
     * @param string $str
     * @return void
     */
    public function log(string $str): void;
}
