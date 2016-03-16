<?php
namespace Producer;

use Producer\Vcs\VcsInterface;

class ProducerContainer
{
    protected $homedir;
    protected $workdir;
    protected $stdout;
    protected $stderr;

    public function __construct(
        $homedir,
        $workdir,
        $stdout = STDOUT,
        $stderr = STDERR
    ) {
        $this->homedir = $homedir;
        $this->workdir = $workdir;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function newCommand(array $argv)
    {
        array_shift($argv);
        $name = array_shift($argv);

        $class = "Producer\Command\\" . ucfirst($name);
        if (! class_exists($class)) {
            throw new Exception("Command '$name' not found.");
        }

        $homefs = $this->newFsio($this->homedir);
        $config = $this->newConfig($homefs);
        $logger = $this->newLogger();
        $workfs = $this->newFsio($this->workdir);
        $vcs = $this->newVcs($workfs);
        $api = $this->newApi($vcs->getOrigin(), $config);

        return new $class($config, $logger, $workfs, $vcs, $api);
    }

    protected function newFsio($dir)
    {
        return new Fsio($dir);
    }

    protected function newConfig(Fsio $fsio)
    {
        return new Config($fsio);
    }

    protected function newLogger()
    {
        return new Stdlog($this->stdout, $this->stderr);
    }

    public function newVcs($fsio)
    {
        if ($fsio->isDir('.git')) {
            return new Vcs\Git($fsio);
        };

        if ($fsio->isDir('.hg')) {
            return new Vcs\Hg($fsio);
        }

        throw new Exception("Could not find .git or .hg files.");
    }

    public function newApi($origin, $config)
    {
        switch (true) {
            case (strpos($origin, 'github.com') !== false):
                return $this->newApiGithub($origin, $config);
            case (strpos($origin, 'gitlab.com') !== false):
                return $this->newApiGitlab($origin, $config);
            case (strpos($origin, 'bitbucket.org') !== false):
                return $this->newApiBitbucket($origin, $config);
            default:
                throw new Exception("Producer will not work with {$origin}.");
        }
    }

    protected function newApiGithub($origin, Config $config)
    {
        return new Api\Github(
            $origin,
            $config->get('github_username'),
            $config->get('github_token')
        );
    }

    protected function newApiGitlab($origin, Config $config)
    {
        return new Api\Gitlab(
            $origin,
            $config->get('gitlab_token')
        );
    }

    protected function newApiBitbucket($origin, Config $config)
    {
        return new Api\Bitbucket(
            $origin,
            $config->get('bitbucket_username'),
            $config->get('bitbucket_password')
        );
    }
}
