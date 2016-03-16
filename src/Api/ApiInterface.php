<?php
namespace Producer\Api;

/**
 *
 * @package producer/producer
 *
 */
interface ApiInterface
{
    public function getRepo();
    public function fetchIssues();
}
