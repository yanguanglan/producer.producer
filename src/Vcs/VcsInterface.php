<?php
namespace Producer\Vcs;

/**
 *
 * @package producer/producer
 *
 */
interface VcsInterface
{
    public function getOrigin();
    public function getBranch();
    public function updateBranch();
    public function checkSupportFiles();
    public function checkLicenseYear();
}
