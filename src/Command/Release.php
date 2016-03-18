<?php
namespace Producer\Command;

class Release extends Validate
{
    public function __invoke(array $argv)
    {
        $this->prep(array_shift($argv));

        $this->logger->warning("THIS WILL RELEASE THE PACKAGE.");
        $this->logger->warning("YOU HAVE 3 SECONDS TO CANCEL.");
        sleep(3);

        $this->validate();
        $this->release();
    }

    protected function release()
    {
        $this->logger->info("Releasing $this->package $this->version");
        $this->api->release(
            $this->repo->getBranch(),
            $this->version,
            $this->repo->getChanges(),
            $this->isPreRelease()
        );
        $this->repo->sync();
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
