<?php

declare(strict_types=1);

namespace Freento\SqlLog\Model\Elasticsearch;

use Freento\SqlLog\Exception\IncorrectLocalElasticConfigs;
use Freento\SqlLog\Exception\NotFoundLocalElasticConfigs;
use Freento\SqlLog\Helper\Config as ConfigFile;

class Config
{
    /**
     * @var array<Mixed>
     */
    private array $elasticConfig;

    /**
     * @param ConfigFile $configFile
     */
    public function __construct(private readonly ConfigFile $configFile)
    {
    }

    /**
     * Check if the elastic configurations are valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $isValid = true;
        try {
            $this->getESConfig();
        } catch (\Exception) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Get elastic engine.
     *
     * @return string
     * @throws IncorrectLocalElasticConfigs
     * @throws NotFoundLocalElasticConfigs
     */
    public function getEngine(): string
    {
        return $this->getESConfig()['engine'] ?? '';
    }

    /**
     * Get elastic config.
     *
     * @return mixed[]
     * @throws IncorrectLocalElasticConfigs
     * @throws NotFoundLocalElasticConfigs
     */
    public function getESConfig(): array
    {
        if (!isset($this->elasticConfig)) {
            $options = $this->configFile->getElasticConfigs();
            if (empty($options)) {
                throw new NotFoundLocalElasticConfigs(__('Elastic local configs are empty.'));
            }

            if (!isset($options['hostname'])) {
                throw new IncorrectLocalElasticConfigs(__('Not all fields specified in elastic local configs.'));
            }

            $hostname = preg_replace('/http[s]?:\/\//i', '', $options['hostname']);
            $protocol = parse_url($options['hostname'], PHP_URL_SCHEME);
            if (!$protocol) {
                $protocol = 'http';
            }

            $authString = '';
            if (!empty($options['enableAuth']) && (int)$options['enableAuth'] === 1 && isset($options['username'], $options['password'])) {
                $authString = "{$options['username']}:{$options['password']}@";
            }

            $portString = '';
            if (!empty($options['port'])) {
                $portString = ':' . $options['port'];
            }

            $host = $protocol . '://' . $authString . $hostname . $portString;
            $options['hosts'] = [$host];

            $this->elasticConfig = $options;
        }

        return $this->elasticConfig;
    }
}
