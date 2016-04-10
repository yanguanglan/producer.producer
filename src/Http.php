<?php
namespace Producer;

class Http
{
    protected $base;

    public function __construct($base)
    {
        $this->base = $base;
    }

    public function __invoke($method, $path, array $query = [], array $data = [])
    {
        $url = $this->base . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $context = $this->newContext($method);
        return json_decode(file_get_contents($url, FALSE, $context));
    }

    public function get($path, array $query = [])
    {
        return $this('GET', $path, $query);
    }

    public function post($path, array $query = [], array $data = [])
    {
        return $this('POST', $path, $query, $data);
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
