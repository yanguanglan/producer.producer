<?php
namespace Producer;

use Producer\Api\Github;

class GitHubTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider remoteProvider
     */
    public function testRepoNameCanBeDerivedFromRemote($remote, $hostname, $repoName)
    {
        $api = new Github(
            $remote,
            $hostname,
            'username',
            'token'
        );

        $this->assertEquals($repoName, $api->getRepoName());
    }

    public function remoteProvider()
    {   
        return [
            ['git@github.com:user/repo.git', 'api.github.com', 'user/repo'],
            ['http://github.com/user/repo.git', 'api.github.com', 'user/repo'],
            ['https://github.com/user/repo.git', 'api.github.com', 'user/repo'],
            ['git@example.org:user/repo.git', 'example.org', 'user/repo'],
            ['http://example.org/user/repo.git', 'example.org', 'user/repo'],
            ['https://example.org/user/repo.git', 'example.org', 'user/repo'],
        ];
    }
}
