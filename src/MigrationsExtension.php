<?php

/**
 * Copyright (c) dotBlue (http://dotblue.net)
 */

namespace DotBlue\Migrations;

use Nette\DI;


class MigrationsExtension extends DI\CompilerExtension
{

	/** @var array */
	private $defaults = [
		'table' => 'migrations',
		'groups' => [],
	];



	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('driver'))
			->setClass('Nextras\Migrations\Drivers\MySqlNetteDbDriver', [
				'tableName' => $config['table'],
			]);

		$container->addDefinition($this->prefix('extension'))
			->setClass('Nextras\Migrations\Extensions\NetteDbSql');

		$controller = $container->addDefinition($this->prefix('controller'))
			->setClass('Nextras\Migrations\Controllers\ConsoleController')
			->addSetup('addExtension', ['sql']);

		$container->addDefinition($this->prefix('command'))
			->setClass('DotBlue\Migrations\MigrationsCommand')
			->addTag('kdyby.console.command');

		foreach ($config['groups'] as $name => $group) {
			$path = is_array($group) ? $group['path'] : $group;
			$controller->addSetup('addGroup', [
				$name,
				$path,
				isset($group['deps']) ? $group['deps'] : [],
			]);
		}
	}

}
