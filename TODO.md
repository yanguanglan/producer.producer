# TODO

## General

- allow for repo-specific .producer file

- for `validate` version, against previous major/minor/bugfix version numbers

- for `release`, send notifications


## New Commands

Cf. existing aura bin commands at <https://github.com/auraphp/bin/tree/master/src/Command>.

```
producer versions
    lists existing release versions

producer log-since-release [$version]

producer release $version
    checks that a release would be valid
    actually does the release (bitbucket is just a tag)
```
