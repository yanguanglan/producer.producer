<?php
namespace Producer\Vcs;

/**
 *
 * @package producer/producer
 *
 */
class Hg extends AbstractVcs
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
