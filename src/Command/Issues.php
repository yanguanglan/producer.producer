<?php
namespace Producer\Command;

use Producer\Api\ApiInterface;
use Psr\Log\LoggerInterface;
use Producer\Repo\RepoInterface;

/**
 *
 * @package producer/producer
 *
 */
class Issues
{
    public function __construct(
        LoggerInterface $logger,
        RepoInterface $repo,
        ApiInterface $api
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->api = $api;
    }

    public function __invoke(array $argv)
    {
        $issues = $this->api->issues();
        if (empty($issues)) {
            return;
        }

        $this->logger->info($this->api->getRepoName());
        $this->logger->info('');
        foreach ($issues as $issue) {
            $this->logger->info("    {$issue->number}. {$issue->title}");
            $this->logger->info("        {$issue->url}");
            $this->logger->info('');
        }
    }
}
