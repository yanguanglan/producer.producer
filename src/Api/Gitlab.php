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
use Producer\Repo\RepoInterface;

/**
 *
 * The GitLab API.
 *
 * @package producer/producer
 *
 */
class Gitlab implements ApiInterface
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
     * The secret token.
     *
     * @var string
     *
     */
    protected $token;

    /**
     *
     * Constructor.
     *
     * @param string $origin The repository remote origin.
     *
     * @param string $token The API secret token.
     *
     */
    public function __construct($origin, $token)
    {
        $this->apiurl = "https://gitlab.com/api/v3";
        $this->token = $token;

        $ssh = 'git@gitlab.com:';
        $len = strlen($ssh);
        if (substr($origin, 0, $len) == $ssh) {
            $repoName = substr($origin, $len);
        } else {
            // presume https://
            $repoName = parse_url($origin, PHP_URL_PATH);
        }

        if (substr($repoName, -4) == '.git') {
            $repoName = substr($repoName, 0, -4);
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

    /**
     *
     * Returns a list of open issues from the API.
     *
     * @return array
     *
     */
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

    /**
     *
     * Submits a release to the API.
     *
     * @param string $source The source branch, tag, or commit hash.
     *
     * @param string $version The version number to release.
     *
     */
    public function release(RepoInterface $repo, $version)
    {
        $body = json_encode([
            'id' => $this->repoName,
            'tag_name' => $version,
            'ref' => $repo->getBranch(),
            'release_description' => $repo->getChanges()
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

        $repo->sync();
    }
}
