# TODO

## General

- allow for repo-specific .producer file

## New Commands

Cf. existing aura bin commands.

```
producer docs
    checks the php docblocks, including @package tags

producer versions
    lists existing release versions

producer log-since {$version}

producer release
    checks that a release would be valid

producer release {$version}
    checks against previous major/minor/bugfix progression
    checks that a release would be valid
    actually does the release (bitbucket is just a tag)
    sends notifications
```
