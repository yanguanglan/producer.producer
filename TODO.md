# TODO

## General

- allow for repo-specific .producer file, esp. to specify plugins, templates,
  and support file names (e.g. "LICENSE" => "LICENSE.md").

- for `validate`, check previous major/minor/bugfix version numbers

- for `release`, send notifications

- `release` for gitlab

- `release` for bitbucket

## New Commands

Cf. existing aura bin commands at <https://github.com/auraphp/bin/tree/master/src/Command>.

```
producer versions
    lists existing release versions

producer log-since-release [$version]

```
