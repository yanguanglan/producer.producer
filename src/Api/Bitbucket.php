<?php
namespace Producer\Api;

class Bitbucket implements ApiInterface
{
    protected $apiurl;
    protected $repo;

    public function __construct($origin, $user, $pass)
    {
        $this->apiurl = "https://{$user}:{$pass}@api.bitbucket.org/2.0";
        $this->setRepo($origin);
    }

    protected function setRepo($origin)
    {
        $repo = $this->getRepoOrigin($origin);
        if (substr($repo, -4) == '.hg') {
            $repo = substr($repo, 0, -3);
        }
        $this->repo = trim($repo, '/');
    }

    protected function getRepoOrigin($origin)
    {
        return parse_url($origin, PHP_URL_PATH);
    }

    public function getRepo()
    {
        return $this->repo;
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
                return $json->values;
            }

            // add results to the list
            if (! empty($json->values)) {
                foreach ($json->values as $item) {
                    $list[] = $item;
                }
            }

            // next page!
            $page ++;

        } while (! empty($json->values));

        return $list;
    }

    public function fetchIssues()
    {
        $list = $this->api('GET', "/repositories/{$this->repo}/issues?sort=created_on");
        $issues = [];
        foreach ($list as $issue) {
            $issues[] = (object) [
                'title' => $issue->title,
                'number' => $issue->id,
                'url' => "https://bitbucket.org/{$this->repo}/issues/{$issue->id}",
            ];
        }
        return $issues;
    }
}
