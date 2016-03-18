<?php
namespace Producer\Api;

/**
 *
 * @package producer/producer
 *
 */
interface ApiInterface
{
    public function getRepoName();
    public function issues();
    public function release($source, $version, $changes, $preRelease);
}
