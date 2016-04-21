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
            'bitbucket_password' => null,
            'bitbucket_username' => null,
            'github_token' => null,
            'github_username' => null,
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

    public function testLoadHomeAndRepoConfig()
    {
        $homefs = new FakeFsio('/home/username');
        $homefs->setFiles(['/home/username/.producer/config' => $this->homeConfig]);

        $repofs = new FakeFsio('/path/to/repo');
        $repofs->setFiles(['/path/to/repo/.producer/config' => $this->repoConfig]);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_password' => null,
            'bitbucket_username' => null,
            'github_token' => null,
            'github_username' => null,
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
