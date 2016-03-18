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
class Validate
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
        $this->setVersion(array_shift($argv));
        $this->setComposerAndPackage();
        $this->logger->info("Validating {$this->package} {$this->version}");
        $this->repo->pull();
        $this->repo->checkStatus();
        $this->repo->checkSupportFiles();
        $this->repo->checkLicenseYear();
        $this->repo->runTests();
        $this->checkDocblocks();
        $this->checkChangelog();
        $this->showIssues();
        $this->logger->info("{$this->package} {$this->version} appears valid for release!");
    }

    protected function setComposerAndPackage()
    {
        $this->repo->validateComposer();
        $this->composer = $this->repo->getComposer();
        $this->package = $this->composer->name;
    }

    protected function setVersion($version)
    {
        if (! $version) {
            throw new Exception('Please specify a version number.');
        }

        if ($this->isValidVersion($version)) {
            $this->version = $version;
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

    protected function checkDocblocks()
    {
        switch (true) {
            case substr($this->version, 0, 2) == '0.':
                $skip = '0.x';
                break;
            case strpos($this->version, 'dev') !== false:
                $skip = 'dev';
                break;
            case strpos($this->version, 'alpha') !== false:
                $skip = 'alpha';
                break;
            default:
                $skip = false;
        }

        if ($skip) {
            $this->logger->info("Skipping docblock check for $skip release.");
            return;
        }

        $this->repo->checkDocblocks();
    }

    protected function showIssues()
    {
        $issues = $this->api->fetchIssues();
        if (empty($issues)) {
            $this->logger->info('No open issues.');
            return;
        }

        $this->logger->warning('There are open issues:');
        foreach ($issues as $issue) {
            $this->logger->warning("    {$issue->number}. {$issue->title}");
            $this->logger->warning("        {$issue->url}");
        }
    }
}
