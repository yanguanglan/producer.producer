# TODO

## General

- `validate`

    - check previous major/minor/bugfix version numbers

    - check for @license tag in file-level docblock

- when `changes = CHANGELOG.*`, to get the change notes for the release, regex
  to find a line with `## VERSION` on it, up til the next `## *` heading.


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
