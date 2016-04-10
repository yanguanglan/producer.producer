<?php
namespace Producer;

class Http
{
    protected $base;

    public function __construct($base)
    {
        $this->base = $base;
    }

    public function get($path, array $query = [])
    {
        $url = $this->base . $path;
        $context = $this->newContext('GET');
        $page = 0;
        do {
            $page ++;
            $query['page'] = $page;
            $url .= '?' . http_build_query($query);
            $json = json_decode(file_get_contents($url, FALSE, $context));
            if ($json) {
                yield $json;
            }
        } while ($json);
    }

    public function post($path, array $query = [], array $data = [])
    {
        $url = $this->base . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $context = $this->newContext('POST', $data);
        $data = file_get_contents($url, FALSE, $context);
        return json_decode($data);
    }

    protected function newContext($method, array $data = [])
    {
        $http = [
            'method' => $method,
            'header' => implode("\r\n", [
                'User-Agent: php/stream',
                'Accept: application/json',
                'Content-Type: application/json',
            ]),
        ];

        if ($data) {
            $http['content'] = json_encode($data);
        }

        return stream_context_create(['http' => $http, 'https' => $http]);
    }
}
