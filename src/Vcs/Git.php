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
        $this->config = parse_ini_string($this->fsio->get('config'), true);
    }

    public function getOrigin()
    {
        if (! isset($this->config['remote origin']['url'])) {
            throw new Exception('Could not determine remote origin.');
        }
        return $this->config['remote origin']['url'];
    }
}
