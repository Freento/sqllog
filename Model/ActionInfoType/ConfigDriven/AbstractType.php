<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\ActionInfoType\ConfigDriven;

use Freento\SqlLog\Helper\Config;
use Freento\SqlLog\Model\ActionInfoType\ActionInfoCacheableInterface;

abstract class AbstractType implements ActionInfoCacheableInterface
{
    /**
     * @var int
     */
    protected int $dataVersion = 0;

    /**
     * @var bool
     */
    protected bool $isLoggingActive;

    /**
     * @param Config $config
     */
    public function __construct(protected readonly Config $config)
    {
    }

    /**
     * @inheritDoc
     */
    public function isLoggingActive(): bool
    {
        if ($this->isDataOutOfDate() || !isset($this->isConfigEnable)) {
            $this->isLoggingActive = $this->isConfigEnable() && $this->isAllowed();
            $this->dataVersion = $this->config->getDataVersion();
        }

        return $this->isLoggingActive;
    }

    /**
     * @inheritDoc
     */
    public function isDataChanged(): bool
    {
        return $this->isDataOutOfDate();
    }

    /**
     * Check if data is no longer relevant
     *
     * @return bool
     */
    private function isDataOutOfDate(): bool
    {
        return $this->config->isDataVersionOutOfDate($this->dataVersion);
    }

    /**
     * Get is action allowed
     *
     * @return bool
     */
    protected function isAllowed(): bool
    {
        $allowedPatterns = $this->getAllowedPatterns();
        if ($allowedPatterns && !$this->matchWithArray($this->getActionString(), $allowedPatterns)) {
            return false;
        }

        $disallowedPatterns = $this->getDisallowedPatterns();
        return !$disallowedPatterns || !$this->matchWithArray($this->getActionString(), $disallowedPatterns);
    }

    /**
     * Match string with every pattern from array till find match
     * return true if at least one match was found
     *
     * @param string $string
     * @param string[] $patternArray
     * @return bool
     */
    private function matchWithArray(string $string, array $patternArray): bool
    {
        $isMatched = false;
        $old_error = error_reporting(0);
        foreach ($patternArray as $regex) {
            $clearRegex = trim($regex);
            if (preg_match("/$clearRegex/", $string)) {
                $isMatched = true;
                break;
            }
        }
        error_reporting($old_error);

        return $isMatched;
    }

    /**
     * Get allowed pattern of action string
     *
     * @return string[]
     */
    abstract protected function getAllowedPatterns(): array;

    /**
     * Get disallowed patterns of action string
     *
     * @return string[]
     */
    abstract protected function getDisallowedPatterns(): array;

    /**
     * Get is config enable
     *
     * @return bool
     */
    abstract protected function isConfigEnable(): bool;
}
