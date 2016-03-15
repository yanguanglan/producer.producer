<?php
namespace Producer\Vcs;

use Producer\Exception;
use Producer\Fsio;

class Git implements VcsInterface
{
    protected $config = [];
    protected $origin;

    public function __construct(Fsio $fsio)
    {
        $this->fsio = $fsio;
        $this->config = $this->fsio->parseIni('.git/config', true);
    }

    public function getOrigin()
    {
        if (! isset($this->config['remote origin']['url'])) {
            throw new Exception('Could not determine remote origin.');
        }
        return $this->config['remote origin']['url'];
    }
}
