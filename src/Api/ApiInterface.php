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
    public function fetchIssues();
}
