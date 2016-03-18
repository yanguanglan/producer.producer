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
class Phpdoc
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

    public function __invoke(array $args)
    {
        $this->repo->checkDocblocks();
    }
}
