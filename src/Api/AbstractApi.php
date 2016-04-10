<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Api;

use Producer\Exception;
use Producer\Http;

/**
 *
 * An abstract API class.
 *
 * @package producer/producer
 *
 */
abstract class AbstractApi implements ApiInterface
{
    protected $http;

    /**
     *
     * The API repository name.
     *
     * @var string
     *
     */
    protected $repoName;

    /**
     *
     * Returns the API repository name.
     *
     * @return string
     *
     */
    public function getRepoName()
    {
        return $this->repoName;
    }

    protected function setHttp($base)
    {
        $this->http = new Http($base);
    }

    protected function httpGet($path, array $query = [])
    {
        $query = $this->httpQuery($query);
        foreach ($this->http->get($path, $query) as $json) {
            yield $this->httpValue($json);
        }
    }

    protected function httpPost($path, array $query = [], array $data = [])
    {
        $query = $this->httpQuery($query);
        return $this->http->post($path, $query, $data);
    }

    protected function httpQuery(array $query)
    {
        return $query;
    }

    protected function httpValue($json)
    {
        return $json;
    }
}
