<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Api;

/**
 *
 * The interface APIs.
 *
 * @package producer/producer
 *
 */
interface ApiInterface
{
    /**
     *
     * Returns the API repository name.
     *
     * @return string
     *
     */
    public function getRepoName();

    /**
     *
     * Returns a list of open issues from the API.
     *
     * @return array
     *
     */
    public function issues();

    /**
     *
     * Submits a release to the API.
     *
     * @param string $source The source branch, tag, or commit hash.
     *
     * @param string $version The version number to release.
     *
     * @param string $changes The change notes for this release.
     *
     * @param bool $preRelease Is this a pre-release (non-production) version?
     *
     * @return mixed
     *
     */
    public function release($source, $version, $changes, $preRelease);
}
