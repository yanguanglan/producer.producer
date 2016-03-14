<?php
namespace Producer;

use Producer\Vcs\VcsInterface;

class ProducerContainer
{
    protected $config;
    protected $fsio;

    public function __construct(array $config, Fsio $fsio)
    {
        $this->config = $config;
        $this->fsio = $fsio;
    }

    public function newVcs()
    {
        if ($this->fsio->isDir(".git")) {
            $dir = $this->fsio->path(".git");
            return new Vcs\Git(new Fsio($dir));
        };

        throw new Exception("Producer only works with git for now.");
    }

    public function newApi(VcsInterface $vcs)
    {
        $origin = $vcs->getOrigin();
        if (strpos($origin, 'github.com') !== false) {
            return new Api\Github(
                $this->config['PRODUCER_GITHUB_USER'],
                $this->config['PRODUCER_GITHUB_TOKEN'],
                $origin
            );
        }

        throw new Exception("Producer only works with github for now.");
    }
}
