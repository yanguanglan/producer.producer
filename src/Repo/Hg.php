<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Repo;

/**
 *
 * A Mercurial repository.
 *
 * @package producer/producer
 *
 */
class Hg extends AbstractRepo
{
    protected $configFile = '.hg/hgrc';

    public function getOrigin()
    {
        if (! isset($this->config['paths']['default'])) {
            throw new Exception('Could not determine default path.');
        }
        return $this->config['paths']['default'];
    }
}
