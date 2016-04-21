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
        'bitbucket_password' => null,
        'bitbucket_username' => null,
        'github_token' => null,
        'github_username' => null,
        'gitlab_token' => null,
        'package' => '',
        'commands' => [
            'phpdoc' => 'phpdoc',
            'phpunit' => 'phpunit',
        ],
        'files' => [
            'changelog' => '',
            'changes' => '',
            'contributing' => 'CONTRIBUTING.md',
            'license' => 'LICENSE.md',
            'phpunit' => 'phpunit.xml.dist',
            'readme' => 'README.md',
        ],
    ];

    /**
     *
     * The name of the Producer config file, wherever located.
     *
     * @var string
     *
     */
    protected $configFile = '.producer/config';

    /**
     *
     * Constructor.
     *
     * @param Fsio $homefs The user's home directory filesystem.
     *
     * @param Fsio $repofs The package repository filesystem.
     *
     * @throws Exception
     *
     */
    public function __construct(Fsio $homefs, Fsio $repofs)
    {
        $this->loadHomeConfig($homefs);
        $this->loadRepoConfig($repofs);
        $this->fixChangelogValue($repofs);
        unset($this->data['files']['changes']);
    }

    /**
     *
     * Loads the user's home directory Producer config file.
     *
     * @param Fsio $homefs The user's home directory filesystem.
     *
     * @throws Exception
     *
     */
    protected function loadHomeConfig(Fsio $homefs)
    {
        if (! $homefs->isFile($this->configFile)) {
            $path = $homefs->path($this->configFile);
            throw new Exception("Config file {$path} not found.");
        }

        $config = $homefs->parseIni($this->configFile, true);
        $this->data = array_replace_recursive($this->data, $config);
    }

    /**
     *
     * Loads the project's config file, if it exists.
     *
     * @param Fsio $repofs The package repository filesystem.
     *
     * @throws Exception
     *
     */
    protected function loadRepoConfig(Fsio $repofs)
    {
        if (! $repofs->isFile($this->configFile)) {
            return;
        }

        $config = $repofs->parseIni($this->configFile, true);
        $this->data = array_replace_recursive($this->data, $config);
    }

    /**
     *
     * Fixes the 'changelog' setting value.
     *
     * After the 2.0 stable release, Jordi Boggiano noted that `CHANGELOG.md`
     * was by far a more common file name than `CHANGES.md`. In the interest
     * of maintaining backwards compatibility, this method allows for both the
     * old 'changes' config setting, and a new 'changelog' config setting.
     * The newer setting takes precedence. If the setting is not explicitly
     * specified, it defaults back to `CHANGES.md` **only** if there is already
     * a `CHANGES.md` file; otherwise, it defaults to `CHANGELOG.md`. This
     * should make it easier on users who have already adopted Producer with a
     * `CHANGES.md` file, and allow for new users to default to `CHANGELOG.md`.
     *
     * @param Fsio $repofs The package repository filesystem.
     *
     */
    protected function fixChangelogValue(Fsio $repofs)
    {
        // explicit new 'changelog' setting? remove old 'changes' setting.
        if ($this->data['files']['changelog']) {
            return;
        }

        // explicit old 'changes' setting? convert to 'changelog'.
        if ($this->data['files']['changes']) {
            $this->data['files']['changelog'] = $this->data['files']['changes'];
            return;
        }

        // no new 'changelog' setting, and no old 'changes' setting.
        // look for the old default `CHANGES.md` file.
        if ($repofs->isFile('CHANGES.md')) {
            $this->data['files']['changelog'] = 'CHANGES.md';
            return;
        }

        // no old `CHANGES.md` file? default to `CHANGELOG.md`.
        $this->data['files']['changelog'] = 'CHANGELOG.md';
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

        throw new Exception("No config value set for '$key'.");
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
        return $this->data;
    }
}
