<?php

declare(strict_types=1);

namespace Freento\SqlLog\Helper\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @method getElasticConfigs()
 */
class File extends DataObject
{
    private const CONFIG_DIRECTORY_NAME = 'fps';
    private const CONFIG_FILE_NAME = 'sql-log.json';
    private const ELASTIC_CONFIGS_KEY = 'elastic_configs';
    private const DATA_RELOAD_IN_SECONDS = 60;

    /**
     * @var string
     */
    private string $configFilePath = '';

    /**
     * @var int
     */
    private int $lastDataLoadTime = 0;

    /**
     * @param DirectoryList $directoryList
     * @param DriverPool $driverPool
     * @param Json $json
     * @param mixed[] $defaultConfigs
     */
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly DriverPool $driverPool,
        private readonly Json $json,
        private readonly array $defaultConfigs = []
    ) {
        parent::__construct();
    }

    /**
     * Get config file path
     *
     * @return string
     * @throws FileSystemException
     */
    private function getConfigFilePath(): string
    {
        if (!$this->configFilePath) {
            $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
            $configPath = $varDir . DIRECTORY_SEPARATOR . self::CONFIG_DIRECTORY_NAME;
            $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
            if (!$fileDriver->isDirectory($configPath)) {
                $fileDriver->createDirectory($configPath);
            }

            $this->configFilePath = $configPath . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;
        }

        return $this->configFilePath;
    }

    /**
     * Check config file existence
     *
     * @return bool
     */
    public function isConfigFileExist(): bool
    {
        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        try {
            return $fileDriver->isExists($this->getConfigFilePath());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set elastic config data
     *
     * @param mixed[] $config
     * @return void
     * @throws FileSystemException
     */
    public function setElasticConfigs(array $config): void
    {
        $this->setData(self::ELASTIC_CONFIGS_KEY, $config);
    }

    /**
     * Get data by key
     *
     * @return mixed
     * @throws FileSystemException
     */
    public function getData($key = '', $index = null)
    {
        if ($this->isDataReloadNeeded()) {
            $this->loadDataFromFile();
        }

        return parent::getData($key, $index);
    }

    /**
     * Get last data load time (unix timestamp)
     *
     * @return int
     */
    public function getLastDataLoadTime(): int
    {
        return $this->lastDataLoadTime;
    }

    /**
     * Get if data lifetime has expired
     *
     * @return bool
     */
    public function isDataOutOfDate(): bool
    {
        return $this->isDataReloadNeeded();
    }

    /**
     * Set data for key
     *
     * @param string|mixed[] $key
     * @param mixed $value
     * @return File
     * @throws FileSystemException
     */
    public function setData($key, $value = null): File
    {
        if ($this->isDataReloadNeeded()) {
            $this->loadDataFromFile();
        }

        parent::setData($key, $value);
        $this->saveDataToFile();
        return $this;
    }

    /**
     * Check if data lifetime has expired
     *
     * @return bool
     */
    private function isDataReloadNeeded(): bool
    {
        return !$this->lastDataLoadTime || (time() - $this->lastDataLoadTime >= self::DATA_RELOAD_IN_SECONDS);
    }

    /**
     * Load data from config file
     *
     * @return void
     * @throws FileSystemException
     */
    private function loadDataFromFile(): void
    {
        $data = $this->defaultConfigs;
        if (!$this->isConfigFileExist()) {
            $this->createConfigFile();
        } else {
            $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
            $fileContent = $fileDriver->fileGetContents($this->getConfigFilePath());
            try {
                $data = $this->unserializeFileContent($fileContent);
            } catch (\InvalidArgumentException $e) {
                $this->createConfigFile();
            }
        }

        $this->lastDataLoadTime = time();
        $this->_data = $data;
    }

    /**
     * Unserialize string
     *
     * @param string $content
     * @return mixed[]
     */
    private function unserializeFileContent(string $content): array
    {
        if (!$content) {
            return [];
        }

        $data = $this->json->unserialize($content);
        if (!is_array($data)) {
            $data = [];
        }

        return $data;
    }

    /**
     * Save data to config file
     *
     * @return void
     * @throws FileSystemException
     */
    private function saveDataToFile(): void
    {
        $data = $this->getData();
        if (!$this->isConfigFileExist()) {
            $this->createConfigFile();
        }

        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        $stream = $fileDriver->fileOpen($this->getConfigFilePath(), 'w');
        $data = $this->json->serialize($data);
        if (!is_string($data)) {
            $data = '';
        }

        $fileDriver->fileWrite($stream, $data);
        $fileDriver->fileClose($stream);
    }

    /**
     * Create config file
     *
     * @return void
     * @throws FileSystemException
     */
    private function createConfigFile(): void
    {
        $fileDriver = $this->driverPool->getDriver(DriverPool::FILE);
        $stream = $fileDriver->fileOpen($this->getConfigFilePath(), 'a');
        $data = $this->json->serialize($this->defaultConfigs);
        if (!is_string($data)) {
            $data = '';
        }

        $fileDriver->fileWrite($stream, $data);
        $fileDriver->fileClose($stream);
    }
}
