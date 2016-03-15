<?php
namespace Producer\Command;

use Producer\Stdlog;
use Producer\Fsio;
use Producer\Vcs\VcsInterface;
use Producer\Api\ApiInterface;

class Issues
{
    public function __construct(
        Stdlog $logger,
        Fsio $fsio,
        VcsInterface $vcs,
        ApiInterface $api
    ) {
        $this->logger = $logger;
        $this->fsio = $fsio;
        $this->vcs = $vcs;
        $this->api = $api;
    }

    public function __invoke()
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