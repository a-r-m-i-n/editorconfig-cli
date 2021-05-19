# armin/editorconfig-cli

CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.


## Requirements

- PHP 7.3, 7.4 or 8.0
- Enabled PHP extensions: iconv, json


## Installation

To install the editor-config CLI tool you need to download a handy PHAR executable,
or use Composer like this:

```
$ composer req --dev armin/editorconfig-cli"
```

**Tip:** You can also install packages globally with Composer (using the ``composer global`` command).

To download the PHAR executables, check out the releases section
[here](https://github.com/a-r-m-i-n/editorconfig-cli/releases).


## What is EditorConfig?

![EditorConfig logo](docs/images/editorconfig-logo.png)

> EditorConfig helps maintain consistent coding styles for multiple developers working on the
> same project across various editors and IDEs.

Which coding styles should get applied, are configured in the **.editorconfig** file.

You'll find more info about syntax and features of EditorConfig on
https://editorconfig.org


## Features

- Parsing .editorconfig file
- Validating files against corresponding .editorconfig declarations
    - Auto exclusion of files matching .gitignore declarations
- Tool to fix most issues automatically
- The following "rules" are existing:
    - Charset (check only)
    - EndOfLine
    - InsertFinalNewLine
    - TrimTrailingWhitespace
    - MaxLineLength (check only)
    - Indention
        - Style (tab/spaces)
        - Size (width)
- Optional strict mode to force defined indent size of spaces


## Usage

Composer style:
```
$ vendor/bin/ec --help
```

PHAR style:
```
$ php ec-1.3.0.phar --help
```


### How it works

1. Counting all files in the given working directory (``-d``).
2. If the amount of files is greater than 500, ask the user for confirmation to continue
   (use ``-n`` for non-interactive mode).
3. Starting with scan (when ``--fix`` is **not** set). By default a visual activity indicator shows scanned files
   (and highlights errors). You can disable this, with ``--no-progress``.
   When ``--fix`` (or ``-f``) is set, all found issues get fixed.
4. It displays the results (to hide details of each file, you can enable the compact mode ``-c``).


### Screenshot

Here you see all arguments and options the ``ec`` CLI command provides:

![Screenshot](docs/images/ec.png)


### Arguments and options

- Read more about using [Custom Finder instances](docs/CustomFinderInstance.md) (``--finder-config``)


## Dev notes

### Code quality tools

```
$ ddev composer run check
```
```
$ ddev composer run fix
```
```
$ ddev composer run test
```

### Compiling phar binary

```
$ ddev composer run compile
```

Note: In php.ini the option ``phar.readonly`` must be set to ``0``.


## Changelog

[See here](docs/Versions.md)
