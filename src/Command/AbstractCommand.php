<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Command;

use Producer\Api\ApiInterface;
use Producer\Repo\RepoInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * Base command class.
 *
 * @package producer/producer
 *
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     *
     * The logger.
     *
     * @var LoggerInterface
     *
     */
    protected $logger;

    /**
     *
     * The local repository.
     *
     * @var RepoInterface
     *
     */
    protected $repo;

    /**
     *
     * The remote API.
     *
     * @var ApiInterface
     *
     */
    protected $api;

    /**
     *
     * Constructor.
     *
     * @param LoggerInterface $logger The logger.
     *
     * @param RepoInterface $repo The local repository.
     *
     * @param ApiInterface $api The remote API.
     *
     */
    public function __construct(
        LoggerInterface $logger,
        RepoInterface $repo,
        ApiInterface $api
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->api = $api;
    }

    /**
     *
     * The command logic.
     *
     * @param array $argv Command line arguments.
     *
     * @return mixed
     *
     */
    abstract public function __invoke(array $argv);
}
