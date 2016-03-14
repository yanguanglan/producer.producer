<?php
namespace Producer;

use Producer\Vcs\VcsInterface;

class ProducerContainer
{
    protected $config;
    protected $fsio;

    public function __construct(
        array $config,
        Stdlog $logger,
        Fsio $fsio
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->fsio = $fsio;
    }

    public function newCommand(array $argv)
    {
        array_shift($argv);
        $name = array_shift($argv);

        $class = "Producer\Command\\" . ucfirst($name);
        if (! class_exists($class)) {
            throw new Exception("Command '$name' not found.");
        }

        $vcs = $this->newVcs();
        $api = $this->newApi($vcs);
        return new $class(
            $this->logger,
            $this->fsio,
            $vcs,
            $api
        );
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
