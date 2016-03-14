<?php
namespace Producer\Api;

class Github implements ApiInterface
{
    protected $apiurl;
    protected $repo;

    public function __construct($user, $token, $origin)
    {
        $this->apiurl = "https://{$user}:{$token}@api.github.com";
        $this->setRepo($origin);
    }

    protected function setRepo($origin)
    {
        $repo = $this->getRepo($origin);
        if (substr($repo, -4) == '.git') {
            $repo = substr($repo, 0, -4);
        }
        $this->repo = $repo;
    }

    protected function getRepo($origin)
    {
        $ssh = 'git@github.com:';
        $len = strlen($ssh);
        if (substr($origin, 0, $len) == $ssh) {
            return substr($origin, $len);
        }

        // presume https://
        return parse_url($origin, PHP_URL_PATH);
    }

    protected function api($method, $path, $body = null, $one = false)
    {
        if (strpos($path, '?') === false) {
            $path .= '?';
        } else {
            $path .= '&';
        }

        $page = 1;
        $list = array();

        do {

            $url = $this->apiurl . $path . "page={$page}";
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", [
                        'User-Agent: php/stream',
                        'Accept: application/json',
                        'Content-Type: application/json',
                    ]),
                    'content' => $body,
                ],
            ]);
            $data = file_get_contents($url, FALSE, $context);
            $json = json_decode($data);

            // for POST etc, do not try additional pages
            $one_page_only = strtolower($method) !== 'get' || $one == true;
            if ($one_page_only) {
                return $json;
            }

            // add results to the list
            if ($json) {
                foreach ($json as $item) {
                    $list[] = $item;
                }
            }

            // next page!
            $page ++;

        } while ($json);

        return $list;
    }

    public function fetchIssues()
    {
        return $this->api('GET', "/repos/{$this->repo}/issues?sort=created&direction=asc");
    }
}
