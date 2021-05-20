# Changelog

**armin/editorconfig-cli**

Link to releases: https://github.com/a-r-m-i-n/editorconfig-cli/releases


## Versions

### 1.3.1

- [TASK] Update dependencies
- [BUGFIX] Fix minimum required versions
- [DEVOPS] Add Github actions
- [DOCS] Add missing editorconfig logo


### 1.3.0

- [DOCS] Improve README
- [TASK] Set required ->files() in custom Finder instance
- [BUGFIX] Do not call scan (or fix) when amount of files is zero
- [TASK] Automatic exclusion: Replace hardcoded folders with Finder's ignoreVCSIgnored(true)
- [FEATURE] Configurable custom Symfony Finder instance


### 1.2.2

- [BUGFIX] Allow uppercase config values


### 1.2.1

- [TASK] Do not replace "phpunit/php-token-stream" and update dependencies
- [BUGFIX] Make editor-config work in environments using symfony/console in version 4


### 1.2.0

- [FEATURE] Add MaxLineLengthRule
- [DEVOPS] Update PhpCsFixer config
- [TASK] Allow Rules to only check code (not fixing them)
- [TASK] Remove Composer patch and update dependencies


### 1.1.2

- [TASK] Update symfony/console
- [BUGFIX] Exclude Compiler.php file correctly from PHAR result
- [BUGFIX] Fix wrong php requirement in "ec" binary


### 1.1.1

- [BUGFIX] Do not output uncovered file message, when amount is 0
- [BUGFIX] Respect default excludes, in verbose output (-v)
- [BUGFIX] Do not count all files, count only invalid ones


### 1.1.0

- [FEATURE] Add "vendor" and "node_modules" as default exclude
- [FEATURE] Add new option "no-error-on-exit"
- [FEATURE] In verbose mode (-v) show files not covered by .editorconfig
- [BUGFIX] Do not throw exception, when .editorconfig value is not lowercase
- [FEATURE] PHP 7.3 support


### 1.0.0

Very first release.

- [TASK] Add author and support info to composer.json
- [TASK] Add license (MIT)
- [TASK] Implement version
- [TASK] Add more README content
- [INITIAL] Set package name to "armin/editorconfig-cli"
- [TASK] Add Composer Patches
- [FEATURE] Add Unit Tests
- [FEATURE] Add EditorConfigCommand with Rules, Scanner and Validator
- [DEVOPS] Add and apply php-cs-fixer
- [DEVOPS] Add and apply phpstan level 8
- [INITIAL] First commit
