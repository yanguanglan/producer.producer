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
    protected $repoName;

    public function __construct($origin, $token)
    {
        $this->apiurl = "https://gitlab.com/api/v3";
        $this->token = $token;
        $this->setRepoName($origin);
    }

    protected function setRepoName($origin)
    {
        $repoName = $this->getRepoOrigin($origin);
        if (substr($repoName, -4) == '.git') {
            $repoName = substr($repoName, 0, -4);
        }
        $this->repoName = trim($repoName, '/');
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

    public function getRepoName()
    {
        return $this->repoName;
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

            $url = $this->apiurl . $path;
            if (! $one) {
                $url .= "page={$page}";
            }

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

    public function issues()
    {
        $repoName = urlencode($this->repoName);
        $list = $this->api('GET', "/projects/{$repoName}/issues?sort=asc");
        $issues = [];
        $url = "https://gitlab.com/{$this->repoName}/issues/";
        foreach ($list as $issue) {
            $issues[] = (object) [
                'number' => $issue->iid,
                'title' => $issue->title,
                'url' => $url . $issue->iid,
            ];
        }
        return $issues;
    }

    public function release($source, $version, $changes, $preRelease)
    {
        $body = json_encode([
            'id' => $this->repoName,
            'tag_name' => $version,
            'ref' => $source,
            'release_description' => $changes
        ]);

        $repoName = urlencode($this->repoName);
        $response = $this->api(
            'POST',
            "/projects/{$repoName}/repository/tags",
            $body,
            true
        );

        if (! isset($response->name)) {
            $message = var_export((array) $response, true);
            throw new Exception($message);
        }

        return $response;
    }
}
