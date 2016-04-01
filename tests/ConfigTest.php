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
        $home = $this->makeFsio(['one' => 1, 'two' => 'two']);
        $repo = $this->makeFsio([], false);

        $config = new Config($home, $repo);

        $this->assertEquals([
            'one' => 1,
            'two' => 'two',
        ], $config->getAll(), "failed to append project configuration");
    }

    public function test_it_appends_project_config_items()
    {
        $home = $this->makeFsio(['one' => 1, 'two' => 'two']);
        $repo = $this->makeFsio(['three' => 3, 'four' => 'four']);

        $config = new Config($home, $repo);
        
        $this->assertEquals([
            'one' => 1,
            'two' => 'two',
            'three' => 3,
            'four' => 'four'
        ], $config->getAll(), "failed to append project configuration");
    }

    public function test_it_overwrites_global_config()
    {
        $home = $this->makeFsio(['one' => 1, 'two' => 'two']);
        $repo = $this->makeFsio(['one' => 'one', 'four' => 'four']);

        $config = new Config($home, $repo);

        $this->assertEquals([
            'one' => 'one',
            'two' => 'two',
            'four' => 'four'
        ], $config->getAll(), "failed to append project configuration");
    }
}

