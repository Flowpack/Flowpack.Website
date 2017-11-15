<?php
namespace Flowpack\Website\Node;

use Flowpack\Website\Service\ProjectMetrics;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\Flow\Annotations as Flow;

class Project extends Node
{
    /**
     * @var ProjectMetrics
     * @Flow\Inject
     */
    protected $metrics;

    public function getStars(): int
    {
        return $this->metrics->stars($this->getPackageKey());
    }

    public function getForks(): int
    {
        return $this->metrics->forks($this->getPackageKey());
    }

    public function getGithubUri(): string
    {
        return $this->metrics->repository($this->getPackageKey());
    }

    public function getDownloads(): string
    {
        return $this->metrics->downloads($this->getPackageKey());
    }

    public function getPackageKey(): string
    {
        return 'Flowpack.' . $this->getProperty('title');
    }

    protected function repositoryName(): string
    {
        return trim($this->getProperty('title'));
    }

}
