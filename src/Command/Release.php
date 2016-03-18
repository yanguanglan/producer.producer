<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Command;

/**
 *
 * Release the package after validating it.
 *
 * @package producer/producer
 *
 */
class Release extends Validate
{
    /**
     *
     * The command logic.
     *
     * @param array $argv Command line arguments.
     *
     * @return mixed
     *
     */
    public function __invoke(array $argv)
    {
        $this->logger->warning("THIS WILL RELEASE THE PACKAGE.");
        parent::__invoke($argv);

        $this->logger->info("Releasing $this->package $this->version");

        $preRelease = substr($this->version, 0, 2) == '0.'
            || strpos($this->version, 'dev') !== false
            || strpos($this->version, 'alpha') !== false
            || strpos($this->version, 'beta') !== false;

        $this->api->release(
            $this->repo->getBranch(),
            $this->version,
            $this->repo->getChanges(),
            $preRelease
        );

        $this->repo->sync();
        $this->logger->info("Released $this->package $this->version !");
    }
}
