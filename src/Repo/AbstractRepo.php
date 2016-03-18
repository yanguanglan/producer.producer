<?php
namespace Producer\Repo;

use Producer\Exception;
use Producer\Fsio;
use Psr\Log\LoggerInterface;

/**
 *
 * @package producer/producer
 *
 */
abstract class AbstractRepo implements RepoInterface
{
    protected $configFile = '';
    protected $configData = [];
    protected $composer;

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

    public function validateComposer()
    {
        $this->shell('composer validate');
    }

    public function getComposer()
    {
        if (! $this->composer) {
            $this->composer = json_decode($this->fsio->get('composer.json'));
        }
        return $this->composer;
    }

    public function checkSupportFiles()
    {
        $files = [
            'CHANGES',
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

    public function runTests()
    {
        $this->shell('composer update');
        $line = $this->shell('phpunit', $output, $return);
        if ($return) {
            throw new Exception($line);
        }
    }

    public function getChanges()
    {
        $file = $this->fsio->isFile('CHANGES', 'CHANGES.md');
        return $this->fsio->get($file);
    }

    public function checkDocblocks()
    {
        // where to write validation records?
        $target = $this->fsio->path('/tmp/phpdoc');

        // remove previous validation records, if any
        $this->shell("rm -rf {$target}");

        // validate
        $cmd = "phpdoc -d src/ -t {$target} --force --verbose --template=xml";
        $line = $this->shell($cmd, $output, $return);

        // get the XML file
        $xml = simplexml_load_file("{$target}/structure.xml");

        // are there missing @package tags?
        $missing = false;
        foreach ($xml->file as $file) {

            // get the expected package name
            $class = $file->class->full_name . $file->interface->full_name;

            // class-level tag (don't care about file-level)
            $package = $file->class['package'] . $file->interface['package'];
            $composer = $this->getComposer();
            if ($package && $package != $composer->name) {
                $missing = true;
                $message = "  Expected class-level @package {$composer->name}, "
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
    }
}
