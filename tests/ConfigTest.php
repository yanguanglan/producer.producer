<?php
namespace Producer;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $homeConfig = '
gitlab_token = foobarbazdibzimgir

[commands]
phpunit = /path/to/phpunit
';

    protected $repoConfig = '
package = Foo.Bar

[commands]
phpunit = ./vendor/bin/phpunit

[files]
contributing = .github/CONTRIBUTING
';

    public function testLoadHomeConfig()
    {
        $homefs = new FakeFsio('/home/username');
        $homefs->setFiles(['/home/username/.producer/config' => $this->homeConfig]);

        $repofs = new FakeFsio('/path/to/repo');

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'api.github.com',
            'github_username' => null,
            'github_token' => null,
            'gitlab_hostname' => 'gitlab.com',
            'gitlab_token' => 'foobarbazdibzimgir',
            'package' => '',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => '/path/to/phpunit',
            ],
            'files' => [
                'changelog' => 'CHANGELOG.md',
                'contributing' => 'CONTRIBUTING.md',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }

    public function testGitHubHostOverride()
    {
        $homefs = $this->mockFsio([
            'github_hostname' => 'example.org',
            'github_username' => 'foo',
            'github_token' => 'bar',
        ]);
        $repofs = $this->mockFsio([], false);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'example.org',
            'github_username' => 'foo',
            'github_token' => 'bar',
            'gitlab_hostname' => 'gitlab.com',
            'gitlab_token' => null,
            'package' => '',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => 'phpunit',
            ],
            'files' => [
                'changes' => 'CHANGES.md',
                'contributing' => 'CONTRIBUTING.md',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }

    public function testGitlabHostOverride()
    {
        $homefs = $this->mockFsio([
            'gitlab_hostname' => 'example.org',
            'gitlab_token' => 'bar',
        ]);
        $repofs = $this->mockFsio([], false);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'api.github.com',
            'github_username' => null,
            'github_token' => null,
            'gitlab_hostname' => 'example.org',
            'gitlab_token' => 'bar',
            'package' => '',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => 'phpunit',
            ],
            'files' => [
                'changes' => 'CHANGES.md',
                'contributing' => 'CONTRIBUTING.md',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }

    public function testLoadHomeAndRepoConfig()
    {
        $homefs = new FakeFsio('/home/username');
        $homefs->setFiles(['/home/username/.producer/config' => $this->homeConfig]);

        $repofs = new FakeFsio('/path/to/repo');
        $repofs->setFiles(['/path/to/repo/.producer/config' => $this->repoConfig]);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'api.github.com',
            'github_username' => null,
            'github_token' => null,
            'gitlab_hostname' => 'gitlab.com',
            'gitlab_token' => 'foobarbazdibzimgir',
            'package' => 'Foo.Bar',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => './vendor/bin/phpunit',
            ],
            'files' => [
                'changelog' => 'CHANGELOG.md',
                'contributing' => '.github/CONTRIBUTING',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }
}
