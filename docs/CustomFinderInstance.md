# Custom Finder Instance

Since version 1.3, the EditorConfigCLI binary allows you to define a specific PHP file,
providing your own Symfony Finder instance, which is used to identify the files to be processed.

## CLI Call

To define the PHP file, you can use the ``--finder-config`` option and pass the relative path to the
config file. You are free to choose the location and filename, here in this example it is located in
the project's root directory and is named **ec-cli-config.php**:

```
$ bin/ec --finder-config ec-cli-config.php
```

**Important:** When this option is set, the following CLI options will not have any impact
anymore:

- ``-d, --dir[=DIR]``
- ``-a, --disable-auto-exclude``
- ``-e, --exclude[=EXCLUDE]``
- ``-g, --git-only``

## Finder config file

Within the config file, you need to create and configure a Finder instance. **The following aspects are important:**

- EditorConfigCLI expects as return value a ``Symfony\Component\Finder\Finder`` instance.
  Anything else will cause an exception.

- The finder instance requires one option to be set:
  ```
  $finder->in('/path');
  ```
- Also, the rules require ``->files()`` to be set, which happens automatically in EditorConfigCLI, after
  the custom Finder instance as been successfully imported.



### Minimum example

```php
<?php // ec-cli-config.php

use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder
    ->in($GLOBALS['finderOptions']['path']);

return $finder;
```

When you provide your own Finder config file, the following CLI arguments and options have no effect anymore,
unless you've implemented them:

- ``-d, --dir[=DIR]`` (default: current working directory)
- ``-a, --disable-auto-exclude``
- ``-e, --exclude[=EXCLUDE]``
- the ``names`` argument (default: ``['*']``)

**All those values get passed in an array, you can access with:**

```php
<?php
$GLOBALS['finderOptions'];
// [
//     'path' => '/real/path/to/dir',
//     'names' => ['*'],
//     'exclude' => [],
//     'disable-auto-exclude' => false,
// ];
```
