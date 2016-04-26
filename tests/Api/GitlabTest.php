<?php
namespace Producer;

use Producer\Api\Gitlab;

class GitlabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider remoteProvider
     */
    public function testRepoNameCanBeDerivedFromRemote($remote, $hostname, $repoName)
    {
        $api = new Gitlab(
            $remote,
            $hostname,
            'token'
        );

        $this->assertEquals($repoName, $api->getRepoName());
    }

    public function remoteProvider()
    {   
        return [
            ['git@gitlab.com:user/repo.git', 'gitlab.com', 'user/repo'],
            ['http://gitlab.com/user/repo.git', 'gitlab.com', 'user/repo'],
            ['https://gitlab.com/user/repo.git', 'gitlab.com', 'user/repo'],
            ['git@example.org:user/repo.git', 'example.org', 'user/repo'],
            ['http://example.org/user/repo.git', 'example.org', 'user/repo'],
            ['https://example.org/user/repo.git', 'example.org', 'user/repo'],
        ];
    }
}
