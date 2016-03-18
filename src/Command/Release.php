<?php
namespace Producer\Command;

class Release extends Validate
{
    public function __invoke(array $argv)
    {
        $this->setVersion(array_shift($argv));
        $this->setComposerAndPackage();

        $this->logger->warning("THIS WILL RELEASE THE PACKAGE.");
        $this->logger->warning("YOU HAVE 3 SECONDS TO CANCEL.");
        sleep(3);

        $this->validate();
        $this->release();
    }

    protected function release()
    {
        $this->logger->info("Releasing $this->package $this->version");
        $changes = $this->fsio->isFile('CHANGES', 'CHANGES.md');
        $this->api->release(
            $this->repo->getBranch(),
            $this->version,
            $this->fsio->get($changes),
            $this->isPreRelease()
        );
        $this->repo->pull();
        $this->logger->info("Released $this->package $this->version !");
    }

    protected function isPreRelease()
    {
        return substr($this->version, 0, 2) == '0.'
            || strpos($this->version, 'dev') !== false
            || strpos($this->version, 'alpha') !== false
            || strpos($this->version, 'beta') !== false;
    }
}
