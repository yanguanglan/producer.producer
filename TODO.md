# TODO

## General

- look first for a repo-specific phpunit, then look for one where producer
  itself is installed.

- allow for repo-specific sections in ~/.producer/config

- allow for repo-specific .producer file

    - allow filename replacement, e.g. "LICENSE" => "LICENSE.md".

    - put those filenames in Config, rather than embedded in Repo

- `validate`

    - check previous major/minor/bugfix version numbers

    - check for @license tag in file-level docblock

- convert to CHANGELOG, and then regex to the previous "## VERSION" line for the change notes?

## New Commands

Cf. existing aura bin commands at <https://github.com/auraphp/bin/tree/master/src/Command>.

```
producer versions
    lists existing release versions

producer log-since {$version}

producer config
    initializes ~/.producer/default/config.php # has private info
    initializes ~/.producer/default/README
    initializes ~/.producer/default/LICENSE
    initializes ~/.producer/default/composer.json
    allow for aura, league, symfony, etc. templates
```
