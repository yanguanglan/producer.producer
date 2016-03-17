<?php
namespace Producer\Vcs;

use Producer\Exception;

/**
 *
 * @package producer/producer
 *
 */
class Git extends AbstractVcs
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

    public function updateBranch()
    {
        $last = $this->shell('git pull', $output, $return);
        if ($return) {
            throw new Exception($last, $return);
        }
    }

    public function getChangelogTimestamp()
    {
        $file = $this->fsio->isFile('CHANGELOG', 'CHANGELOG.md');
        if (! $file) {
            throw new Exception("{$file} is missing.");
        }

        $this->shell("git log -1 {$file}", $output, $return);
        return $this->findTimestamp($output);
    }

    public function getLastCommitTimestamp()
    {
        $this->shell("git log -1", $output, $return);
        return $this->findTimestamp($output);
    }

    protected function findTimestamp($lines)
    {
        foreach ($lines as $line) {
            if (substr($line, 0, 5) == 'Date:') {
                $date = trim(substr($line, 5));
                return strtotime($date);
            }
        }

        throw new Exception("No 'Date:' line found.");
    }

    public function logSinceTimestamp($date)
    {
        $since = date('D M j H:i:s Y', $lastCommit);
        $this->shell("git log --name-only --since='$since' --reverse", $output);
        return $output;
    }
}
