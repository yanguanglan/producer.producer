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
 * The Github API.
 *
 * @package producer/producer
 *
 */
class Github implements ApiInterface
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
     * @param string $token The API secret token.
     *
     */
    public function __construct($origin, $user, $token)
    {
        $this->apiurl = "https://{$user}:{$token}@api.github.com";

        $ssh = 'git@github.com:';
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

    /**
     *
     * Submits a release to the API.
     *
     * @param RepoInterface $repo The repository.
     *
     * @param string $version The version number to release.
     *
     */
    public function release(RepoInterface $repo, $version)
    {
        $prerelease = substr($version, 0, 2) == '0.'
            || strpos($version, 'dev') !== false
            || strpos($version, 'alpha') !== false
            || strpos($version, 'beta') !== false;

        $body = json_encode([
            'tag_name' => $version,
            'target_commitish' => $repo->getBranch(),
            'name' => $version,
            'body' => $repo->getChanges(),
            'draft' => false,
            'prerelease' => $prerelease,
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

        $repo->sync();
    }
}
