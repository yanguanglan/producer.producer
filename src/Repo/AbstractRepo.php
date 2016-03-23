<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Repo;

use Producer\Exception;
use Producer\Fsio;
use Psr\Log\LoggerInterface;

/**
 *
 * Base class for local VCS repos.
 *
 * @package producer/producer
 *
 */
abstract class AbstractRepo implements RepoInterface
{
    /**
     *
     * The VCS config file name.
     *
     * @var string
     *
     */
    protected $configFile = '';

    /**
     *
     * The VCS config file data.
     *
     * @var array
     *
     */
    protected $configData = [];

    /**
     *
     * The `composer.json` data.
     *
     * @var object
     *
     */
    protected $composer;

    /**
     *
     * A filesystem I/O object.
     *
     * @var Fsio
     *
     */
    protected $fsio;

    /**
     *
     * A logger.
     *
     * @var LoggerInterface
     *
     */
    protected $logger;

    /**
     *
     * Constructor.
     *
     * @param Fsio $fsio A filesystem I/O object.
     *
     * @param LoggerInterface $logger A logger.
     *
     */
    public function __construct(Fsio $fsio, LoggerInterface $logger)
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
        $this->configData = $this->fsio->parseIni($this->configFile, true);
    }

    /**
     *
     * Returns the Composer package name.
     *
     * @return string
     *
     */
    public function getPackage()
    {
        return $this->getComposer()->name;
    }

    /**
     *
     * Executes shell commands.
     *
     * @param string $cmd The shell command to execute.
     *
     * @param array $output Returns shell output through the reference.
     *
     * @param mixed $return Returns the exit code through this reference.
     *
     * @return string The last line of output.
     *
     * @see exec
     */
    protected function shell($cmd, &$output = [], &$return = null)
    {
        $cmd = str_replace('; ', ';\\' . PHP_EOL, $cmd);
        $this->logger->debug("> $cmd");
        $output = null;
        $last = exec($cmd, $output, $return);
        foreach ($output as $line) {
            $this->logger->debug("< $line");
        }
        return $last;
    }

    /**
     *
     * Validates the `composer.json` file.
     *
     */
    public function validateComposer()
    {
        $last = $this->shell('composer validate', $output, $return);
        if ($return) {
            throw new Exception($last);
        }
    }

    /**
     *
     * Gets the `composer.json` file data.
     *
     * @return object
     *
     */
    public function getComposer()
    {
        if (! $this->composer) {
            $this->composer = json_decode($this->fsio->get('composer.json'));
        }
        return $this->composer;
    }

    /**
     *
     * Checks the various support files.
     *
     */
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
            if (trim($this->fsio->get($found)) === '') {
                throw new Exception("{$found} file is empty.");
            }
        }

        $files = [
            'phpunit.xml.dist',
        ];
        foreach ($files as $file) {
            if (! $this->fsio->isFile($file)) {
                throw new Exception("{$file} file is missing.");
            }
            if (trim($this->fsio->get($found)) === '') {
                throw new Exception("{$found} file is empty.");
            }
        }
    }

    /**
     *
     * Checks to see that the current year is in the LICENSE.
     *
     */
    public function checkLicenseYear()
    {
        $file = $this->fsio->isFile('LICENSE', 'LICENSE.md');
        $license = $this->fsio->get($file);
        $year = date('Y');
        if (strpos($license, $year) === false) {
            throw new Exception('The LICENSE copyright year looks out-of-date.');
        }
    }

    /**
     *
     * Runs the tests using phpunit.
     *
     */
    public function checkTests()
    {
        $this->shell('composer update');
        $phpunit = $this->which('phpunit');
        $last = $this->shell($phpunit, $output, $return);
        if ($return) {
            throw new Exception($last);
        }
        $this->checkStatus();
    }

    /**
     *
     * Gets the contents of the CHANGES file.
     *
     */
    public function getChanges()
    {
        $file = $this->fsio->isFile('CHANGES', 'CHANGES.md');
        return $this->fsio->get($file);
    }

    /**
     *
     * Checks the `src/` docblocks using phpdoc.
     *
     */
    public function checkDocblocks()
    {
        // where to write validation records?
        $target = $this->fsio->sysTempDir('/phpdoc/' . $this->getPackage());

        // remove previous validation records, if any
        $this->shell("rm -rf {$target}");

        // validate
        $phpdoc = $this->which('phpdoc');
        $cmd = "$phdoc -d src/ -t {$target} --force --verbose --template=xml";
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

    /**
     *
     * Find the closest binary for tools such as phpunit and phpdoc.
     *
     * Look first in the repo vendor/bin, then in the producer vendor/bin
     * (probably a `composer global require producer`), then fall back to the
     * system bin.
     *
     * @param string $bin Which binary to look for.
     *
     * @return string The path to the binary.
     *
     */
    protected function which($bin)
    {
        // is it in the repo itself?
        $repoBin = "vendor/bin/$bin";
        if ($this->fsio->isFile($repoBin)) {
            return $this->fsio->path($repoBin);
        }

        // note that this is in reference to this class directory,
        // not the repository directory. this is to check composer global.
        $composerBin = dirname(dirname(dirname(__DIR__))) . "/vendor/bin/$bin";
        if (file_exists($composerBin) && is_readable($composerBin)) {
            return $composerBin;
        }

        // use a (hopefully) system-available one
        return $bin;
    }
}
