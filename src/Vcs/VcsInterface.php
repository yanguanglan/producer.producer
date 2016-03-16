<?php
namespace Producer\Vcs;

interface VcsInterface
{
    public function getOrigin();
    public function getBranch();
    public function updateBranch();
    public function checkSupportFiles();
    public function checkLicenseYear();
}
