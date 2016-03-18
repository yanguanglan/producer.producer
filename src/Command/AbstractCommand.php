<?php
namespace Producer\Command;

use Producer\Api\ApiInterface;
use Producer\Repo\RepoInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * @package producer/producer
 *
 */
abstract class AbstractCommand
{
    protected $logger;
    protected $repo;
    protected $api;

    public function __construct(
        LoggerInterface $logger,
        RepoInterface $repo,
        ApiInterface $api
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->api = $api;
    }
}
