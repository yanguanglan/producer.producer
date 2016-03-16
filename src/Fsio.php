<?php
namespace Producer;

use Producer\Exception;

/**
 *
 * @package producer/producer
 *
 */
class Fsio
{
    protected $root;

    public function __construct($root)
    {
        $root = DIRECTORY_SEPARATOR . ltrim($root, DIRECTORY_SEPARATOR);
        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->root = $root;
    }

    public function path($spec)
    {
        $spec = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $spec);
        return $this->root . trim($spec, DIRECTORY_SEPARATOR);
    }

    public function get($file)
    {
        $file = $this->path($file);

        $level = error_reporting(0);
        $result = file_get_contents($file);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    public function put($file, $data)
    {
        $file = $this->path($file);

        $level = error_reporting(0);
        $result = file_put_contents($file, $data);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    public function parseIni($file, $sections = false, $mode = INI_SCANNER_NORMAL)
    {
        $file = $this->path($file);

        $level = error_reporting(0);
        $result = parse_ini_file($file, $sections, $mode);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    public function isFile($file)
    {
        $file = $this->path($file);
        return file_exists($file) && is_readable($file);
    }

    public function isDir($dir)
    {
        $dir = $this->path($dir);
        return is_dir($dir);
    }

    public function mkdir($dir, $mode = 0777, $deep = true)
    {
        $dir = $this->path($dir);

        $level = error_reporting(0);
        $result = mkdir($dir, $mode, $deep);
        error_reporting($level);

        if ($result !== false) {
            return;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    public function getCwd()
    {
        return getcwd();
    }
}
