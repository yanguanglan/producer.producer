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
     * The config values with defaults.
     *
     * @var array
     *
     */
    protected $data = [
        'markdown_files' => [
            'CHANGES',
            'CONTRIBUTING',
            'LICENSE',
            'README',
        ],

        'required_files' => [
            'phpunit.xml.dist',
        ],

        'license_file' => [
            'LICENSE', 'LICENSE.md'
        ]
    ];

    /**
     *
     * Config items that are CSV and need to be arrays
     *
     * @var array
     *
     */
    protected $csv_items = ['markdown_files', 'required_files'];

    /**
     *
     * The name of the global Producer config file.
     *
     * @var string
     *
     */
    protected $global_file = '.producer/config';

    /**
     *
     * The name of the project's config file
     *
     * @var string
     *
     */
    protected $project_file = '.producer';

    /**
     *
     * Constructor.
     *
     * @param Fsio $homefs
     *
     * @param Fsio $repofs
     *
     * @throws Exception
     *
     */
    public function __construct(Fsio $homefs, Fsio $repofs)
    {
        $this->loadGlobalConfig($homefs);
        $this->loadProjectConfig($repofs);
        $this->parseCsvToArrays();
    }

    /**
     * 
     * Load's Producer's global config file
     * 
     * @param Fsio $fsio
     *
     * @throws Exception
     *
     */
    protected function loadGlobalConfig(Fsio $fsio)
    {
        if (! $fsio->isFile($this->global_file)) {
            $path = $fsio->path($this->global_file);
            throw new Exception("Config file {$path} not found.");
        }

        $this->data = array_merge($this->data, $fsio->parseIni($this->global_file));
    }
    
    /**
     * Loads the project's config file, if it exists
     * 
     * @param Fsio $fsio
     *
     * @throws Exception
     *
     */
    public function loadProjectConfig(Fsio $fsio)
    {
        if ($fsio->isFile($this->project_file)) {
            $config = $fsio->parseIni($this->project_file);
            $this->data = array_merge($this->data, $config);
        }
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

    /**
     *
     * Confirm that a config value is set
     *
     * @param $key
     *
     * @return bool
     *
     */
    public function has($key) {
        return (isset($this->data[$key]));
    }

    /**
     *
     * Return all configuration data
     *
     * @return array
     *
     */
    public function getAll()
    {
        return $this->data ?: [];
    }

    /**
     *
     * Parses desired CSV config items into array
     *
     */
    protected function parseCsvToArrays()
    {
        foreach ($this->csv_items as $item) {
            if (is_string($this->data[$item])) {
                $this->data[$item] = explode(",", trim($this->data[$item]));
            }
        }
    }
}
