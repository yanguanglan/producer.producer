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
 * @package producer/producer
 *
 */
class Phpdoc extends AbstractCommand
{
    public function __invoke(array $args)
    {
        $this->repo->checkDocblocks();
    }
}
