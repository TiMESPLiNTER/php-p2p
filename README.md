# php-p2p

This package provides a simple p2p solution based on `reactphp/socket`.

## Requirements

* php 7.4

## Usage

Have a look at [example/test.php](./example/test.php).

Start first node:

```bash
$ php ./example/test.php 4711
```

Start second node:

```bash
$ php ./example/test.php 4722 127.0.0.1:4711
```
