<?php
namespace Producer;

use Producer\Api\Gitlab;

class GitlabTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetGitlabRepoName()
    {
        $api = new Gitlab(
            'git@gitlab.com:producerphp/producer.producer.git',
            'gitlab.com',
            'token'
        );

        $this->assertEquals('producerphp/producer.producer', $api->getRepoName());
    }

    public function testCanGetGitlabSelfHostedRepoName()
    {
        $api = new Gitlab(
            'git@example.org:producerphp/producer.producer.git',
            'example.org',
            'token'
        );

        $this->assertEquals('producerphp/producer.producer', $api->getRepoName());
    }
}
