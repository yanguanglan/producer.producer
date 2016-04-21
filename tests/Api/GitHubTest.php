<?php
namespace Producer;

use Producer\Api\Github;

class GitHubTest extends \PHPUnit_Framework_TestCase
{
    public function testGitHubEnterprise()
    {
        $api = new Github(
            'git@example.org:producerphp/producer.producer.git',
            'example.org',
            'username',
            'token'
        );

        $this->assertEquals('producerphp/producer.producer', $api->getRepoName());
    }
}
