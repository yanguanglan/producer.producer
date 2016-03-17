<?php
namespace Producer\Vcs;

use Producer\Exception;
use Producer\Fsio;
use Psr\Log\LoggerInterface;

/**
 *
 * @package producer/producer
 *
 */
abstract class AbstractVcs implements VcsInterface
{
    protected $configFile = '';
    protected $configData = [];

    public function __construct(Fsio $fsio, LoggerInterface $logger)
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
        $this->configData = $this->fsio->parseIni($this->configFile, true);
    }

    abstract public function getOrigin();

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

    public function getComposer()
    {
        return json_decode($this->fsio->get('composer.json'));
    }

    public function checkSupportFiles()
    {
        $files = [
            'CHANGELOG',
            'CONTRIBUTING',
            'LICENSE',
            'README',
        ];
        foreach ($files as $file) {
            $found = $this->fsio->isFile($file, "{$file}.md");
            if (! $found) {
                throw new Exception("{$file} file is missing.");
            }
        }

        $files = [
            'phpunit.xml.dist',
            'tests/bootstrap.php',
        ];
        foreach ($files as $file) {
            if (! $this->fsio->isFile($file)) {
                throw new Exception("{$file} file is missing.");
            }
        }
    }

    public function checkLicenseYear()
    {
        $file = $this->fsio->isFile('LICENSE', 'LICENSE.md');
        $license = $this->fsio->get($file);
        $year = date('Y');
        if (strpos($license, $year) === false) {
            throw new Exception('The LICENSE copyright year looks out-of-date.');
        }
    }
}
