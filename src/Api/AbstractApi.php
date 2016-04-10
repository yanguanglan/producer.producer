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
        $page = 1;
        do {
            $found = false;
            $query['page'] = $page;
            $query = $this->httpQuery($query);
            $json = $this->http->get($path, $query);
            foreach ($this->httpValue($json) as $item) {
                $found = true;
                yield $item;
            }
            $page ++;
        } while ($found);
    }

    protected function httpPost($path, array $query = [], array $data = [])
    {
        $query = $this->httpQuery($query);
        return $this->http->post($path, $query, $data);
    }

    protected function httpQuery(array $query, $page = null)
    {
        if ($page !== null) {
            $query['page'] = $page;
        }
        return $query;
    }

    protected function httpValue($json)
    {
        return $json;
    }
}
