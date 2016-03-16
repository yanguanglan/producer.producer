<?php
namespace Producer\Command;

use Producer\Api\ApiInterface;
use Producer\Exception;
use Producer\Vcs\VcsInterface;
use Psr\Log\LoggerInterface;

class Release
{
    protected $composer;
    protected $package;
    protected $version = false;
    protected $branch;
    protected $logger;
    protected $vcs;
    protected $api;

    public function __construct(
        LoggerInterface $logger,
        VcsInterface $vcs,
        ApiInterface $api
    ) {
        $this->logger = $logger;
        $this->vcs = $vcs;
        $this->api = $api;
    }

    public function __invoke(array $argv)
    {
        $this->setComposerPackage();
        $this->setVersion(array_shift($argv));
        $this->setBranch();

        $this->logger->info('Updating working branch.');
        $this->vcs->updateBranch();

        $this->vcs->checkSupportFiles();
        $this->vcs->checkLicenseYear();

        $this->logger->info('Done!');
    }

    protected function setComposerPackage()
    {
        $this->composer = $this->vcs->getComposer();
        $this->package = $this->composer->name;
        $this->logger->info("Composer package '{$this->package}'.");
    }

    protected function setVersion($version)
    {
        if (! $version) {
            $this->logger->info('Dry run; will not release.');
            return;
        }

        if ($this->isValidVersion($version)) {
            $this->version = $version;
            $this->logger->info("Preparing version '{$this->version}' for release.");
            return;
        }

        $message = "Version '{$this->version}' is invalid. "
                 . "Please use the format '1.2.3(-dev|-alpha4|-beta5|-RC6)'.";
        throw new Exception($message);
    }

    protected function isValidVersion($version)
    {
        $format = '^(\d+.\d+.\d+)(-(dev|alpha\d+|beta\d+|RC\d+))?$';
        preg_match("/$format/", $version, $matches);
        return (bool) $matches;
    }

    protected function setBranch()
    {
        $this->logger->info("Determining working branch.");
        $this->branch = $this->vcs->getBranch();
        $this->logger->info("Working branch is '{$this->branch}'.");
    }
}
