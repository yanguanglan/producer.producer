<?php
namespace Producer;

use Producer\Api\Github;

class GitHubTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetGithubRepoName()
    {
        $api = new Github(
            'git@github.com:producerphp/producer.producer.git',
            'api.github.com',
            'username',
            'token'
        );

        $this->assertEquals('producerphp/producer.producer', $api->getRepoName());
    }

    public function testCanGetGitHubEnterpriseRepoName()
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
