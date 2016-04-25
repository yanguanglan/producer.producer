<?php

namespace Producer;

use Producer\Api\Github;
use ReflectionClass;

class ProducerContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider githubProvider
     */
    public function testThatItReturnsAppropriateApiImplementationForGithub($host, $origin)
    {
        $container = new ProducerContainer($_SERVER['HOME'], getcwd(), STDOUT, STDERR);

        // Unlock ProducerContainer#newApi...
        $reflector = new ReflectionClass($container);
        $newApi = $reflector->getMethod('newApi');
        $newApi->setAccessible(true);

        $config = $this->mockConfig([
            'github_hostname' => $host,
            'github_username' => 'producer',
            'github_token' => 'token',
        ]);

        // $container->newApi($origin, $config) : ApiInterfaces
        $api = $newApi->invokeArgs($container, [$origin, $config]);
        
        $this->assertInstanceOf(Github::class, $api);
    }

    public function githubProvider()
    {
        return [
            ['github.enterprise.com', 'git@github.enterprise.com:producer/producer.git'],
            ['api.github.com', 'git@github.com:producer/producer.git'],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockConfig($data)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($data as $arg => $return) {
            $valueMap[] = [$arg, $return];
        }

        $config->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        return $config;
    }

}
