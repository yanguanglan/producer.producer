<?php
namespace Producer\Api;

use Producer\Exception;

/**
 *
 * @package producer/producer
 *
 */
class Github implements ApiInterface
{
    protected $apiurl;
    protected $repoName;

    public function __construct($origin, $user, $token)
    {
        $this->apiurl = "https://{$user}:{$token}@api.github.com";
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
        $ssh = 'git@github.com:';
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
        $list = $this->api('GET', "/repos/{$this->repoName}/issues?sort=created&direction=asc");
        $issues = [];
        foreach ($list as $issue) {
            $issues[] = (object) [
                'title' => $issue->title,
                'number' => $issue->number,
                'url' => $issue->html_url,
            ];
        }
        return $issues;
    }

    public function release($source, $version, $changes, $isPreRelease)
    {
        $body = json_encode([
            'tag_name' => $version,
            'target_commitish' => $source,
            'name' => $version,
            'body' => $changes,
            'draft' => false,
            'prerelease' => $isPreRelease,
        ]);

        $response = $this->api(
            'POST',
            "/repos/{$this->repoName}/releases",
            $body,
            true
        );

        if (! isset($response->id)) {
            $message = var_export((array) $response, true);
            throw new Exception($message);
        }

        return $response;
    }
}
