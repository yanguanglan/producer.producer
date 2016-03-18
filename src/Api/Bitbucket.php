<?php
namespace Producer\Api;

/**
 *
 * @package producer/producer
 *
 */
class Bitbucket implements ApiInterface
{
    protected $apiurl;
    protected $repoName;

    public function __construct($origin, $user, $pass)
    {
        $this->apiurl = "https://{$user}:{$pass}@api.bitbucket.org/2.0";
        $this->setRepoName($origin);
    }

    protected function setRepoName($origin)
    {
        $repoName = $this->getRepoOrigin($origin);
        if (substr($repoName, -4) == '.hg') {
            $repoName = substr($repoName, 0, -3);
        }
        $this->repoName = trim($repoName, '/');
    }

    protected function getRepoOrigin($origin)
    {
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

    public function issues()
    {
        $list = $this->api('GET', "/repositories/{$this->repoName}/issues?sort=created_on");
        $issues = [];
        foreach ($list as $issue) {
            $issues[] = (object) [
                'title' => $issue->title,
                'number' => $issue->id,
                'url' => "https://bitbucket.org/{$this->repoName}/issues/{$issue->id}",
            ];
        }
        return $issues;
    }

    public function release($source, $version, $changes, $preRelease)
    {
        throw new Exception('Bitbucket release not implemented.');
    }
}
