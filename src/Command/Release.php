<?php
namespace Producer\Command;

use Producer\Api\ApiInterface;
use Producer\Exception;
use Producer\Vcs\VcsInterface;
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

    protected function shell($cmd, &$output = null, &$return = null)
    {
        $cmd = str_replace('; ', ';\\' . PHP_EOL, $cmd);
        $this->logger->debug("> $cmd");
        $output = null;
        $result = exec($cmd, $output, $return);
        foreach ($output as $line) {
            $this->logger->debug("< $line");
        }
        return $result;
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
        $this->runTests();
        $this->checkDocblocks();
        $this->checkChangelog();
        $this->showIssues();

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

    protected function runTests()
    {
        $this->logger->info('Update composer and run tests.');
        $this->shell('composer update');
        $line = $this->shell('phpunit', $output, $return);
        if ($return) {
            throw new Exception($line);
        }
    }

    protected function checkDocblocks()
    {
        $this->logger->info('Checking the docblocks.');

        $target = "/tmp/phpdoc/{$this->package}";

        // remove previous validation records
        $this->shell("rm -rf {$target}");

        // validate
        $cmd = "phpdoc -d src/ -t {$target} --force --verbose --template=xml";
        $line = $this->shell($cmd, $output, $return);

        // remove phpdoc log files
        $this->shell('rm -f phpdoc-*.log');

        // get the XML file and look for errors
        $xml = simplexml_load_file("{$target}/structure.xml");

        // are there missing @package tags?
        $missing = false;
        foreach ($xml->file as $file) {

            // get the expected package name
            $class = $file->class->full_name . $file->interface->full_name;

            // class-level tag (don't care about file-level)
            $package = $file->class['package'] . $file->interface['package'];
            if ($package && $package != $this->composer->name) {
                $missing = true;
                $message = "  Expected class-level @package {$this->composer->name}, "
                    . "actual @package {$package}, "
                    . "in class {$class}";
                $this->logger->error($message);
            }
        }

        if ($missing) {
            throw new Exception('Docblocks do not appear valid.');
        }

        // are there other invalidities?
        foreach ($output as $line) {
            // this line indicates the end of parsing
            if (substr($line, 0, 41) == 'Transform analyzed project into artifacts') {
                break;
            }
            // invalid lines have 2-space indents
            if (substr($line, 0, 2) == '  ') {
                throw new Exception('Docblocks do not appear valid.');
            }
        }

        // guess they're valid
        $this->logger->info('Docblocks appear valid.');
    }

    protected function checkChangelog()
    {
        $this->logger->info('Checking if CHANGELOG up to date.');

        $lastChangelog = $this->vcs->getChangelogDate();
        $this->logger->info("CHANGELOG date is $lastChangelog.");

        $lastCommit = $this->vcs->getLastCommitDate();
        $this->logger->info("Last commit date is $lastCommit.");

        if ($lastChangelog == $lastCommit) {
            $this->logger->info('CHANGELOG appears up to date.');
            return;
        }

        $this->logger->error('CHANGELOG appears out of date.');
        $this->vcs->logSinceDate($lastChangelog);
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
