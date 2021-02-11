# fgtclb/editor-config

CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.



## Dev notes

### Code quality tools

#### Checking
```
$ ddev composer run check
```
#### Fixing
```
$ ddev composer run fix
```

### Compiling phar files

```
$ ddev composer run compile
```

Note: In php.ini the option ``phar.readonly`` must be set to ``0``.
