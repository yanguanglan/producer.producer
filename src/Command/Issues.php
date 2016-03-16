<?php
namespace Producer\Command;

use Producer\Api\ApiInterface;
use Psr\Log\LoggerInterface;
use Producer\Vcs\VcsInterface;

class Issues
{
    public function __construct(
        LoggerInterface $logger,
        VcsInterface $vcs,
        ApiInterface $api
    ) {
        $this->logger = $logger;
        $this->vcs = $vcs;
        $this->api = $api;
    }

    public function __invoke(array $argv)
    {
        $issues = $this->api->fetchIssues();
        if (empty($issues)) {
            return;
        }

        $this->logger->info($this->api->getRepo());
        $this->logger->info('');
        foreach ($issues as $issue) {
            $this->logger->info("    {$issue->number}. {$issue->title}");
            $this->logger->info("        {$issue->url}");
            $this->logger->info('');
        }
    }
}
