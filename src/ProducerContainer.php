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
        if ($this->fsio->isDir('.git')) {
            return new Vcs\Git($this->fsio);
        };

        if ($this->fsio->isDir('.hg')) {
            return new Vcs\Hg($this->fsio);
        }

        throw new Exception("Could not find .git or .hg files.");
    }

    public function newApi(VcsInterface $vcs)
    {
        $origin = $vcs->getOrigin();

        if (strpos($origin, 'github.com') !== false) {
            return new Api\Github(
                $origin,
                $this->config['PRODUCER_GITHUB_USER'],
                $this->config['PRODUCER_GITHUB_TOKEN']
            );
        }

        if (strpos($origin, 'gitlab.com') !== false) {
            return new Api\Gitlab(
                $origin,
                $this->config['PRODUCER_GITLAB_TOKEN']
            );
        }

        if (strpos($origin, 'bitbucket.org') !== false) {
            return new Api\Bitbucket(
                $origin,
                $this->config['PRODUCER_BITBUCKET_USERNAME'],
                $this->config['PRODUCER_BITBUCKET_PASSWORD']
            );
        }

        throw new Exception("Producer will not work with {$origin}.");
    }
}
