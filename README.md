# fgtclb/editor-config

CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.



## Dev notes

### Code quality tools

#### Checking
```
$ ddev composer run check
```

Same as:
```
$ ddev composer run phpstan
$ ddev composer run php-cs
```

#### Fixing
```
$ ddev composer run fix
```

Same as:

```
$ ddev composer run php-fix
```


### Compiling phar files

```
$ ddev composer run compile-phar
```

Note: In php.ini the option ``phar.readonly`` must be set to ``0``.
