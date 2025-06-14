# Changelog

**armin/editorconfig-cli**

Link to releases: https://github.com/a-r-m-i-n/editorconfig-cli/releases


## Versions

### 2.1.1

- [BUGFIX] Assure compatibility with Symfony v7.3 (**thanks to Elias Häußler**)
- [BUGFIX] Set minimum required PHP version in "ec" binary

### 2.1.0

- [TASK] Update dependencies
- [BUGFIX] Improve output when indention in strict mode is wrong
- [TASK] Update copyright notice in LICENSE
- [DEVOPS] Remove unnecessary docker-compose.web.yaml
- [DEVOPS] Update GitHub actions (**thanks to Elias Häußler**)
- [DEVOPS] Enable parallel runs in PHP-CS-Fixer (**thanks to Elias Häußler**)
- [DEVOPS] Add PHP 8.4 to test matrix (**thanks to Elias Häußler**)
- [DEVOPS] Update to PHPStan v2 (**thanks to Elias Häußler**)

### 2.0.1

- [TASK] Remove "checkMissingIterableValueType" option and add missing iteration value types
- [TASK] Fix code styles
- [TASK] Update dependencies
- [TASK] Exclude Compiler class from dist archives (**thanks to Elias Häußler**)
- [TASK] Exclude development-only files from dist archives (**thanks to Elias Häußler**)

### 2.0.0

- [DOCS] Update screenshot in README
- [TEST] Improve functional test code coverage
- **[TASK][!!!] Do not support Symfony v4 components (#23)**
- [BUGFIX] Fix verbosity in functional tests
- [TASK] Improve application usages and .editorconfig test exclusions
- [TASK] Apply PHP 8.2 adjustments
- [TASK] Apply phpstan fixes
- [TASK] Apply php-cs-fixer fixes
- **[TASK][!!!] Drop PHP 7.4, 8.0 and 8.1 support, ensure PHP 8.3 support (#17)**

### 1.8.1

- [TASK] Add +x flag to bin/ec binary
- [DOCS] Improve README contents
- [BUGFIX] Return error code 3 when not confirming to continue (#24)
- [TASK] Update dependencies

### 1.8.0

- [TASK] Refactor type hints and method access modifiers
- [TASK] Refactor validate method of rules (#21)
- [BUGFIX] Do not detect "image/svg" as binary file
- **[FEATURE] Detect and output warning when files being staged in Git are physically missing**
- [BUGFIX] Do not throw exception when git repo contains file paths with special chars (#22)
- [TASK] Update dependencies

### 1.7.4

- [BUGFIX] Do not detect trailing whitespaces in empty files

### 1.7.3

- [BUGFIX] Do not apply final new line to empty files

### 1.7.2

- [BUGFIX] Do not throw exception for empty file, when checking if file is binary

### 1.7.0

**Caution!** When updating to this version, for the first time, also files with e.g. "application/" mime-type get checked.
This was a major issue in all previous version and is fixed, now. Before only files with mime-type "text/" has been validated.

- [BUGFIX] Fix check for binary files and do not exclude JSON or YAML files

### 1.6.2

- [BUGFIX] Set composer.lock file to PHP 7.4 level

### 1.6.1

- [TASK] Drop PHP 7.3 support

### 1.6.0

- [DEVOPS] Various improvements
- [BUGFIX] Add max_line_length to skip-able rules
- [BUGFIX] Fix functional tests, to work on Windows

### 1.5.2

- [BUGFIX] Downgrade to compatible versions (PHP 7.3)

### 1.5.1

- [TASK] Add support for Symfony 6.x
- [DEVOPS] Add PHP 8.1 to GitHub Action matrix Armin Vieweg Today 13:37

### 1.5.0

- [TASK] Update dependencies
- [TASK] Small improvements
- Revert "[BUGFIX] Respect "root=true" flag"
- Revert "[TEMP] Add patched EditorConfig"
- [FEATURE] Measure and show duration of scan/fix
- [DOCS] Improve README
- [DEVOPS] Display code coverage in CLI output
- [BUGFIX] Use current working directory when "--dir" is null
- [FEATURE] Add new option --git-only
- [TEST] Improve functional tests
- [DEVOPS] Add Github action: Upload test reports artifact
- [DEVOPS] Add phpunit code coverage
- [DEVOPS] Update phpunit from ^7.5 to ^9.5
- [TASK] Remove unused code


### 1.4.0

- [TASK] Improve texts
- [TASK] Streamline wording of error messages
- [TASK] Sort error result by line
- [TEST] Improve functional test
- [FEATURE] Add new option --skip (-s)
- [BUGFIX] Respect "root=true" flag
- [TEMP] Add patched EditorConfig
- [BUGFIX] Respect missing final new line
- [DEVOPS] Add composer script "all"
- [FEATURE] Add new option --uncovered
- [TASK] Add more verbose output (-v)
- [BUGFIX] Do not require "end_of_line", when using "insert_final_newline"
- [FEATURE] Add first functional tests
- [BUGFIX] Do not throw exception, when no root .gitignore file given
- [TASK] Do not output full path in result
- [TASK] Add progress bar and streamline scan result message
- [DEVOPS] Add Github action to automate releases


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
