# TODO

## General

- allow for repo-specific sections in ~/.producer/config

- allow for repo-specific .producer file

    - allow filename replacement, e.g. "LICENSE" => "LICENSE.md".

    - put those filenames in Config, rather than embedded in Repo

- `validate`

    - check previous major/minor/bugfix version numbers

    - check for @license tag in file-level docblock

## New Commands

Cf. existing aura bin commands at <https://github.com/auraphp/bin/tree/master/src/Command>.

```
producer versions
    lists existing release versions

producer log-since-release [$version]

```
