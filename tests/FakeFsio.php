<?php
namespace Producer;

class FakeFsio extends Fsio
{
    protected $files = array();
    protected $dirs = array();

    public function get($file)
    {
        $file = $this->path($file);
        return $this->files[$file];
    }

    public function put($file, $data)
    {
        $file = $this->path($file);
        $this->files[$file] = $data;
    }

    public function parseIni($file, $sections = false, $mode = INI_SCANNER_NORMAL)
    {
        $file = $this->path($file);
        return parse_ini_string($this->files[$file], $sections, $mode);
    }

    public function isFile($file)
    {
        $file = $this->path($file);
        return isset($this->files[$file]);
    }

    public function unlink($file)
    {
        $file = $this->path($file);
        unset($this->files[$file]);
    }

    public function isDir($dir)
    {
        $dir = $this->path($dir);
        return isset($this->dirs[$dir]);
    }

    public function mkdir($dir, $mode = 0777, $deep = true)
    {
        $dir = $this->path($dir);
        $this->dirs[$dir] = true;
    }

    public function rmdir($dir)
    {
        $dir = $this->path($dir);
        unset($this->dirs[$dir]);
    }

    public function setDirs(array $dirs)
    {
        $this->dirs = $dirs;
    }

    public function setFiles(array $files)
    {
        $this->files = $files;
    }
}
