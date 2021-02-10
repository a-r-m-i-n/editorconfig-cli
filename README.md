# fgtclb/editor-config

CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.



## Dev notes

### Code quality tools

```
$ ddev composer run check

$ ddev composer run phpstan
```


### Compiling phar files

```
$ ddev composer run compile-phar
```

Note: In php.ini the option ``phar.readonly`` must be set to ``0``.
