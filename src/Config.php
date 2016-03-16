<?php
namespace Producer;

/**
 *
 * @package producer/producer
 *
 */
class Config
{
    protected $data = [];
    protected $file = '.producer/config';

    public function __construct(Fsio $fsio)
    {
        if (! $fsio->isFile($this->file)) {
            $path = $fsio->path($this->file);
            throw new Exception("Config file {$path} not found.");
        }

        $this->data = $fsio->parseIni($this->file);
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        throw new Exception("No value set for '$key' in '{$this->file}'.");
    }
}
