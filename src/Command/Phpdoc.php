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
use Psr\Log\LoggerInterface;
use Producer\Repo\RepoInterface;

/**
 *
 * Validate the `src/` docblocks with Phpdoc.
 *
 * @package producer/producer
 *
 */
class Phpdoc extends AbstractCommand
{
    /**
     *
     * The command logic.
     *
     * @param array $argv Command line arguments.
     *
     * @return mixed
     *
     */
    public function __invoke(array $args)
    {
        $this->repo->checkDocblocks();
    }
}
