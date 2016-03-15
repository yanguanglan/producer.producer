<?php
namespace Producer\Vcs;

use Producer\Exception;
use Producer\Fsio;

class Hg implements VcsInterface
{
    protected $config = [];
    protected $origin;

    public function __construct(Fsio $fsio)
    {
        $this->fsio = $fsio;
        $this->config = $this->fsio->parseIni('.hg/hgrc', true);
    }

    public function getOrigin()
    {
        if (! isset($this->config['paths']['default'])) {
            throw new Exception('Could not determine default path.');
        }
        return $this->config['paths']['default'];
    }
}
