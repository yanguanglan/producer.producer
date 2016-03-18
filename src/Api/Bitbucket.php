<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer\Api;

/**
 *
 * The BitBucket API.
 *
 * @package producer/producer
 *
 */
class Bitbucket implements ApiInterface
{
    /**
     *
     * The URL to the API.
     *
     * @var string
     *
     */
    protected $apiurl;

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
     * Constructor.
     *
     * @param string $origin The repository remote origin.
     *
     * @param string $user The API username.
     *
     * @param string $pass The API password.
     *
     */
    public function __construct($origin, $user, $pass)
    {
        $this->apiurl = "https://{$user}:{$pass}@api.bitbucket.org/2.0";
        $repoName = parse_url($origin, PHP_URL_PATH);
        if (substr($repoName, -4) == '.hg') {
            $repoName = substr($repoName, 0, -3);
        }
        $this->repoName = trim($repoName, '/');
    }

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

    /**
     *
     * Call the API via HTTP.
     *
     * @param string $method The HTTP request method.
     *
     * @param string $path The API path.
     *
     * @param string $body The HTTP request body.
     *
     * @param bool $one Make only one call, not many to get many pages.
     *
     * @return mixed
     *
     */
    protected function api($method, $path, $body = '', $one = false)
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

    /**
     *
     * Returns a list of open issues from the API.
     *
     * @return array
     *
     */
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

    /**
     *
     * Submits a release to the API.
     *
     * @param string $source The source branch, tag, or commit hash.
     *
     * @param string $version The version number to release.
     *
     * @param string $changes The change notes for this release.
     *
     * @param bool $preRelease Is this a pre-release (non-production) version?
     *
     * @return mixed
     *
     */
    public function release($source, $version, $changes, $preRelease)
    {
        throw new Exception('Bitbucket release not implemented.');
    }
}
