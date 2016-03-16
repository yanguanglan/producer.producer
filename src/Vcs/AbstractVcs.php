<?php
namespace Producer\Vcs;

use Producer\Exception;
use Producer\Fsio;
use Psr\Log\LoggerInterface;

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
            '.scrutinizer.yml',
            '.travis.yml',
            'CHANGELOG',
            'CONTRIBUTING',
            'LICENSE',
            'phpunit.bootstrap.php',
            'phpunit.xml.dist',
            'README',
        ];

        foreach ($files as $file) {
            if (! $this->fsio->isFile($file)) {
                throw new Exception("Support file '{$file}' not found.");
            }
        }
    }

    public function checkLicenseYear()
    {
        $license = $this->fsio->get('LICENSE');
        $year = date('Y');
        if (strpos($license, $year) === false) {
            throw new Exception('The LICENSE copyright year looks out-of-date.');
        }
    }
}
