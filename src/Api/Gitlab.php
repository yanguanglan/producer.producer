<?php
namespace Producer\Api;

/**
 *
 * @package producer/producer
 *
 */
class Gitlab implements ApiInterface
{
    protected $apiurl;
    protected $repo;

    public function __construct($origin, $token)
    {
        $this->apiurl = "https://gitlab.com/api/v3";
        $this->token = $token;
        $this->setRepo($origin);
    }

    protected function setRepo($origin)
    {
        $repo = $this->getRepoOrigin($origin);
        if (substr($repo, -4) == '.git') {
            $repo = substr($repo, 0, -4);
        }
        $this->repo = trim($repo, '/');
    }

    protected function getRepoOrigin($origin)
    {
        $ssh = 'git@gitlab.com:';
        $len = strlen($ssh);
        if (substr($origin, 0, $len) == $ssh) {
            return substr($origin, $len);
        }

        // presume https://
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
        $path .= "private_token={$this->token}&";

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
        $repo = urlencode($this->repo);
        $list = $this->api('GET', "/projects/{$repo}/issues?sort=asc");
        $issues = [];
        $url = "https://gitlab.com/{$this->repo}/issues/";
        foreach ($list as $issue) {
            $issues[] = (object) [
                'number' => $issue->iid,
                'title' => $issue->title,
                'url' => $url . $issue->iid,
            ];
        }
        return $issues;
    }
}
