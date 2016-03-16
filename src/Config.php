<?php
namespace Producer;

class Config
{
    protected $data = [];
    protected $file = '.producer/config';

    public function __construct(Fsio $fsio)
    {
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
