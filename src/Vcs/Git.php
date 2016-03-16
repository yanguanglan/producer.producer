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
}
