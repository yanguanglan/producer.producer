<?php
/**
 *
 * This file is part of Producer for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Producer;

/**
 *
 * Producer configuration values.
 *
 * @package producer/producer
 *
 */
class Config
{
    /**
     *
     * The config values.
     *
     * @var array
     *
     */
    protected $data = [];

    /**
     *
     * The name of the Producer config file.
     *
     * @var string
     *
     */
    protected $file = '.producer/config';

    /**
     *
     * Constructor.
     *
     * @param Fsio $fsio A filesystem I/O object.
     *
     */
    public function __construct(Fsio $fsio)
    {
        if (! $fsio->isFile($this->file)) {
            $path = $fsio->path($this->file);
            throw new Exception("Config file {$path} not found.");
        }

        $this->data = $fsio->parseIni($this->file);
    }

    /**
     *
     * Returns a config value.
     *
     * @param string $key The config value.
     *
     * @return mixed
     *
     */
    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        throw new Exception("No value set for '$key' in '{$this->file}'.");
    }
}
