<?php
namespace Producer\Command;

use Producer\Api\ApiInterface;
use Producer\Exception;
use Producer\Repo\RepoInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * @package producer/producer
 *
 */
class Release
{
    protected $composer;
    protected $package;
    protected $version = false;
    protected $branch;
    protected $logger;
    protected $repo;
    protected $api;

    public function __construct(
        LoggerInterface $logger,
        RepoInterface $repo,
        ApiInterface $api
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->api = $api;
    }

    public function __invoke(array $argv)
    {
        $this->setComposerPackage();
        $this->setBranch(array_shift($argv));
        $this->setVersion(array_shift($argv));

        $this->repo->pull();
        $this->repo->checkSupportFiles();
        $this->repo->checkLicenseYear();
        $this->repo->runTests();
        $this->repo->checkDocblocks();
        $this->checkChangelog();
        $this->showIssues();

        $this->logger->info('Done!');
    }

    protected function setComposerPackage()
    {
        $this->composer = $this->repo->getComposer();
        $this->package = $this->composer->name;
        $this->logger->info("Composer package '{$this->package}'.");
    }

    protected function setBranch($branch)
    {
        if ($branch) {
            $this->repo->checkout($branch);
        }
        $this->branch = $this->repo->getBranch();
        $this->logger->info("Working branch is '{$this->branch}'.");
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

    protected function checkChangelog()
    {
        $this->logger->info('Checking if CHANGELOG up to date.');

        $lastChangelog = $this->repo->getChangelogDate();
        $this->logger->info("CHANGELOG date is $lastChangelog.");

        $lastCommit = $this->repo->getLastCommitDate();
        $this->logger->info("Last commit date is $lastCommit.");

        if ($lastChangelog == $lastCommit) {
            $this->logger->info('CHANGELOG appears up to date.');
            return;
        }

        $this->logger->error('CHANGELOG appears out of date.');
        $this->repo->logSinceDate($lastChangelog);
        throw new Exception('Please update and commit the CHANGELOG.');
    }

    protected function showIssues()
    {
        $issues = $this->api->fetchIssues();
        if (empty($issues)) {
            $this->logger->info('No open issues.');
        }

        $this->logger->warning('There are open issues:');
        foreach ($issues as $issue) {
            $this->logger->warning("    {$issue->number}. {$issue->title}");
            $this->logger->warning("        {$issue->url}");
        }
    }
}
