# Contribute

Any contributions to EditorConfigCLI are very welcome! No matter if you report issues or contribute code.

If you want to provide some code, here are some hints for you.

## Tools

To ensure proper code styles, etc. you can perform the Composer script **all** before submitting your
merge request.

```
$ ddev composer run all
```

First, it fixes all issues, which can get fixed automatically. Then, it performs an additional check,
which includes phpstan (level 8) and performs unit- and functional tests (without coverage).

As last step, it will compile the phar binary, locally.


### Code quality tools

[![Code Checks](https://github.com/a-r-m-i-n/editorconfig-cli/actions/workflows/code-checks.yml/badge.svg)](https://github.com/a-r-m-i-n/editorconfig-cli/actions/workflows/code-checks.yml)

```
$ ddev composer run check
$ ddev composer run fix
$ ddev composer run test
```

### Testing

```
$ ddev composer run test
$ ddev composer run test-with-coverage

$ ddev composer run test-php-unit
$ ddev composer run test-php-functional
```
Note: Xdebug must be available (``ddev xdebug on``) when testing with code coverage enabled.

The results will be located here:

- [Text Report for Unit Tests](../.build/reports/phpunit-unit-results.txt)
- [Text Report for Functional Tests](../.build/reports/phpunit-functional-results.txt)
- [HTML Coverage Report for Unit Tests](../.build/reports/coverage-unit/index.html)
- [HTML Coverage Report for Functional Tests](../.build/reports/coverage-functional/index.html)


### Compiling phar binary

```
$ ddev composer run compile
```

Note: In php.ini the option ``phar.readonly`` must be set to ``0``.


## Automation

### Code styles

When you provide a merge request, Github actions will check your code, using the "check" and "test-with-coverage"
Composer script.

Also, each build will run on the following combinations of PHP version and Composer dependencies flag:

- PHP 7.4, Lowest
- PHP 7.4, Highest
- PHP 8.0, Lowest
- PHP 8.0, Highest
- PHP 8.1, Lowest
- PHP 8.1, Highest
- PHP 8.2, Lowest
- PHP 8.2, Highest

*Note:* "Highest" is the default behaviour of Composer.
        "Lowest" is when you run Composer update with ``--prefer-lowest``


**A build may fail when:**

- EditorConfigCli found issues
- PhpStyleFixer found issues
- PhpStan found issues
- A unit test failed
- A functional test failed

The Github action will provide artifacts for each build, containing the tests results in various formats.


### Release

When a new tag is pushed, Github actions will automatically create a release for it.

This includes compiling the PHAR binary and attaching it, to the release.
Also, the commit messages since last release, will be added.
