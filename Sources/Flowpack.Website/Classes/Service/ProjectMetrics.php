<?php
namespace Flowpack\Website\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 */
class ProjectMetrics
{
    const ORGANIZATION = 'Flowpack';

    const DATA_URI_PATTERN = 'https://packagist.org/packages/@PACKAGE_KEY@.json';

    /**
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $logger;

    /**
     * @var Browser
     * @Flow\Inject
     */
    protected $client;

    /**
     * @var array
     */
    protected $cache = [];

    public function initializeObject()
    {
        $this->client->setRequestEngine(new CurlEngine());
    }

    protected function fetch(string $package): array
    {
        $package = \strtolower($package);
        $package = \preg_replace('/\./','/', $package, 1);
        $package = \str_replace('.', '-', $package);

        if (isset($this->cache[$package])) {
            return $this->cache[$package];
        }

        $data = $this->client->request(\str_replace('@PACKAGE_KEY@', $package, self::DATA_URI_PATTERN));
        $data = \json_decode($data, true);

        $packageData = $data['package'];
        $this->cache[$package] = [
            'stars' => $packageData['github_stars'],
            'watchers' => $packageData['github_watchers'],
            'forks' => $packageData['github_forks'],
            'issues' => $packageData['github_open_issues'],
            'downloads' => $packageData['downloads'],
            'repository' => $packageData['repository'],
        ];

        return $this->cache[$package];
    }

    public function repository(string $package): string
    {
        return $this->call($package, 'repository');
    }

    public function stars(string $package): int
    {
        return (int)$this->call($package, 'stars');
    }

    public function forks(string $package): int
    {
        return (int)$this->call($package, 'forks');
    }

    public function downloads(string $package): int
    {
        return (int)$this->call($package, 'downloads.total');
    }

    public function watchers(string $package): int
    {
        return (int)$this->call($package, 'watchers');
    }

    protected function call(string $package, string $metric)
    {
        try {
            $data = $this->fetch($package);
            return Arrays::getValueByPath($data, $metric);
        } catch (\Exception $exception) {
            $this->logger->logException($exception);
        }

        return 0;
    }
}
