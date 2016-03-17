<?php
namespace Producer\Repo;

use Producer\Exception;

/**
 *
 * @package producer/producer
 *
 */
class Git extends AbstractRepo
{
    protected $configFile = '.git/config';

    public function getOrigin()
    {
        if (! isset($this->configData['remote origin']['url'])) {
            throw new Exception('Could not determine remote origin.');
        }
        return $this->configData['remote origin']['url'];
    }

    public function getBranch()
    {
        $branch = $this->shell('git rev-parse --abbrev-ref HEAD', $output, $return);
        if ($return) {
            throw new Exception(implode(PHP_EOL, $output), $return);
        }
        return trim($branch);
    }

    public function checkout($branch)
    {
        $this->shell("git checkout {$branch}", $output, $return);
        if ($return) {
            throw new Exception(implode(PHP_EOL, $output), $return);
        }
    }

    public function pull()
    {
        $last = $this->shell('git pull', $output, $return);
        if ($return) {
            throw new Exception($last, $return);
        }
    }

    public function getChangelogDate()
    {
        $file = $this->fsio->isFile('CHANGELOG', 'CHANGELOG.md');
        if (! $file) {
            throw new Exception("{$file} is missing.");
        }

        $this->shell("git log -1 {$file}", $output, $return);
        return $this->findDate($output);
    }

    public function getLastCommitDate()
    {
        $this->shell("git log -1", $output, $return);
        return $this->findDate($output);
    }

    protected function findDate($lines)
    {
        foreach ($lines as $line) {
            if (substr($line, 0, 5) == 'Date:') {
                return trim(substr($line, 5));
            }
        }

        throw new Exception("No 'Date:' line found.");
    }

    public function logSinceDate($date)
    {
        $this->shell("git log --name-only --since='$date' --reverse", $output);
        return $output;
    }
}
