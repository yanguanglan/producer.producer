<?php
namespace Producer\Repo;

/**
 *
 * @package producer/producer
 *
 */
interface RepoInterface
{
    public function getOrigin();
    public function getBranch();
    public function sync();
    public function checkSupportFiles();
    public function checkLicenseYear();
}
