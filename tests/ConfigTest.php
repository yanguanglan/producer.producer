<?php
namespace Producer;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected function makeFsio(array $returnData, $isFile = true)
    {
        $fsio = $this->getMockBuilder(Fsio::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFile', 'parseIni'])
            ->getMock();
        $fsio
            ->expects($this->any())
            ->method('isFile')->will($this->returnValue($isFile));
        $fsio
            ->expects($this->any())
            ->method('parseIni')->will($this->returnValue($returnData));

        return $fsio;
    }

    /* Tests */
    public function test_it_does_not_load_a_missing_project_config_file()
    {
        $home = $this->makeFsio(['one' => 1, 'two' => ['a' => 'a', 'b' => 'b']]);
        $repo = $this->makeFsio([], false);

        $config = new Config($home, $repo);

        $this->assertEquals([
            'one' => 1,
            'two' => [
                'a' => 'a',
                'b' => 'b'
            ]
        ], $config->getAll(), "failed to append project configuration");
    }

    public function test_it_appends_project_config_items()
    {
        $home = $this->makeFsio(['one' => 1, 'two' => ['a' => 'a']]);
        $repo = $this->makeFsio(['three' => 3, 'two' => ['b' => 'b']]);

        $config = new Config($home, $repo);
        
        $this->assertEquals([
            'one' => 1,
            'two' => [
                'a' => 'a',
                'b' => 'b'
            ],
            'three' => 3,
        ], $config->getAll(), "failed to append project configuration");
    }

    public function test_it_overwrites_global_config()
    {
        $home = $this->makeFsio(['one' => 1, 'two' => ['a' => 'a']]);
        $repo = $this->makeFsio(['one' => 'one', 'two' => ['a' => 'b']]);

        $config = new Config($home, $repo);

        $actual = $config->getAll();

        $this->assertEquals([
            'one' => 'one',
            'two' => ['a' => 'b'],
        ], $actual, "failed to overwrite project configuration");
    }
}

