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
    /**
     *
     * The Mercurial config file name.
     *
     * @var string
     *
     */
    protected $configFile = '.hg/hgrc';

    /**
     *
     * Returns the VCS repo origin (i.e., the remote API origin).
     *
     * @return string
     *
     */
    public function getOrigin()
    {
        if (! isset($this->config['paths']['default'])) {
            throw new Exception('Could not determine default path.');
        }
        return $this->config['paths']['default'];
    }
}
