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
class Gitlab extends AbstractApi
{
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
     * The configured hostname for Gitlab
     *
     * @var string
     *
     */
    protected $hostname;

    /**
     *
     * Constructor.
     *
     * @param string $origin The repository remote origin.
     *
     * @param string $hostname
     * @param string $token  The API secret token.
     */
    public function __construct($origin, $hostname, $token)
    {
        // set the HTTP object and token
        $this->setHttp("http://{$hostname}/api/v3");
        $this->token = $token;
        $this->hostname = $hostname;

        $this->setRepoNameFromOrigin($origin);
    }

    private function setRepoNameFromOrigin($origin)
    {
        // If SSH, strip username off so that `parse_url`
        // can work as expected
        if (strpos($origin, 'git@') !== false) {
            $origin = substr($origin, 4);
        }

        // start by presuming HTTPS
        $repoName = parse_url($origin, PHP_URL_PATH);

        // strip .git from the end
        $repoName = preg_replace('/\.git$/', '', $repoName);

        // retain
        $this->repoName = trim($repoName, '/');
    }

    /**
     *
     * Modifies query params to add a page and other API-specific params.
     *
     * @param array $query The query params.
     *
     * @param int $page The page number, if any.
     *
     * @return array
     *
     */
    protected function httpQuery(array $query, $page = 0)
    {
        $query['private_token'] = $this->token;
        return parent::httpQuery($query, $page);
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
        $issues = [];

        $repoName = urlencode($this->repoName);
        $yield = $this->httpGet(
            "/projects/{$repoName}/issues",
            [
                'sort' => 'asc',
            ]
        );

        foreach ($yield as $issue) {
            $issues[] = (object) [
                'number' => $issue->iid,
                'title' => $issue->title,
                'url' => "https://{$this->hostname}/{$this->repoName}/issues/{$issue->iid}",
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
        $query = [];

        $data = [
            'id' => $this->repoName,
            'tag_name' => $version,
            'ref' => $repo->getBranch(),
            'release_description' => $repo->getChanges()
        ];

        $repoName = urlencode($this->repoName);
        $response = $this->httpPost(
            "/projects/{$repoName}/repository/tags",
            $query,
            $data
        );

        if (! isset($response->name)) {
            $message = var_export((array) $response, true);
            throw new Exception($message);
        }

        $repo->sync();
    }
}
