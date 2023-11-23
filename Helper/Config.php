<?php

declare(strict_types=1);

namespace Freento\SqlLog\Helper;

use Freento\SqlLog\Helper\Config\File;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;

class Config
{
    public const STORES_CONFIG_XML_PATH_GENERAL_PART = 'freento_sqllog/general/';
    public const ENABLE_ON_WEB_REQUESTS_PATH = 'enable_on_web_requests';
    public const ALLOWED_URLS_PATH = 'allowed_urls';
    public const DISALLOWED_URLS_PATH = 'disallowed_urls';
    public const ENABLE_IN_CLI_PATH = 'enable_in_cli';
    public const ALLOWED_COMMANDS_PATH = 'allowed_commands';
    public const DISALLOWED_COMMANDS_PATH = 'disallowed_commands';
    public const PATH_FIELD_MAP = [
        self::STORES_CONFIG_XML_PATH_GENERAL_PART . self::ENABLE_ON_WEB_REQUESTS_PATH => self::ENABLE_ON_WEB_REQUESTS_PATH,
        self::STORES_CONFIG_XML_PATH_GENERAL_PART . self::ALLOWED_URLS_PATH => self::ALLOWED_URLS_PATH,
        self::STORES_CONFIG_XML_PATH_GENERAL_PART . self::DISALLOWED_URLS_PATH => self::DISALLOWED_URLS_PATH,
        self::STORES_CONFIG_XML_PATH_GENERAL_PART . self::ENABLE_IN_CLI_PATH => self::ENABLE_IN_CLI_PATH,
        self::STORES_CONFIG_XML_PATH_GENERAL_PART . self::ALLOWED_COMMANDS_PATH => self::ALLOWED_COMMANDS_PATH,
        self::STORES_CONFIG_XML_PATH_GENERAL_PART . self::DISALLOWED_COMMANDS_PATH => self::DISALLOWED_COMMANDS_PATH,
    ];

    /**
     * @param File $configFile
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly File $configFile,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Add elastic client configs
     *
     * @return void
     * @throws FileSystemException
     */
    public function updateElasticConfigs(): void
    {
        $engine = $this->scopeConfig->getValue('catalog/search/engine');
        $this->configFile->setElasticConfigs([
            'hostname' => $this->scopeConfig->getValue(sprintf('catalog/search/%s_server_hostname', $engine)),
            'port' => $this->scopeConfig->getValue(sprintf('catalog/search/%s_server_port', $engine)),
            'enableAuth' => $this->scopeConfig->getValue(sprintf('catalog/search/%s_enable_auth', $engine)),
            'username' => $this->scopeConfig->getValue(sprintf('catalog/search/%s_username', $engine)),
            'password' => $this->scopeConfig->getValue(sprintf('catalog/search/%s_password', $engine)),
            'timeout' => $this->scopeConfig->getValue(sprintf('catalog/search/%s_timeout', $engine))
                ?: \Magento\Elasticsearch\Model\Config::ELASTICSEARCH_DEFAULT_TIMEOUT,
            'engine' => $engine
        ]);
    }

    /**
     * Get elastic configs
     *
     * @return mixed[]
     */
    public function getElasticConfigs(): array
    {
        return $this->configFile->getElasticConfigs() ?: [];
    }

    /**
     * @return bool
     * @throws FileSystemException
     */
    public function isEnableInWeb(): bool
    {
        return (bool)$this->getForConfigPath(self::ENABLE_ON_WEB_REQUESTS_PATH);
    }

    /**
     * @return bool
     * @throws FileSystemException
     */
    public function isEnableInCli(): bool
    {
        return (bool)$this->getForConfigPath(self::ENABLE_IN_CLI_PATH);
    }

    /**
     * @return string[]
     * @throws FileSystemException
     */
    public function getAllowedUrls(): array
    {
        return $this->stringLinesIntoArray($this->getForConfigPath(self::ALLOWED_URLS_PATH) ?: '');
    }

    /**
     * @return string[]
     * @throws FileSystemException
     */
    public function getAllowedCommands(): array
    {
        return $this->stringLinesIntoArray($this->getForConfigPath(self::ALLOWED_COMMANDS_PATH) ?: '');
    }

    /**
     * @return string[]
     * @throws FileSystemException
     */
    public function getDisallowedUrls(): array
    {
        return $this->stringLinesIntoArray($this->getForConfigPath(self::DISALLOWED_URLS_PATH) ?: '');
    }

    /**
     * @return string[]
     * @throws FileSystemException
     */
    public function getDisallowedCommands(): array
    {
        return $this->stringLinesIntoArray($this->getForConfigPath(self::DISALLOWED_COMMANDS_PATH) ?: '');
    }

    /**
     * @param string $str
     * @return string[]
     */
    private function stringLinesIntoArray(string $str): array
    {
        return array_filter(preg_split('/((\r?\n)|(\r\n?))/', $str) ?: []);
    }

    /**
     * @param string $path
     * @return mixed
     * @throws FileSystemException
     */
    public function getForConfigPath(string $path)
    {
        $key = $this->getFieldByPath($path);
        return $this->configFile->getData($key);
    }

    /**
     * @param string $path
     * @param string $value
     * @return void
     * @throws FileSystemException
     */
    public function setForConfigPath(string $path, string $value): void
    {
        $key = $this->getFieldByPath($path);
        $this->configFile->setData($key, $value);
    }

    /**
     * Get config data version
     *
     * @return int
     */
    public function getDataVersion(): int
    {
        return $this->configFile->getLastDataLoadTime();
    }

    /**
     * Check if data version is out of date
     *
     * @param int $version
     * @return bool
     */
    public function isDataVersionOutOfDate(int $version): bool
    {
        return $this->configFile->isDataOutOfDate() || $version !== $this->getDataVersion();
    }

    /**
     * @param string $path
     * @return string
     */
    private function getFieldByPath(string $path): string
    {
        return self::PATH_FIELD_MAP[$path] ?? str_replace('/', '_', $path);
    }
}
