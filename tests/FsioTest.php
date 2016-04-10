<?php
namespace Producer;

class FsioTest extends \PHPUnit_Framework_TestCase
{
    protected $fsio;
    protected $base;

    protected function setUp()
    {
        $this->fsio = new Fsio(__DIR__);
    }

    public function testIsDir()
    {
        $this->assertTrue($this->fsio->isDir('../' . basename(__DIR__)));
    }

    public function testMkdir()
    {
        $dir = 'tmp';
        $this->fsio->rmdir($dir);

        $this->assertFalse($this->fsio->isDir($dir));
        $this->fsio->mkdir($dir);
        $this->assertTrue($this->fsio->isDir($dir));
        $this->fsio->rmdir($dir);
        $this->assertFalse($this->fsio->isDir($dir));

        $this->setExpectedException(
            'Producer\Exception',
            'mkdir(): File exists'
        );
        $this->fsio->mkdir('../' . basename(__DIR__));
    }

    public function testPutAndGet()
    {
        $file = 'fakefile';
        $this->fsio->unlink($file);

        $expect = 'fake text';
        $this->fsio->put($file, $expect);
        $actual = $this->fsio->get($file);
        $this->assertSame($expect, $actual);
        $this->fsio->unlink($file);
    }

    public function testPut_error()
    {
        $this->setExpectedException(
            'Producer\Exception',
            'No such file or directory'
        );
        $this->fsio->put('no-such-directory/fakefile', 'fake text');
    }

    public function testGet_error()
    {
        $this->setExpectedException(
            'Producer\Exception',
            'No such file or directory'
        );
        $this->fsio->get('no-such-directory/fakefile');
    }
}
