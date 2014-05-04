## Easy setup for [nextras/migrations](https://github.com/nextras/migrations) with [kdyby/console](https://github.com/Kdyby/Console)


#### Requirements

- PHP 5.4+
- [nextras/migrations](https://github.com/nextras/migrations) >= 2.1
- [kdyby/console](https://github.com/Kdyby/Console) >= 2.0 (optional)

## Installation

1) Copy source codes from Github or using [Composer](http://getcomposer.org/):
```sh
$ composer require dotblue/nextras-migrations-command@~1.0
```

2) Register as Configurator's extension:
```
extensions:
	migrations: DotBlue\Migrations\MigrationsExtension
```

3) Set configuration to fit your app:
```
migrations:
	groups:
		structures: %appDir%/../sql
```

## `symfony/console` or `kdyby/console`?

You can use this extension with plain Symfony Console. But if you use Kdyby Console, command will get registered automatically.
